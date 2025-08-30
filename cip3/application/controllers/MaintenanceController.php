<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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

}