<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model {
    protected $table = 'categories';

    public function getCategories($limit, $offset)
    {
        $this->db->select('uuid as id, name');
        $this->db->from($this->table);
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function counts()
    {
        return $this->db->count_all($this->table);
    }
}