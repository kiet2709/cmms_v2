<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AuthHook {

   public function testApiCall() {

        // Nạp helper thủ công (không dùng $CI->load->helper)
        require_once APPPATH . 'helpers/api_helper.php';

        $res = api_request('GET', '?c=TestController&m=hello');

        echo "Status: " . $res['status'] . "\n";
        echo "Message: " . ($res['body']['message'] ?? 'N/A') . "\n";
        exit;
   }
}
