<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AuthController extends CI_Controller
{
    public function hello()
    {
        // Đây là logic xử lý cho route /api/hello
        $data = [
            'status' => 'success',
            'message' => 'Hello World from CI3 API!'
        ];

        // Trả JSON response
        header('Content-Type: application/json');
        echo json_encode($data);
    }


    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('DailyTask_model');
        $this->load->model('Maintenance_model');
        $this->load->library('JWT');
        $this->load->helpers('api');
    }

    // POST /index.php/api/login
    // Body JSON: { "username": "...", "password": "..." }
    public function login()
    {
        // // đọc JSON body
        // $input = json_decode($this->input->raw_input_stream, true);
        // $username = isset($input['username']) ? trim($input['username']) : '';
        // $password = isset($input['password']) ? $input['password'] : '';

        // if ($username === '' || $password === '') {
        //     $data = ['error' => 'username & password are required'];

        //     $this->respond(422, $data);

        // }

        // $result = api_request(
        //     'POST', 
        //     '?c=AuthController&m=login',
        //     [
        //         'username' => $username,
        //         'password' => $password
        //     ]
        // );;


        // $this->DailyTask_model->insert_daily_tasks();

        // $this->respond(200, $result['body']);

        $input = json_decode($this->input->raw_input_stream, true);
        $username = isset($input['username']) ? trim($input['username']) : '';
        $password = isset($input['password']) ? $input['password'] : '';

        if ($username === '' || $password === '') {
            $data = ['error' => 'username & password are required'];

            $this->respond(422, $data);

        }

        $user = $this->User_model->find_by_username($username);
        if (!$user || !password_verify($password, $user['password'])) {
            $data = [
                'error' => 'invalid username or password'
            ];
            $this->respond(401, $data);
        }

        // tạo JWT
        $now = time();
        $payload = [
            'sub' => $user['uuid'],
            'username' => $user['username'],
            'role' => $this->User_model->get_role($user['uuid'])['name'],
            'iat' => $now,
            'exp' => $now + 2 * 60 * 60, // hết hạn sau 2 giờ
        ];
        $token = $this->jwt->encode($payload);
        

        $data = [
            'accessToken' => $token,
            'expires_in' => 2 * 60 * 60
        ];

        $this->DailyTask_model->insert_daily_tasks();
        $this->Maintenance_model->cronjobInsertMaintenance();

        // Trả JSON response
        header('Content-Type: application/json');
        echo json_encode($data);
    }


    public function logout()
    {
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "success",
            "message" => "Logout thành công"
        ]);
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

}