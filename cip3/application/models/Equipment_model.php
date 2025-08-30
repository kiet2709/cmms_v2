<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Equipment_model extends CI_Model {
    protected $table = 'equipments';

    public function getEquipments($limit, $offset)
    {
        $this->db->select('equipments.uuid, machine_id, family, model, cavity, manufacturer, manufacturing_date, history_count, unit, categories.name as category');
        $this->db->from($this->table);
         $this->db->join('categories', 'categories.uuid = equipments.category_id', 'left');
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function counts()
    {
        return $this->db->count_all($this->table);
    }

    public function getById($equipment_id)
    {
        $this->db->select('equipments.uuid, machine_id, family, model, cavity, manufacturer, manufacturing_date, history_count, unit, categories.name as category, categories.uuid as category_id');
        $this->db->from($this->table);
         $this->db->join('categories', 'categories.uuid = equipments.category_id', 'left');
        $this->db->where('equipments.uuid', $equipment_id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function insertEquipment($data) {
        return $this->db->insert('equipments', $data);
    }

    public function insertWorkingInstructions($equipmentId, $wiList) {
        if (empty($wiList)) return;

        $batch = [];
        foreach ($wiList as $wiId) {
            $batch[] = [
                'equipment_id'       => $equipmentId,
                'working_instruction_id' => $wiId,
                'created_at'         => date('Y-m-d H:i:s')
            ];
        }
        $this->db->insert_batch('equipment_working_instructions', $batch);
    }

    private function generate_uuid() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function updateEquipment($uuid, $data) {
        $this->db->where('uuid', $uuid);
        return $this->db->update('equipments', $data);
    }

    // public function updateWorkingInstructions($equipmentId, $newWiList) {
    //     // Lấy toàn bộ WI hiện tại trong DB (kể cả deleted_at)
    //     $this->db->select('working_instruction_id, deleted_at');
    //     $this->db->from('equipment_working_instructions');
    //     $this->db->where('equipment_id', $equipmentId);
    //     $query = $this->db->get();
    //     $existing = $query->result_array();

    //     $existingMap = [];
    //     foreach ($existing as $row) {
    //         $existingMap[$row['working_instruction_id']] = $row['deleted_at'];
    //     }

    //     // convert thành array chỉ số
    //     $newWiList = $newWiList ?: [];

    //     // 1. Xử lý WI bị bỏ đi → set deleted_at = NOW()
    //     foreach ($existingMap as $wiId => $deletedAt) {
    //         if (!in_array($wiId, $newWiList)) {
    //             $this->db->where('equipment_id', $equipmentId);
    //             $this->db->where('working_instruction_id', $wiId);
    //             $this->db->update('equipment_working_instructions', [
    //                 'deleted_at' => date('Y-m-d H:i:s')
    //             ]);
    //         }
    //     }

    //     // 2. Xử lý WI được giữ lại hoặc thêm mới
    //     foreach ($newWiList as $wiId) {
    //         if (isset($existingMap[$wiId])) {
    //             // Nếu đang có mà bị đánh dấu deleted_at → khôi phục
    //             if ($existingMap[$wiId] !== null) {
    //                 $this->db->where('equipment_id', $equipmentId);
    //                 $this->db->where('working_instruction_id', $wiId);
    //                 $this->db->update('equipment_working_instructions', [
    //                     'deleted_at' => null
    //                 ]);
    //             }
    //         } else {
    //             // chưa có → insert mới
    //             $this->db->insert('equipment_working_instructions', [
    //                 'equipment_id' => $equipmentId,
    //                 'working_instruction_id' => $wiId,
    //                 'created_at' => date('Y-m-d H:i:s'),
    //                 'deleted_at' => null
    //             ]);
    //         }
    //     }
    // }


    public function updateWorkingInstructions($equipmentId, $newWiList)
    {
        // Xóa sạch WI cũ
        $this->db->where('equipment_id', $equipmentId);
        $this->db->delete('equipment_working_instructions');

        // Nếu list rỗng thì thôi
        if (empty($newWiList)) return;

        // Chuẩn bị dữ liệu insert batch
        $batch = [];
        foreach ($newWiList as $wiId) {
            $batch[] = [
                'equipment_id'          => $equipmentId,
                'working_instruction_id'=> $wiId,
                'created_at'            => date('Y-m-d H:i:s'),
            ];
        }

        // Insert mới toàn bộ
        $this->db->insert_batch('equipment_working_instructions', $batch);
    }

    public function deleteObsoleteTasks($equipmentId, $newWiList) {
        // Xử lý ngày hôm nay
        $today = date('Y-m-d');

        // Xóa những task không còn trong newWiList
        $this->db->where('equipment_id', $equipmentId);
        
        // NOT IN wi_id
        if (!empty($newWiList)) {
            $this->db->where_not_in('wi_id', $newWiList);
        }

        // Điều kiện OR: (date_start = today OR inspected_date IS NULL OR status IS NULL)
        $this->db->group_start();
        $this->db->where('date_start', $today);
        $this->db->or_where('inspected_date IS NULL', null, false);
        $this->db->or_where('status IS NULL', null, false);
        $this->db->group_end();

        // Thực hiện DELETE
        return $this->db->delete('daily_tasks');
    }
}