<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * @property Role_model $Role_model
 */
class RoleController extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Role_model');
	}

	// API: Lấy tất cả roles
	public function getRoles()
	{
		// $roles = $this->Role_model->get_all_roles();
		// Lấy limit & page từ request
		$limit = $this->input->get('limit') ?? 20;
		$page = $this->input->get('page') ?? 1;

		$offset = ($page - 1) * $limit;

		// Lấy dữ liệu phân trang
		$roles = $this->Role_model->getRoles($limit, $offset);
		$total_items = $this->Role_model->counts();
		$total_pages = ceil($total_items / $limit);

		$result = [
			'status' => 'success',
			'message' => 'Lấy dữ liệu thành công',
			'data' => $roles,
			'total_items' => count($roles),
			'total_pages' => $total_pages,
			'total_in_all_page' => $total_items
		];

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode($result));
	}

	// API: Lấy role theo ID
	public function getRoleById($uuid)
	{
		$role = $this->Role_model->get_role_by_id($uuid);
		echo json_encode($role);
	}

	// API: Thêm role
	public function createRole()
	{
		header('Content-Type: application/json');

		$data = json_decode(file_get_contents("php://input"), true);

		if (!$data) {
			echo json_encode([
				"status" => "error",
				"message" => "Dữ liệu không hợp lệ"
			]);
			return;
		}

		$insertData = [
			"uuid" => $this->uuid_v4(),
			"name"   => $data["name"],
			"description" => $data["description"],
			"created_at"        => date("Y-m-d H:i:s"),
			"created_by"        => "admin",
		];

		if ($this->db->insert("roles", $insertData)) {
			echo json_encode([
				"status" => "success",
				"message" => "User đã được thêm thành công"
			]);
		} else {
			echo json_encode([
				"status" => "error",
				"message" => "Không thể thêm user"
			]);
		}
	}

	// API: Cập nhật role
	public function updateRole()
	{
		$data = json_decode(file_get_contents("php://input"), true);
		$uuid = $data['uuid'];

		if (!$uuid) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(['message' => 'UUID is required']));
		}

		$dataa = array(
			'name'    => $data['name'],
			'description'  => $data['description'],
			'updated_by'         => 'admin',
			'updated_at' 		=> date("Y-m-d H:i:s"),
		);

		$updated = $this->Role_model->update_role($uuid, $dataa);
		log_message('debug', 'Update result: ' . json_encode($updated));
		if ($updated) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(['message' => 'User updated successfully']));
		} else {
			return $this->output
				->set_content_type('application/json')
				->set_status_header(400)
				->set_output(json_encode(['message' => 'Update failed']));
		}
	}

	// API: Xoá role
	public function deleteRole()
	{
		$uuid = $this->input->get('uuid');

		if (!$uuid) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(['message' => 'UUID is required']));
		}

		$this->load->model('Role_model');
		$role = $this->Role_model->get_role_by_id($uuid);
		if (!$role) {
			return $this->output
				->set_content_type('application/json')
				->set_status_header(404)
				->set_output(json_encode(['message' => 'Role not found']));
		}
		$data = [
			'deleted_at' 		=> date("Y-m-d H:i:s"),
			'deleted_by'			=> "admin",
		];

		// $this->Role_model->deleteUserByUuid($uuid);
		$this->Role_model->updateRoleByUuid($uuid, $data);

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode(['message' => 'Role deleted successfully']));
	}

	// Helper: Tạo UUID v4
	private function uuid_v4()
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
}
