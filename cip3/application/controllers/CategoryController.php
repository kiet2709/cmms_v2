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

}