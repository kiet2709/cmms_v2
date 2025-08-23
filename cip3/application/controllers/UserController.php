<?php
defined('BASEPATH') or exit('No direct script access allowed');

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

}