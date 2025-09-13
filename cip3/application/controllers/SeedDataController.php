<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property SeedData_model $SeedData_model
 * @property CI_Output $output
 */
class SeedDataController extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('SeedData_model');
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

    public function getProduction()
	{

		// Lấy dữ liệu phân trang
		$users = $this->SeedData_model->getProduction();
		// $total_items = $this->User_model->counts();
		// $total_pages = ceil($total_items / $limit);

		$result = [
			'status' => 'success',
			'message' => 'Lấy dữ liệu thành công',
			'data' => $users,
			'total_items' => count($users),

		];

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode($result));
	}

    public function getCentral()
	{

		// Lấy dữ liệu phân trang
		$users = $this->SeedData_model->getCentral();
		// $total_items = $this->User_model->counts();
		// $total_pages = ceil($total_items / $limit);

		$result = [
			'status' => 'success',
			'message' => 'Lấy dữ liệu thành công',
			'data' => $users,
			'total_items' => count($users),

		];

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode($result));
	}

    public function getCombineCentralAndProduction()
    {
        $data = $this->SeedData_model->getCombineCentralAndProduction();
        
        $result = [
			'status' => 'success',
			'message' => 'Lấy dữ liệu thành công',
			'data' => $data,
			'total_items' => count($data),

		];

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode($result));
    }


    public function getCategories()
    {
        $data = $this->SeedData_model->getCategories();
        
        $result = [
			'status' => 'success',
			'message' => 'Lấy dữ liệu thành công',
			'data' => $data,
			'total_items' => count($data),

		];
        

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode($result));
    }

    public function seedMachine()
    {
        //lấy category và machine từ 2 database kia
        $listCategory = $this->SeedData_model->getCategories();
        $listMachine = $this->SeedData_model->getCombineCentralAndProduction();

        $categoryInsert = [];
        foreach ($listCategory as $cat) {
            $categoryInsert[] = [
                'uuid'       => $this->uuidv4(),
                'code'              => $cat['code'] ?? null, // ánh xạ uuid
                'name'           => $cat['name'],
            ];
        }

        //insert category vào db cmms
        $this->SeedData_model->seedCategory($categoryInsert);

        //lấy category từ table categories ra
        $categories = $this->SeedData_model->getCategoriesFromCMMS();

        //tạo 1 mảng lấy uuid theo code của category
        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[$cat['code']] = $cat['uuid'];
        }

        $insertData = [];

        //tạo mảng machine có category_id là uuid của category trên
        foreach ($listMachine as $m) {
            // lấy code từ machine_id (chữ cái đầu đến khi gặp số)
            preg_match('/^[A-Za-z]+/', $m['machine_id'], $matches);
            $code = $matches[0] ?? null;

            $insertData[] = [
                'uuid' => $this->uuidv4(),
                'machine_id'       => $m['machine_id'],
                'category_id'      => $categoryMap[$code] ?? null, // ánh xạ uuid
                'cavity'           => $m['cavity'],
                'model'            => $m['model'],
                'manufacturer'     => $m['manufacturer'],
                'manufacturing_date' => $m['manufacturing_date']
            ];
        }

        return $this->SeedData_model->seedMachine($insertData);
    }

    public function insertCount()
    {
        $data = $this->SeedData_model->insertCount();
        
        $result = [
			'status' => 'success',
			'message' => 'Lấy dữ liệu thành công',
			'data' => $data,
			'total_items' => count($data),

		];
        

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode($result));
    }
}
