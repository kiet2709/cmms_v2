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
		//add steps
        $content = json_encode($data['steps']);

        // Lấy category_id từ code
        $category = $this->db->get_where('categories', [
            'code' => $meta['category_code']
        ])->row_array();

        if (!$category) {
            $category = $this->db->get_where('categories', [
                'uuid' => $meta['category_code']
            ])->row_array();
        }

        if (!$category) {
            return $this->respond(400, ['error' => 'Invalid category code']);
        }

        if (!$meta['type'] || !$meta['description'] || !$meta['frequency']) {
            return $this->respond(400, ['error' => $meta['type']]);
        }

        if (!$meta['frequency'] == 'Unit' && !$meta['unitType'] && !$meta['unitValue']) {
            return $this->respond(400, ['error' => 'Invalid unit']);
        }

        $category_id = $category['uuid'];

        // return $this->respond(400, ['error' => $meta]);



        // Sinh suffix với padding 6 chữ số
        if (!empty($meta['uuid'])) {
            // Lấy WI theo uuid
            $wi = $this->db->get_where('working_instructions',[
                'uuid' => $meta['uuid']
            ])->row_array();

            // return $this->respond(400, ['error' => $meta['uuid']]);

            if ($wi && !empty($wi['code'])) {
                // Tách số đuôi (6 số cuối)
                if (preg_match('/(\d{6})$/', $wi['code'], $matches)) {
                    $suffix = $matches[1]; // ví dụ: 000002
                } else {
                    // fallback nếu không match
                    $suffix = str_pad(1, 6, '0', STR_PAD_LEFT);
                }

                // Ghép prefix mới từ meta với số cũ
                $final_code = preg_replace('/XXXXXX$/', $suffix, $meta['code']);
            } else {
                throw new Exception("WI not found with uuid: " . $meta['uuid']);
            }

            $this->db->trans_start();
            $this->db->where('uuid', $meta['uuid']);
            $this->db->update('working_instructions', [
                'uuid' => $this->uuidv4(),
                'code'        => $final_code,
                'type'        => $meta['type'],
                'name'        => $meta['description'],
                'frequency'     => $meta['frequency'],
                'unit_value'     => $meta['unitValue'],
                'unit_type'     => $meta['unitType'],
                'category_id' => $category_id,
                'schema' => $content,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->db->trans_complete();

            $this->respond(200, [
                'code'        => $final_code,
                'type'        => $meta['type'],
                'name'        => $meta['description'],
                'category_id' => $category_id,
                'schema'      => $content,
            ]);


        } else {
                // Đếm số WI đã có trong DB
            $count = $this->db->count_all('working_instructions');
            // Logic cũ: tạo số mới
            $suffix = str_pad($count + 1, 6, '0', STR_PAD_LEFT);
            $final_code = preg_replace('/XXXXXX$/', $suffix, $meta['code']);
        
        
            $this->db->insert('working_instructions', [
                'uuid' => $this->uuidv4(),
                'code'        => $final_code,
                'type'        => $meta['type'],
                'name'        => $meta['description'],
                'frequency'     => $meta['frequency'],
                'unit_value'     => $meta['unitValue'],
                'unit_type'     => $meta['unitType'],
                'category_id' => $category_id,
                'schema' => $content,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->respond(200, [
                'code'        => $final_code,
                'type'        => $meta['type'],
                'name'        => $meta['description'],
                'category_id' => $category_id,
                'schema'      => $content,
            ]);
        
        }        

        //TODO: for audit
        // $uuid = get_jwt_sub();
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


    public function getWiByMachineId()
    {
        $e_id = $this->input->get('equipment_id');
        $ewi = $this->WorkingInstruction_model->getByMachineId($e_id);
        $result = [
            'status' => 'success',
            'message' => 'Lấy dữ liệu thành công',
            'data' => $ewi,
        ];

        return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($result));
    }

    public function getWiByCategoryId()
    {
        $category_id = $this->input->get('category_id');
        $wi = $this->WorkingInstruction_model->getByCategoryId($category_id);
        $result = [
            'status' => 'success',
            'message' => 'Lấy dữ liệu thành công',
            'data' => $wi,
        ];

        return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($result));
    }

    public function delete() {
        // Lấy JSON từ request body
        $data = json_decode($this->input->raw_input_stream, true);


        // Kiểm tra dữ liệu hợp lệ
        if (!isset($data['uuid']) || !isset($data['userId'])) {
            return $this->output
                        ->set_content_type('application/json')
                        ->set_status_header(400)
                        ->set_output(json_encode([
                            'success' => false,
                            'message' => 'Missing uuid or userId'
                        ]));
        }

        // Gọi model xóa WI
        $deleted = $this->WorkingInstruction_model->deletebyId(
            $data['uuid'], 
            $data['userId']
        );

        if ($deleted) {
            return $this->output
                        ->set_content_type('application/json')
                        ->set_status_header(200)
                        ->set_output(json_encode([
                            'success' => true,
                            'message' => 'Working instruction deleted successfully'
                        ]));
        } else {
            return $this->output
                        ->set_content_type('application/json')
                        ->set_status_header(500)
                        ->set_output(json_encode([
                            'success' => false,
                            'message' => 'Failed to delete working instruction'
                        ]));
        }
    }

}
