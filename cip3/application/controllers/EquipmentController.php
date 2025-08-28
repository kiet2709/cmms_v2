<?php
defined('BASEPATH') or exit('No direct script access allowed');

class EquipmentController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Equipment_model');
        $this->load->model('DailyTask_model');
        $this->load->library('JWT');
    }

    private function respond($status_code, $data)
    {
        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))
            ->_display();
        exit;
    }

    public function getAllEquipments()
    {
        // Lấy limit & page từ request
        $limit = $this->input->get('limit') ?? 20;
        $page = $this->input->get('page') ?? 1;

        $offset = ($page - 1) * $limit;

        // Lấy dữ liệu phân trang
        $users = $this->Equipment_model->getEquipments($limit, $offset);
        $total_items =  $this->Equipment_model->counts();
        $total_pages = ceil($total_items / $limit);

        $result = [
            'status' => 'success',
            'message' => 'Lấy dữ liệu thành công',
            'data' => $users,
            'total_items' => count($users),
            'total_pages' => $total_pages,
            'total_in_all_page' => $total_items
        ];

        return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($result));
    }

    public function getEquipmentById()
    {
        $equipment_id = $this->input->get('equipment_id');
        $equipment = $this->Equipment_model->getById($equipment_id);
        $result = [
            'status' => 'success',
            'message' => 'Lấy dữ liệu thành công',
            'data' => $equipment,
        ];

        return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($result));
    }


      public function createEquipment() {
        // $post = json_decode(file_get_contents('php://input'), true);
        $post = $_REQUEST['payload'] ?? null;
        if (empty(array_filter($post['data'], fn($v) => $v !== '' && $v !== null))) {
            return $this->respond(400, ['message' => 'missing field info']);
        }

        $data = $post['data'];

        // build equipment data
        $equipmentData = [
            'uuid'              => $this->uuidv4(),
            'machine_id'      => $data['machineId'] ?? null,
            'model'           => $data['model'] ?? null,
            'family'          => $data['family'] ?? null,
            'manufacturing_date'=> $data['manufactureDate'] ?? null,
            'manufacturer'    => $data['manufacturer'] ?? null,
            'history_count'   => $data['historyCount'] ?? null,
            'unit'            => $data['unit'] ?? null,
            'cavity'          => $data['cavity'] ?? null,
            'category_id'     => $data['category'] ?? null,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $this->db->trans_start();
        $this->Equipment_model->insertEquipment($equipmentData);

        // insert mapping
        $this->Equipment_model->insertWorkingInstructions($equipmentData['uuid'], $data['inspectionCodes'] ?? []);
        $this->Equipment_model->insertWorkingInstructions($equipmentData['uuid'], $data['maintenanceLevel1'] ?? []);
        $this->Equipment_model->insertWorkingInstructions($equipmentData['uuid'], $data['maintenanceLevel2'] ?? []);
        $this->Equipment_model->insertWorkingInstructions($equipmentData['uuid'], $data['maintenanceLevel3'] ?? []);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Insert failed'
                ]));
        }
        $this->DailyTask_model->insert_daily_tasks();

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => 'Equipment created successfully',
                'equipment_id' => $equipmentData['id']
            ]));
    }

    public function updateEquipment() {
        $post = $_REQUEST['payload'] ?? null;
        if (empty(array_filter($post['data'], fn($v) => $v !== '' && $v !== null))) {
            return $this->respond(400, ['message' => 'missing field info']);
        }

        $data = $post['data'];


        // build equipment data
        $equipmentData = [
            'machine_id' => $data['machineId'],
            'model'             => $data['model'] ?? null,
            'family'            => $data['family'] ?? null,
            'manufacturing_date'=> $data['manufactureDate'] ?? null,
            'manufacturer'      => $data['manufacturer'] ?? null,
            'history_count'     => $data['historyCount'] ?? null,
            'unit'              => $data['unit'] ?? null,
            'cavity'            => $data['cavity'] ?? null,
            'category_id'       => $data['category'] ?? null,
            'updated_at'        => date('Y-m-d H:i:s'),
        ];

        $this->db->trans_start();
        $this->Equipment_model->updateEquipment($data['uuid'], $equipmentData);

        $allWi = array_merge(
            $data['inspectionCodes'] ?? [],
            $data['maintenanceLevel1'] ?? [],
            $data['maintenanceLevel2'] ?? [],
            $data['maintenanceLevel3'] ?? []
        );
        // update working instructions mapping
        $this->Equipment_model->updateWorkingInstructions($data['uuid'], $allWi ?? []);

        $this->db->trans_complete();

        $this->DailyTask_model->insert_daily_tasks();

        if ($this->db->trans_status() === FALSE) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Update failed'
                ]));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => 'Equipment updated successfully',
                'equipment_id' => $data['machineId']
            ]));
    }


    private function uuidv4() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

}