<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Maintenance_model extends CI_Model {
    public function getListMachineHaveMaintenancePlan()
    {
        $this->db->select('ms.equipment_id, 
            ms.uuid as ms_uuid, 
            e.machine_id, 
            e.family, 
            e.model,
            e.cavity,
            e.manufacturer,
            e.manufacturing_date,
            ct.name as category,
            ms.est_count,
            ms.est_date,
            ms.actual_count,
            ms.actual_date
        ');
        $this->db->from('maintenance_sessions ms');
        $this->db->join('equipments e', 'ms.equipment_id = e.uuid');
        $this->db->join('categories ct', 'e.category_id = ct.uuid');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getListMaintenanceTaskByMaintenanaceSession($sessionId)
    {
        $this->db->select('*');
        $this->db->from('maintenance_tasks mt');
        $this->db->where('mt.maintenance_session_id', $sessionId);
        $query = $this->db->get();
        return $query->result_array();
    }   

}