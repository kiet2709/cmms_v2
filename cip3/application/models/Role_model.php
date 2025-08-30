<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Role_model extends CI_Model
{

	protected $table = 'roles';

	public function __construct()
	{
		parent::__construct();
	}

	// Lấy tất cả role
	public function get_all_roles()
	{
		return $this->db->get($this->table)->result_array();
	}
	public function getRoles($limit, $offset)
	{
		$this->db->select('
        uuid,
        name,
        description,
        created_by,
        updated_by,
        deleted_by,
        created_at,
        updated_at,
        deleted_at
    ');
		$this->db->from('roles');
		$this->db->where('deleted_at IS NULL');
		$this->db->where('deleted_by IS NULL');
		$this->db->limit($limit, $offset);
		$query = $this->db->get();

		return $query->result_array();
	}

	// Lấy role theo id
	public function get_role_by_id($uuid)
	{
		return $this->db->where('uuid', $uuid)->get($this->table)->row_array();
	}

	// Thêm role
	public function insert_role($data)
	{
		return $this->db->insert($this->table, $data);
	}

	// Cập nhật role
	public function update_role($uuid, $data)
	{
		return $this->db->where('uuid', $uuid)->update($this->table, $data);
	}
	public function updateRoleByUuid($uuid, $data)
	{
		return $this->db->where('uuid', $uuid)->update('roles', $data);
	}

	// Xoá role
	public function delete_role($uuid)
	{
		return $this->db->where('uuid', $uuid)->delete($this->table);
	}
	public function counts()
	{
		$this->db->from($this->table);
		$this->db->where('deleted_at IS NULL');
		$this->db->where('deleted_by IS NULL');
		return $this->db->count_all_results();
	}
}
