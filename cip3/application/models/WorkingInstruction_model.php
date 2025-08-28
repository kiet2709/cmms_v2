<?php
defined('BASEPATH') or exit('No direct script access allowed');

class WorkingInstruction_model extends CI_Model
{
    protected $table = 'working_instructions';

    public function getAllWi($limit, $offset)
    {
        $this->db->select('uuid as id, code, name, type, schema, updated_at, frequency, unit_type, unit_value');
        $this->db->from($this->table);
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function counts()
    {
        return $this->db->count_all($this->table);
    }

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['image', 'url', 'form', 'string']);
    }

    /**
     * Tải lên một file duy nhất
     * @param array $file mảng file đã được định dạng lại từ $_FILES
     * @return string đường dẫn đã lưu
     */
    public function upload_single_file($file)
    {
            // Đây là thư mục thật trên server (path vật lý)
    $uploadPath = FCPATH . 'uploads/working_instructions/'; 
    // FCPATH = D:\xampp\htdocs\cip3\

    // Đảm bảo thư mục tồn tại
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
        
        // Gọi hàm helper save_image với tham số là mảng file của từng tệp
        return save_image($file, $uploadPath);
    }


       /**
     * Lưu form data và image paths vào database
     * @param array $data Form data (code, type, description, schema)
     * @param array $image_paths Array of relative image paths
     * @return string UUID của record working_instructions
     */
    public function save_form($data, $image_paths)
    {
        // Generate UUID for working_instructions
        $uuid = $this->uuidv4(); // Using CI's string helper

        // Save to working_instructions
        $form_data = [
            'uuid' => $uuid,
            'code' => $data['code'],
            'type' => $data['type'],
            'name' => $data['description'],
            'schema' => $data['schema']
        ];
        $this->db->insert($this->table, $form_data);

        // Save image paths to wi_images
        foreach ($image_paths as $path) {
            $image_data = [
                'uuid' => random_string('uuid'), // New UUID for each image
                'wi_id' => $uuid, // Link to working_instructions UUID
                'path' => $path
            ];
            $this->db->insert($this->image_table, $image_data);
        }

        return $uuid;
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

    public function getById($id) {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('uuid', $id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getByMachineId($machineId)
    {
        $this->db->select('wi.uuid as wi_id, wi.code, wi.name, wi.type, wi.schema, e.uuid as equipment_id');
        $this->db->from('working_instructions wi');
        $this->db->join('equipment_working_instructions ewi', 'wi.uuid = ewi.working_instruction_id');
        $this->db->join('equipments e', 'e.uuid = ewi.equipment_id');
        $this->db->where('e.uuid', $machineId);
        $query = $this->db->get();
        $records = $query->result();
        return $records;
    }

    public function getByCategoryId($categoryId)
    {
        $this->db->select('*');
        $this->db->from('working_instructions wi');
        $this->db->where('wi.category_id', $categoryId);
        $query = $this->db->get();
        $records = $query->result();
        return $records;
    }

}