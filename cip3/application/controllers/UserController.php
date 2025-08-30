<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * @property User_model $User_model
 */
class UserController extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_model');
		$this->load->library('JWT');
		$this->load->helper('auth');
	}

	private function respond($status_code, $data)
	{
		$this->output
			->set_status_header($status_code)
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))
			->_display();
		exit;
	}

	public function getProfile()
	{
		$uuid = get_jwt_sub();

		$user = $this->User_model->find_by_uuid($uuid);
		if (!$user) {
			$this->respond(404, ['error' => 'User not found']);
		}

		$role = $this->User_model->get_role($user['role_id']);

		$this->respond(200, [
			'user' => $user,
			'role' => $role->name,
		]);
	}

	public function getAllUsers()
	{
		// Lấy limit & page từ request
		$limit = $this->input->get('limit') ?? 20;
		$page = $this->input->get('page') ?? 1;

		$offset = ($page - 1) * $limit;

		// Lấy dữ liệu phân trang
		$users = $this->User_model->getUsers($limit, $offset);
		$total_items = $this->User_model->counts();
		$total_pages = ceil($total_items / $limit);

		$result = [
			'status' => 'success',
			'message' => 'Lấy dữ liệu thành công',
			'data' => $users,
			'total_items' => count($users),
			'total_pages' => $total_pages,
			'total_in_all_page' => $total_items
		];

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode($result));
	}
	public function createUser()
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
			"uuid" => $this->uuidv4(),
			"employment_id"   => $data["employment_id"],
			"employment_name" => $data["employment_name"],
			"username"        => $data["username"],
			"position"        => $data["position"],
			"role_id"          => $data["role_id"],
			"password"        => password_hash($data["password"], PASSWORD_BCRYPT), // mã hóa password
			"created_at"      => date("Y-m-d H:i:s"),
		];

		if ($this->db->insert("users", $insertData)) {
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
	//////

	public function updateUser()
	{
		$data = json_decode(file_get_contents("php://input"), true);
		$uuid = $data['uuid'];

		if (!$uuid) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(['message' => 'UUID is required']));
		}

		$dataa = array(
			'employment_id'    => $data['employment_id'],
			'employment_name'  => $data['employment_name'],
			'username'         => $data['username'],
			'position'         => $data['position'],
			'role_id'             => $data['role_id'],
			'updated_at' 		=> date("Y-m-d H:i:s"),
			'updated_by'			=> 'hyuyyyy'
		);

		// nếu password có nhập thì mới update
		if ($data['password']) {
			$dataa['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
		}


		$updated = $this->User_model->updateUserByUuid($uuid, $data);
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


	////////
	public function deleteUser()
	{
		$uuid = $this->input->get('uuid');

		if (!$uuid) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(['message' => 'UUID is required']));
		}

		$this->load->model('User_model');
		$user = $this->User_model->find_by_uuid($uuid);
		if (!$user) {
			return $this->output
				->set_content_type('application/json')
				->set_status_header(404)
				->set_output(json_encode(['message' => 'User not found']));
		}
		$data = [
			'deleted_at' 		=> date("Y-m-d H:i:s"),
			'deleted_by'			=> "aa",
		];

		// $this->User_model->deleteUserByUuid($uuid);
		$this->User_model->updateUserByUuid($uuid, $data);

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode(['message' => 'User deleted successfully']));
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
}
