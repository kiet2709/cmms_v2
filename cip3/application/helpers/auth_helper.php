<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('get_jwt_sub')) {
    function get_jwt_sub()
    {
        $CI =& get_instance();

        // Lấy Authorization header
        $authHeader = $CI->input->get_request_header('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            show_error(json_encode(['error' => 'Authorization token not found']), 401, 'Unauthorized');
        }

        $token = $matches[1];

        try {
            $decoded = $CI->jwt->decode($token);

            $uuid = $decoded['sub'] ?? null;
            if (!$uuid) {
                show_error(json_encode(['error' => 'UUID not found in token']), 400, 'Bad Request');
            }

            return $uuid;
        } catch (Exception $e) {
            show_error(json_encode(['error' => 'Invalid token', 'message' => $e->getMessage()]), 401, 'Unauthorized');
        }
    }
}