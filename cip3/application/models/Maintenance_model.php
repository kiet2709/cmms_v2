<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Maintenance_model extends CI_Model {
    public function getListMachineHaveMaintenancePlan()
    {
        $now = date('Y-m-d');
        $this->db->select('ms.equipment_id, 
            ms.uuid as ms_uuid, 
            e.machine_id, 
            e.model,
            e.cavity,
            e.manufacturer,
            e.manufacturing_date,
            ct.name as category,
        ');
        $this->db->from('maintenance_sessions ms');
        $this->db->join('equipments e', 'ms.equipment_id = e.uuid');
        $this->db->join('categories ct', 'e.category_id = ct.uuid');
        $this->db->where('DATE(date_maintenance)', $now);
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

    public function getWIMaintenance($uuid)
    {
        $this->db->select('*');
        $this->db->from('maintenance_tasks mt');
        $this->db->where('mt.uuid', $uuid);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function addPlan($data)
    {
        $now = date('Y-m-d H:i:s');
        $this->db->insert('maintenance_sessions', [
            'uuid' => $this->uuidv4(),
            'equipment_id' => $data['equipmentUuid'],
            'date_maintenance' => $data['date'],
            'status' => 'pending',
            'created_by' => $data['userId'],
            'updated_by' => $data['userId'],
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }

    private function uuidv4()
	{
		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff)
		);
	}

    public function getPlanByEquipment($uuid)
    {
        $this->db->select('*');
        $this->db->from('maintenance_sessions');
        $this->db->where('equipment_id', $uuid);
        $query = $this->db->get();
        return $query->result();
    }

    public function delete($uuid)
    {
        $this->db->where('uuid', $uuid);
        return $this->db->delete('maintenance_sessions');
    }

    public function insertTaskViaSession($sessionId)
    {
        $this->db->select('*');
        $this->db->from('maintenance_sessions');
        $this->db->where('uuid', $sessionId);
        $query = $this->db->get();
        $session = $query->row();

        $this->db->select(
            'wi.uuid as wi_id,
            wi.code,
            wi.name,
            wi.type,
            wi.schema,
            wi.category_id
        ');
        $this->db->from('equipment_working_instructions ewi');
        $this->db->join('working_instructions wi', 'ewi.working_instruction_id = wi.uuid');
        $this->db->where('equipment_id', $session->equipment_id);
        $query = $this->db->get();
        $tasks = $query->result();

        $now = date('Y-m-d');
        $insertData = [];
        foreach ($tasks as $task) {
            $insertData[] = [
                'uuid' => $this->uuidv4(),
                'wi_id' => $task->wi_id,
                'code' => $task->code,
                'name' => $task->name,
                'type' => $task->type,
                'schema' => $task->schema,
                'category_id' => $task->category_id,
                'maintenance_session_id' => $sessionId,
                'date_start' => $now
            ];
        }
        $this->db->insert_batch('maintenance_tasks', $insertData);
    }

    public function cronjobInsertMaintenance()
    {
        $now = date('Y-m-d');
        $this->db->select('*');
        $this->db->from('maintenance_sessions');
        $this->db->where('DATE(date_maintenance)', $now);
        $query = $this->db->get();
        $sessions = $query->result();
        foreach ($sessions as $session) {
            $this->db->where('maintenance_session_id', $session->uuid);
            $countTaskInSession = $this->db->count_all_results('maintenance_tasks');

            $this->db->where('equipment_id', $session->equipment_id);
            $countTaskInPlan = $this->db->count_all_results('equipment_working_instructions');

            if ($countTaskInSession < $countTaskInPlan) {
                $this->db->where('maintenance_session_id', $session->uuid);
                $this->db->delete('maintenance_tasks');

                $this->insertTaskViaSession($session->uuid);
            }
        }
    }

    public function getMaintenanceSessionIncomming()
    {
        $now = date('Y-m-d');
        $this->db->select('e.machine_id, ms.date_maintenance, ms.status');
        $this->db->from('maintenance_sessions ms');
        $this->db->join('equipments e', 'e.uuid = ms.equipment_id');
        $this->db->where('date_maintenance >=', $now);
        $query = $this->db->get();
        return $query->result();

    }

}