<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SeedData_model extends CI_Model
{
    protected $db_central;

    protected $db_production;

    protected $tableMachine = 'equipments';
    protected $tableCategory = 'categories';

    public function __construct()
    {
        parent::__construct();
        $this->db_central = $this->load->database('db2', TRUE);
        $this->db_production = $this->load->database('db3', TRUE);
    }


	public function getCentral()
	{
		$this->db_central->select('*');
		$this->db_central->from('devices');
		$query = $this->db_central->get();
		return $query->result_array();
	}

    public function getProduction()
	{
		$this->db_production->select('*');
		$this->db_production->from('devices');
		$query = $this->db_production->get();
		return $query->result_array();
	}

    public function getCombineCentralAndProduction()
    {
        $this->db_central->select('*');
		$this->db_central->from('devices');
		$query = $this->db_central->get();
		$centrals = $query->result();

        $this->db_production->select('*');
		$this->db_production->from('devices');
		$query = $this->db_production->get();
		$productions = $query->result();

        $combines = [];
        foreach ($centrals as $central) {
            $combine = [];
            $combine['machine_id'] = $central->device_id;
            $combine['type'] = $central->system;
            $combine['cavity'] = 'null';
            $combine['model'] = 'null';
            $combine['manufacturer'] = 'null';
            $combine['manufacturing_date'] = 'null';
            $combines[] = $combine;
        }

        foreach ($productions as $production) {
            $combine = [];
            $combine['machine_id'] = $production->device_id;
            $combine['type'] = $production->display_type;
            $combine['cavity'] = $production->cavities ?? 'null';
            $combine['model'] = 'null';
            $combine['manufacturer'] = 'null';
            $combine['manufacturing_date'] = 'null';

            $combines[] = $combine;
        }

        return $combines;
    }

    public function getCategories()
    {
        $combines = $this->getCombineCentralAndProduction();
        $codes = [];
        foreach ($combines as $item) {
            $machine_id = $item['machine_id'];
            $type = $item['type'];

            // regex lấy tất cả chữ trước chữ số đầu tiên
            if (preg_match('/^[A-Za-z]+/', $machine_id, $matches)) {
                $code = $matches[0];
                
                // dùng key kết hợp code + type để đảm bảo unique
                $key = $code . '|' . $type;
                if (!isset($codes[$key])) {
                    $codes[$key] = [
                        'code' => $code,
                        'name' => $type
                    ];
                }
            }
        }

        $unique_codes = array_values($codes);

        return $unique_codes;

    }

    public function seedCategory($listCategories)
    {
        if (empty($listCategories)) {
            return 0;
        }

        // Bắt đầu transaction
        $this->db->trans_start();

        // Insert batch trực tiếp
        $this->db->insert_batch($this->tableCategory, $listCategories);

        // Kết thúc transaction, tự động commit nếu không lỗi, rollback nếu lỗi
        $this->db->trans_complete();

        // Kiểm tra transaction
        return $this->db->trans_status(); // true nếu commit, false nếu rollback
    }


    /**
     * Insert nhiều máy vào bảng machines với transaction
     * @param array $listMachine
     * @return bool true nếu insert thành công, false nếu rollback
     */
    public function seedMachine($listMachine)
    {
        if (empty($listMachine)) {
            return 0;
        }


        // Bắt đầu transaction
        $this->db->trans_start();

        // Insert batch trực tiếp
        $this->db->insert_batch($this->tableMachine, $listMachine);

        // Kết thúc transaction, tự động commit nếu không lỗi, rollback nếu lỗi
        $this->db->trans_complete();

        // Kiểm tra transaction
        return $this->db->trans_status(); // true nếu commit, false nếu rollback
    }

    public function getCategoriesFromCMMS()
	{
		$this->db->select('uuid, name, code');
		$this->db->from($this->tableCategory);
		$this->db->where('deleted_at IS NULL');
		$this->db->where('deleted_by IS NULL');
		$query = $this->db->get();
		return $query->result_array();
	}

    public function insertCount()
    {
        // Mold: sum cavities với điều kiện cycle_time > 20
        $moldData = $this->sumByDevice('mold', 'mold_id', 'cavities', ['cycle_time' => ['>', 20]]);
        // Tuft: sum output
        $tuftData = $this->sumByDevice('tuft', 'device_id', 'output');
        // Blister: sum cyclecount
        $blisterData = $this->sumByDevice('blister', 'device_id', 'cyclecount');

        // Convert thành map để merge nhanh
        $mapMold    = $this->toMap($moldData);
        $mapTuft    = $this->toMap($tuftData);
        $mapBlister = $this->toMap($blisterData);

        // Lấy danh sách machines từ DB chính
        $machines = $this->db->select('machine_id')
                            ->from('equipments')
                            ->get()
                            ->result_array();

        $result = [];
        
        foreach ($machines as $m) {
            $eid = $m['machine_id'];

            $obj = new stdClass();
            $obj->equipment_id       = $eid;
            $value = 0;

            if (!empty($mapMold[$eid])) {
                $value = $mapMold[$eid];
            } elseif (!empty($mapTuft[$eid])) {
                $value = $mapTuft[$eid];
            } elseif (!empty($mapBlister[$eid])) {
                $value = $mapBlister[$eid];
            }

            $obj->history_count = $value;
            $result[] = $obj;
        }

        return $result;
    }

    private function toMap($rows)
    {
        $map = [];
        foreach ($rows as $r) {
            $map[$r->equipment_id] = (int)$r->total;
        }
        return $map;
    }

    private function sumByDevice($table, $idField, $sumField, $where = [])
    {
        // 1) Lấy all machines từ DB chính
        $machines = $this->db->select('machine_id')
                            ->from('equipments')
                            ->get()
                            ->result_array();

        if (empty($machines)) {
            return []; // không có máy nào
        }

        $machineIds = array_column($machines, 'machine_id');

        // 2) Query production DB theo chunks
        $agg = []; 
        $chunkSize = 900; 
        $chunks = array_chunk($machineIds, $chunkSize);

        foreach ($chunks as $chunk) {
            $this->db_production->select("$idField as equipment_id, COALESCE(SUM($sumField), 0) as total", false);
            $this->db_production->from($table);

            // Nếu có điều kiện thêm (ví dụ cycle_time > 20 cho mold)
            if (!empty($where)) {
                foreach ($where as $field => $val) {
                    if (is_array($val) && count($val) === 2 && $val[0] === '>') {
                        $this->db_production->where("$field >", $val[1]);
                    } else {
                        $this->db_production->where($field, $val);
                    }
                }
            }

            $this->db_production->where_in($idField, $chunk);
            $this->db_production->group_by($idField);

            $rows = $this->db_production->get()->result_array();

            foreach ($rows as $r) {
                $eid = $r['equipment_id'];
                $agg[$eid] = isset($agg[$eid]) ? $agg[$eid] + (int)$r['total'] : (int)$r['total'];
            }
        }

        // 3) Merge với danh sách machines
        $result = [];
        foreach ($machines as $m) {
            $obj = new stdClass();
            $obj->equipment_id = $m['machine_id'];
            $obj->total = isset($agg[$m['machine_id']]) ? (int)$agg[$m['machine_id']] : 0;
            $result[] = $obj;
        }

        return $result;
    }




	// public function counts()
	// {
	// 	$this->db->from($this->table);
	// 	$this->db->where('deleted_at IS NULL');
	// 	$this->db->where('deleted_by IS NULL');
	// 	return $this->db->count_all_results();
	// }

    private function generate_uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
