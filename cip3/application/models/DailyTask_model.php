<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DailyTask_model extends CI_Model {
     protected $table = 'daily_tasks';

    public function insert_daily_tasks()
    {
        // Lấy danh sách equipment_working_instructions + join working_instructions
        $this->db->select('ewi.equipment_id, ewi.working_instruction_id, wi.type, wi.uuid as uuid, wi.code, wi.schema, wi.name, wi.category_id');
        $this->db->from('equipment_working_instructions ewi');
        $this->db->join('working_instructions wi', 'wi.uuid = ewi.working_instruction_id');
        $this->db->where('ewi.deleted_at IS NULL');
        $this->db->where('ewi.deleted_by IS NULL');
        $this->db->where('wi.type', 'Daily Inspection');
        $query = $this->db->get();
        $records = $query->result();

        $today = date('Y-m-d');

        foreach ($records as $row) {
            // Check trong daily_tasks có record nào trùng equipment_id + wi_id chưa
            $this->db->from('daily_tasks');
            $this->db->where('equipment_id', $row->equipment_id);
            $this->db->where('wi_id', $row->working_instruction_id);
            $this->db->order_by('created_at', 'DESC');
            $this->db->limit(1);
            $exists = $this->db->get()->row();

            if (!$exists) {
                // Chưa có record nào -> insert mới
                $this->insert_task($row->equipment_id, $row, $today);
            } else {
                // Đã có -> check date_start
                $last_date = date('Y-m-d', strtotime($exists->date_start));
                if ($last_date != $today) {
                    // Insert thêm bản mới cho hôm nay
                    $this->insert_task($row->equipment_id, $row, $today);
                }
                // Nếu $last_date == $today thì bỏ qua (lazy init)
            }
        }
    }

    private function insert_task($equipment_id, $wi, $today)
    {
        $data = [
            'uuid'       => $this->generate_uuid(),
            'equipment_id' => $equipment_id,
            'wi_id'      => $wi->uuid,
            'code'    => $wi->code,
            'name'      => $wi->name,
            'type'      => $wi->type,
            'schema'    => $wi->schema,
            'category_id'=> $wi->category_id,
            'date_start' => $today,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('daily_tasks', $data);
    }

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

    public function get_today_incomplete_equipments()
    {
        $today = date('Y-m-d');

        // 1) Dataset chính: daily_tasks hôm nay, chưa inspected
        $this->db->select('
            dt.equipment_id,
            dt.wi_id,
            dt.status,
            dt.inspector_id,
            e.machine_id,
            e.model, e.cavity,
            e.history_count, e. manufacturing_date, e.unit, e.manufacturer,
            c.name AS category_name,
            u.username AS inspector_username
        ');
        $this->db->from('daily_tasks dt');
        $this->db->join('equipments e', 'e.uuid = dt.equipment_id');
        $this->db->join('categories c', 'c.uuid = e.category_id', 'left');
        $this->db->join('users u', 'u.uuid = dt.inspector_id', 'left');
        $this->db->where('dt.deleted_at IS NULL');
        $this->db->where('dt.deleted_by IS NULL');
        $this->db->group_start();
        $this->db->where('DATE(dt.date_start)', $today);
        $this->db->or_where('dt.inspected_date IS NULL');
        $this->db->or_where('DATE(dt.inspected_date)', $today);
        $this->db->group_end();// chỉ lấy record chưa inspected của hôm nay
        $query = $this->db->get();
        $records = $query->result();

        if (empty($records)) {
            return [];
        }

        // 2) Gom theo equipment_id, đồng thời gom inspectors (loại trùng) & thu statuses
        $grouped = [];
        foreach ($records as $row) {
            $eid = $row->equipment_id;

            if (!isset($grouped[$eid])) {
                $grouped[$eid] = [
                    'equipment_id'   => $eid,
                    'machine_id'     => $row->machine_id,
                    'model'          => $row->model,
                    'cavity'         => $row->cavity,
                    //'family'         => $row->family,
                    'category_name'  => $row->category_name,
                    'history_count' => $row->history_count,
                    'manufacturing_date' => $row->manufacturing_date,
                    'unit' => $row->unit,
                    'manufacturer' => $row->manufacturer,
                    'statuses'       => [],
                    'inspectors_set' => [],  // dùng set để loại trùng
                ];
            }

            // push status
            $grouped[$eid]['statuses'][] = $row->status;

            // gom inspector theo username, loại trùng
            if (!empty($row->inspector_id) && !empty($row->inspector_username)) {
                $grouped[$eid]['inspectors_set'][$row->inspector_username] = true;
            }
        }

        // 3) Lấy inspected_date mới nhất cho mỗi equipment_id (MAX(inspected_date)) từ toàn bảng
        $equipmentIds = array_keys($grouped);

        $this->db->select('equipment_id, MAX(inspected_date) AS last_inspected_date');
        $this->db->from('daily_tasks');
        $this->db->where('deleted_at IS NULL');
        $this->db->where('deleted_by IS NULL');
        $this->db->where_in('equipment_id', $equipmentIds);
        $this->db->where('inspected_date IS NOT NULL'); // chỉ tính những cái đã inspected
        $this->db->group_by('equipment_id');
        $latestQuery = $this->db->get();

        $latestMap = [];
        foreach ($latestQuery->result() as $r) {
            $latestMap[$r->equipment_id] = $r->last_inspected_date; // DATETIME/DATE: MAX là "mới nhất"
        }

        // 4) Kết quả cuối: unique theo equipment_id + tổng hợp status & inspectors + inspected_date mới nhất
        $result = [];
        foreach ($grouped as $eid => $item) {
            // completed nếu tất cả status == 'done', ngược lại incomplete
            $isCompleted = true;
            foreach ($item['statuses'] as $st) {
                if ($st === NULL || strtolower($st) !== 'done') {
                    $isCompleted = false;
                    break;
                }
            }

            $this->db->from('daily_tasks');
            $this->db->where('status', 'done');
            $this->db->where('inspected_date IS NOT NULL', null, false);
            $this->db->where('DATE(inspected_date)', $today);
            $this->db->where('equipment_id',  $item['equipment_id']);
            $count_done_today = $this->db->count_all_results();

            $this->db->from('daily_tasks');
            $this->db->where('equipment_id', $item['equipment_id']);
            $this->db->group_start()
                ->where('DATE(date_start)', $today)
                ->or_group_start()
                    ->where('DATE(inspected_date)', $today)
                ->group_end()
                ->or_group_start()
                    ->where('inspected_date IS NULL')
                ->group_end()
            ->group_end();
            
            $count_pending_today = $this->db->count_all_results();

            $inspectors = implode(', ', array_keys($item['inspectors_set']));
            $lastInspected = isset($latestMap[$eid]) ? $latestMap[$eid] : null;

            $result[] = [
                'equipment_id'   => $item['equipment_id'],
                'machine_id'     => $item['machine_id'],
                'model'          => $item['model'],
                'cavity'         => $item['cavity'],
                //'family'         => $item['family'],
                'count_done'    => $count_done_today,
                'count_pending' => $count_pending_today,
                'category_name'  => $item['category_name'],
                'history_count' => $item['history_count'],
                'manufacturing_date' => $item['manufacturing_date'],
                'unit' => $item['unit'],
                'manufacturer' => $item['manufacturer'],
                'status'         => $isCompleted ? 'completed' : 'incomplete',
                'inspectors'     => $inspectors,      // "username1, username2, ..."
                'inspected_date' => $lastInspected    // MAX(inspected_date) toàn bảng theo equipment_id (có thể null)
            ];
        }

        return $result;
    }

    
    public function getEquipmentFromDate($from, $to)
    {
    $this->db->select("
        e.uuid as equipment_id,
        e.machine_id,
        e.model,
        e.cavity,
        cat.name as category_name,
        e.history_count,
        e.manufacturing_date,
        e.unit,
        e.manufacturer,

        GROUP_CONCAT(DISTINCT u.username ORDER BY u.username SEPARATOR ', ') as inspectors,

        -- status = 'completed' khi tất cả record trong nhóm đều = 'done'
        CASE
            WHEN COUNT(*) > 0
                 AND SUM(CASE WHEN dt.status = 'done' THEN 1 ELSE 0 END) = COUNT(*)
            THEN 'completed'
            ELSE 'incomplete'
        END as status,

        -- count_done: status = 'done' và inspected_date IS NOT NULL và inspector_id IS NOT NULL
        SUM(CASE
            WHEN dt.status = 'done'
                 AND dt.inspected_date IS NOT NULL
                 AND dt.inspector_id IS NOT NULL
            THEN 1 ELSE 0
        END) as count_done,

        -- count_pending: status IS NULL và inspected_date IS NULL và inspector_id IS NULL
        SUM(1) as count_pending,

        COUNT(*) as total_tasks
    ", false);

    $this->db->from('daily_tasks dt');
    $this->db->join('equipments e', 'dt.equipment_id = e.uuid');
    $this->db->join('categories cat', 'e.category_id = cat.uuid', 'left');
    $this->db->join('users u', 'dt.inspector_id = u.uuid', 'left');
    $this->db->where('dt.date_start >=', $from);
    $this->db->where('dt.date_start <=', $to);
    $this->db->group_by('e.uuid');
    $query = $this->db->get();

    return $query->result_array();
    }
    
    
    public function get_daily_tasks($equipment_id) {
        $today = date('Y-m-d');

        $this->db->select('
            daily_tasks.code,
            daily_tasks.uuid as wi_id,
            daily_tasks.name as content,
            daily_tasks.inspected_date,
            daily_tasks.date_start,
            daily_tasks.status,
            daily_tasks.result,
            daily_tasks.inspector_id,
            users.username as inspector_name
        ');
        $this->db->from('daily_tasks');
        $this->db->join('users', 'users.uuid = daily_tasks.inspector_id', 'left');
        $this->db->where('daily_tasks.deleted_by IS NULL');
        $this->db->where('daily_tasks.deleted_at IS NULL');
        $this->db->where('daily_tasks.equipment_id', $equipment_id);

        // (date_start = today OR inspected_date IS NULL)
        $this->db->group_start();
            $this->db->where('DATE(daily_tasks.date_start)', $today);
            $this->db->or_where('daily_tasks.inspected_date IS NULL');
            $this->db->or_where('DATE(daily_tasks.inspected_date)', $today);
        $this->db->group_end();

        $query = $this->db->get();
        return $query->result_array();
    }


    public function getTaskWithDate($equipment_id, $from, $to)
    {
        $today = date('Y-m-d');

        $this->db->select('
            daily_tasks.code,
            daily_tasks.uuid as wi_id,
            daily_tasks.name as content,
            daily_tasks.inspected_date,
            daily_tasks.date_start,
            daily_tasks.status,
            daily_tasks.result,
            daily_tasks.inspector_id,
            users.username as inspector_name
        ');
        $this->db->from('daily_tasks');
        $this->db->join('users', 'users.uuid = daily_tasks.inspector_id', 'left');
        $this->db->where('daily_tasks.deleted_by IS NULL');
        $this->db->where('daily_tasks.deleted_at IS NULL');
        $this->db->where('daily_tasks.equipment_id', $equipment_id);
        $this->db->where('date_start >=', $from);
        $this->db->where('date_start <=', $to);

        $query = $this->db->get();
        return $query->result_array();
    }

    public function getById($id) {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('uuid', $id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function doTask($uuid, $schema, $inspectorId) 
    {
        $now = date('Y-m-d H:i:s');

        $this->db->where('uuid', $uuid);
        return $this->db->update('daily_tasks', [
            'schema' => json_encode($schema, JSON_UNESCAPED_UNICODE),
            'status' => 'done',
            'inspected_date' => $now,
            'inspector_id' => $inspectorId,
        ]);
    }
}