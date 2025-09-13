<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property DailyTask_model $DailyTask_model
 * @property CI_Output $output
 */
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
        $from = $_GET['date_from'];
        $to = $_GET['date_to'];
        $today = date('Y-m-d');
        if ($from == $today && $to == $today) {
            $data = $this->DailyTask_model->get_today_incomplete_equipments();

        } else {
            $data = $this->DailyTask_model->getEquipmentFromDate($from, $to);
        }
        

        // Trả ra dạng JSON
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'date'   => date('Y-m-d'),
                'data'   => $data
            ]));
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

    public function getDailyTasksByEquipment() {
        $equipment_id = $_GET['equipment_id'];
        $from = $_GET['dateFrom'];
        $to = $_GET['dateTo'];
        $today = date('Y-m-d');
        if ($from == $today && $to == $today) {
            $tasks = $this->DailyTask_model->get_daily_tasks($equipment_id);
        } else {
            $tasks = $this->DailyTask_model->getTaskWithDate($equipment_id, $from, $to);
        }
        

        

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


    public function doDailyTask()
    {
        $data = json_decode($this->input->raw_input_stream, true);
        
        $this->DailyTask_model->doTask($data['uuid'], $data['schema'], $data['inspectorId']);

        return $this->respond(200, [
            'success' =>  true,
            'data' => $data
        ]);
    }
    
}
