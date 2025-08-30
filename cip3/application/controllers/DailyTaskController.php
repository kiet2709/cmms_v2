<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DailyTaskController extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('DailyTask_model');
        $this->load->model('WorkingInstruction_model');
    }

    /**
     * API: Lấy danh sách equipment hôm nay chưa inspected (unique equipment_id)
     */
    public function get_today_equipments()
    {
        $data = $this->DailyTask_model->get_today_incomplete_equipments();

        // Trả ra dạng JSON
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'date'   => date('Y-m-d'),
                'data'   => $data
            ]));
    }

    public function getDailyTasksByEquipment() {
        $equipment_id = $_GET['equipment_id'];
        $tasks = $this->DailyTask_model->get_daily_tasks($equipment_id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data'   => $tasks
            ]));
    }

    public function getWiById()
    {
        $wi_id = $this->input->get('id');
        $wi = $this->DailyTask_model->getById($wi_id);
        $result = [
            'status' => 'success',
            'message' => 'Lấy dữ liệu thành công',
            'data' => $wi,
        ];

        return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($result));
    }

    
}
