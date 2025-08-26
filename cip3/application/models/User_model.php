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
		$this->db->select('users.uuid, username, employment_id, employment_name, last_login, last_logout, position, users.created_at, roles.name as role');
		$this->db->from($this->table);
		$this->db->join('roles', 'roles.uuid = users.role_id', 'left');
		$this->db->limit($limit, $offset);
		$query = $this->db->get();
		return $query->result_array();
	}

	public function counts()
	{
		return $this->db->count_all($this->table);
	}
}
