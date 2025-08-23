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
}