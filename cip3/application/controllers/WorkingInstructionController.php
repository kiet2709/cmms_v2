<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WorkingInstructionController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('WorkingInstruction_model');
        $this->load->helper(['image', 'url', 'form', 'string']); // Load the image helper
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


    // 📌 Upload ảnh
    public function upload()
    {
        if (!isset($_FILES['file'])) {
            return $this->output->set_output(json_encode(['error' => 'No file']));
        }

        $path = $this->WorkingInstruction_model->upload_single_file($_FILES['file']);
        $url = base_url($path);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['url' => $url]));
    }

    // 📌 Xóa ảnh
    public function delete_image()
    {
        $input = json_decode($this->input->raw_input_stream, true);
        $path = $_GET['path'];


        if ($path) {
            // Nếu path là full URL thì parse domain ra
            if (strpos($path, 'http') === 0) {
                $parsed = parse_url($path);
                $path = ltrim($parsed['path'], '/'); // "/cip3/uploads/..."
                
                // Bỏ tiền tố "cip3/" nếu có
                if (strpos($path, 'cip3/') === 0) {
                    $path = substr($path, strlen('cip3/')); // => "uploads/..."
                }
            }


            $filePath = FCPATH . $path;


            if (file_exists($filePath)) {
                unlink($filePath);
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'deleted']));
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['error' => $path]));
    }


    private function uuidv4() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    // 📌 Lưu form
    public function save()
    {
        $data = json_decode($this->input->raw_input_stream, true);
        
        
        $meta = $data['meta'];
        $content = json_encode($data['content']);


        

        $this->db->insert('working_instructions', [
            'uuid' => $this->uuidv4(),
            'code' => $meta['code'],
            'type' => $meta['type'],
            'name' => $meta['description'],
            'schema' => $content,
        ]);

        $this->respond(200, [
            'code' => $meta['code'],
            'type' => $meta['type'],
            'name' => $meta['description'],
            'schema' => $content,
        ]);

        // return $this->output
        //     ->set_content_type('application/json')
        //     ->set_output(json_encode(['status' => 'ok']));
    }

    public function getAllWi()
    {
        // Lấy limit & page từ request
        $limit = $this->input->get('limit') ?? 20;
        $page = $this->input->get('page') ?? 1;

        $offset = ($page - 1) * $limit;

        // Lấy dữ liệu phân trang
        $users = $this->WorkingInstruction_model->getAllWi($limit, $offset);
        $total_items =  $this->WorkingInstruction_model->counts();
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

        public function getWiById()
    {
        $wi_id = $this->input->get('id');
        $wi = $this->WorkingInstruction_model->getById($wi_id);
        $result = [
            'status' => 'success',
            'message' => 'Lấy dữ liệu thành công',
            'data' => $wi,
        ];

        return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($result));
    }



}