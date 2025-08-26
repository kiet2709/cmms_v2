<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Equipment_model extends CI_Model {
    protected $table = 'equipments';

    public function getEquipments($limit, $offset)
    {
        $this->db->select('equipments.uuid, machine_id, family, model, cavity, manufacturer, manufacturing_date, history_count, unit, categories.name as category');
        $this->db->from($this->table);
         $this->db->join('categories', 'categories.uuid = equipments.category_id', 'left');
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function counts()
    {
        return $this->db->count_all($this->table);
    }

    public function getById($equipment_id)
    {
        $this->db->select('equipments.uuid, machine_id, family, model, cavity, manufacturer, manufacturing_date, history_count, unit, categories.name as category, categories.uuid as category_id');
        $this->db->from($this->table);
         $this->db->join('categories', 'categories.uuid = equipments.category_id', 'left');
        $this->db->where('equipments.uuid', $equipment_id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function insertEquipment($data) {
        return $this->db->insert('equipments', $data);
    }

    public function insertWorkingInstructions($equipmentId, $wiList) {
        if (empty($wiList)) return;

        $batch = [];
        foreach ($wiList as $wiId) {
            $batch[] = [
                'equipment_id'       => $equipmentId,
                'working_instruction_id' => $wiId,
                'created_at'         => date('Y-m-d H:i:s')
            ];
        }
        $this->db->insert_batch('equipment_working_instructions', $batch);
    }

        private function generate_uuid() {
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