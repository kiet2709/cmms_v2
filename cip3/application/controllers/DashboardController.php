<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Dashboard_model $Dashboard_model
 * @property CI_Output $output
 */
class DashboardController extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Dashboard_model');
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

	private function uuidv4()
	{
		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff)
		);
	}

    public function getStatistic()
	{

		$statistic = $this->Dashboard_model->getStatistic();

		$result = [
			'status' => 'success',
			'message' => 'Lấy dữ liệu thành công',
			'data' => $statistic,
		];

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode($result));
	}

}
