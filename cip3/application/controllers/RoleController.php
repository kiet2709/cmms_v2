<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @property Role_model $Role_model
 */
class RoleController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Role_model');
    }

    // API: Lấy tất cả roles
    public function getRoles() {
        $roles = $this->Role_model->get_all_roles();
        echo json_encode($roles);
    }

    // API: Lấy role theo ID
    public function getRoleById($uuid) {
        $role = $this->Role_model->get_role_by_id($uuid);
        echo json_encode($role);
    }

    // API: Thêm role
    public function createRole() {
        $data = array(
            'uuid' => $this->input->post('uuid') ?? $this->uuid_v4(),
            'name' => $this->input->post('name'),
            'description' => $this->input->post('description')
        );
        $this->Role_model->insert_role($data);
        echo json_encode(['status' => 'success']);
    }

    // API: Cập nhật role
    public function updateRole($uuid) {
        $data = array(
            'name' => $this->input->post('name'),
            'description' => $this->input->post('description')
        );
        $this->Role_model->update_role($uuid, $data);
        echo json_encode(['status' => 'success']);
    }

    // API: Xoá role
    public function deleteRole($uuid) {
        $this->Role_model->delete_role($uuid);
        echo json_encode(['status' => 'success']);
    }

    // Helper: Tạo UUID v4
    private function uuid_v4() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
