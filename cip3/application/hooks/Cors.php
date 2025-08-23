<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cors {
    public function enableCors() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

        // Nếu request là OPTIONS thì trả về 200 luôn, không cho vào controller
        // if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        //     exit(0);
        // }
                // Nếu là preflight thì dừng tại đây, không để CI route nữa
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}