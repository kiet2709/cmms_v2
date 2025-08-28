<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model
{
	protected $table = 'users';

	public function find_by_username($username)
	{
		return $this->db->get_where('users', ['username' => $username])->row_array();
	}

	public function find_by_uuid($uuid)
	{
		return $this->db->get_where('users', ['uuid' => $uuid])->row_array();
	}

	public function get_role_by_user_id($user_id)
	{
		$this->db->select('roles.*');
		$this->db->from('users');
		$this->db->join('roles', 'roles.uuid = users.role_id');
		$this->db->where('users.uuid', $user_id);
		return $this->db->get()->row_array();
	}

	public function get_role($role_id)
	{
		return $this->db->where('uuid', $role_id)->get('roles')->row();
	}


	public function getUsers($limit, $offset)
	{
		$this->db->select('users.uuid, username, employment_id, employment_name, last_login, last_logout, position, users.created_at, roles.name as role_name,roles.uuid as role_uuid');
		$this->db->from($this->table);
		$this->db->join('roles', 'roles.uuid = users.role_id', 'left');
		$this->db->where('users.deleted_at IS NULL');
		$this->db->where('users.deleted_by IS NULL');
		$this->db->limit($limit, $offset);
		$query = $this->db->get();
		return $query->result_array();
	}
	public function insertUsers($data)
	{
		return $this->db->insert('users', $data);
	}
	public function deleteUserByUuid($uuid)
	{
		return $this->db->where('uuid', $uuid)->delete('users');
	}
	public function updateUserByUuid($uuid, $data)
	{
		return $this->db->where('uuid', $uuid)->update('users', $data);
	}

	public function counts()
	{
		$this->db->from($this->table);
		$this->db->where('deleted_at IS NULL');
		$this->db->where('deleted_by IS NULL');
		return $this->db->count_all_results();
	}
}
