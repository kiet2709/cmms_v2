<?php
defined('BASEPATH') or exit('No direct script access allowed');

class UserController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
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

    public function getProfile()
    {
        // Lấy Authorization header
        $authHeader = $this->input->get_request_header('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->respond(404, ['error' => 'Authorization token not found']);
        }

        $token = $matches[1];

        try {
            // Giải mã token
            $decoded = $this->jwt->decode($token);

            // $this->respond(200, ['data' => $decoded]);

            // Giả sử payload có uuid
            $uuid = $decoded['sub'] ?? null;
            // $this->respond(200, ['data' => $uuid]);
            if (!$uuid) {
                return $this->output
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'UUID not found in token']));
            }

            // Lấy user theo uuid
            $user = $this->User_model->find_by_uuid($uuid);

            if (!$user) {
                $this->respond(404, ['error' => 'miss jwt']);
            }

            $role = $this->User_model->get_role($user['role_id']);

            $data = [
                'user' => $user,
                'role' => $role->name,
            ];

            $this->respond(200, $data);



        } catch (Exception $e) {
            $this->respond(401, ['error' => 'Invalid token', 'message' => $e->getMessage()]);
        }
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