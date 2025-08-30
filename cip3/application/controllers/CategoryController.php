<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CategoryController extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Category_model');
		$this->load->library('JWT');
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

	public function getAllCategories()
	{
		// Lấy limit & page từ request
		$limit = $this->input->get('limit') ?? 20;
		$page = $this->input->get('page') ?? 1;

		$offset = ($page - 1) * $limit;

		// Lấy dữ liệu phân trang
		$categories = $this->Category_model->getCategories($limit, $offset);
		$total_items =  $this->Category_model->counts();
		$total_pages = ceil($total_items / $limit);

		$result = [
			'status' => 'success',
			'message' => 'Lấy dữ liệu thành công',
			'data' => $categories,
			'total_items' => count($categories),
			'total_pages' => $total_pages,
			'total_in_all_page' => $total_items
		];

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode($result));
	}
	public function createCategory()
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
			"code"   => $data["code"],
			"name" => $data["name"],
			"created_by" => "admin",
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
