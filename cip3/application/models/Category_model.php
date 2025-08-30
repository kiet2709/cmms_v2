<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Category_model extends CI_Model
{
	protected $table = 'categories';

	public function getCategories($limit, $offset)
	{
		$this->db->select('uuid as id, name, code');
		$this->db->from($this->table);
		$this->db->where('deleted_at IS NULL');
		$this->db->where('deleted_by IS NULL');
		$this->db->limit($limit, $offset);
		$query = $this->db->get();
		return $query->result_array();
	}

	public function counts()
	{
		$this->db->from($this->table);
		$this->db->where('deleted_at IS NULL');
		$this->db->where('deleted_by IS NULL');
		return $this->db->count_all_results();
	}
}
