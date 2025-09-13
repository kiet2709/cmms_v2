<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard_model extends CI_Model
{

    protected $tableDailyTask = 'daily_tasks';

	public function getStatistic()
	{
         $today = date('Y-m-d');

        $this->db->from($this->tableDailyTask);
        $this->db->group_start()
            ->where('DATE(date_start)', $today)
            ->or_group_start()
                ->where('DATE(inspected_date)', $today)
            ->group_end()
            ->or_group_start()
                ->where('inspected_date IS NULL')
            ->group_end()
        ->group_end();
        
        $dailyInspection = $this->db->count_all_results();

        $maintenance = 0;

        $this->db->from('daily_tasks');
        $this->db->where('deleted_at', NULL);
        $this->db->where('deleted_by', NULL);

        // Task không phải hôm nay
        $this->db->where('date_start !=', $today);

        // Task chưa được kiểm tra
        $this->db->where('inspector_id', NULL);
        $this->db->where('status', NULL);
        $this->db->where('inspected_date', NULL);

        $overdue = $this->db->count_all_results();


		return [
            'daily_inspection' => $dailyInspection,
            'maintenance' => $maintenance,
            'overdue' => $overdue
        ];
	}

	public function counts()
	{
		$this->db->from($this->table);
		$this->db->where('deleted_at IS NULL');
		$this->db->where('deleted_by IS NULL');
		return $this->db->count_all_results();
	}
}
