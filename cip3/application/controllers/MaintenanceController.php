<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property Maintenance_model $Maintenance_model
 * @property CI_Output $output
 */
class MaintenanceController extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Maintenance_model');
        // $this->load->model('WorkingInstruction_model');
    }

    /**
     * API: Lấy danh sách equipment hôm nay chưa inspected (unique equipment_id)
     */
    public function getMachineWithMaintenancePlan()
    {
        $data = $this->Maintenance_model->getListMachineHaveMaintenancePlan();

        // Trả ra dạng JSON
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'date'   => date('Y-m-d'),
                'data'   => $data
            ]));
    }

    public function getMachineTaskBySessionId()
    {
        $sessionId = $_GET['session_id'];
        $data = $this->Maintenance_model->getListMaintenanceTaskByMaintenanaceSession($sessionId);

        // Trả ra dạng JSON
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'date'   => date('Y-m-d'),
                'data'   => $data
            ]));
    }

    public function getWIMaintenance()
    {
        $uuid = $_GET['uuid'];
        $data = $this->Maintenance_model->getWIMaintenance($uuid );

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

    public function addPlan()
    {
        $data = json_decode($this->input->raw_input_stream, true);
        
        
        $this->Maintenance_model->addPlan($data);

        return $this->respond(200, ['data' => $data]);
    }

    public function getPlansByEquipment()
    {
        $uuid = $_GET['uuid'];
        $data = $this->Maintenance_model->getPlanByEquipment($uuid);
        return $this->respond(200, $data);
    }

    public function getMaintenanceSessionIncomming()
    {
        $data = $this->Maintenance_model->getMaintenanceSessionIncomming();
        return $this->respond(200, $data);
    }

    public function deletePlan()
    {
        $data = json_decode($this->input->raw_input_stream, true);
        $this->Maintenance_model->delete($data['planId']);
        return $this->respond(200, $data);
    }

}