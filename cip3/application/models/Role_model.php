<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends CI_Model {

    protected $table = 'roles';

    public function __construct() {
        parent::__construct();
    }

    // Lấy tất cả role
    public function get_all_roles() {
        return $this->db->get($this->table)->result_array();
    }

    // Lấy role theo id
    public function get_role_by_id($uuid) {
        return $this->db->where('uuid', $uuid)->get($this->table)->row_array();
    }

    // Thêm role
    public function insert_role($data) {
        return $this->db->insert($this->table, $data);
    }

    // Cập nhật role
    public function update_role($uuid, $data) {
        return $this->db->where('uuid', $uuid)->update($this->table, $data);
    }

    // Xoá role
    public function delete_role($uuid) {
        return $this->db->where('uuid', $uuid)->delete($this->table);
    }
}
