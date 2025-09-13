<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('api_request')) {
    function api_request($method, $endpoint, $data = [], $headers = [])
    {
        // === Lấy base_url từ file config trực tiếp ===
        $apiConfig = include(APPPATH . 'config/api.php');
        $baseUrl   = rtrim($apiConfig['api_base_url'], '/');

        // build URL
        if (preg_match('#^https?://#', $endpoint)) {
            $url = $endpoint;
        } else {
            $url = $baseUrl . '/' . ltrim($endpoint, '/');
        }

        // === LOG URL TRƯỚC KHI CALL ===
        log_message('debug', 'API REQUEST → ' . $method . ' ' . $url);

        $ch = curl_init();
        $method = strtoupper($method);

        switch ($method) {
            case "POST":
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case "PUT":
            case "PATCH":
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case "GET":
                if (!empty($data)) {
                    $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($data);
                }
                break;
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        $headers = array_merge($defaultHeaders, $headers);

        $formatted = [];
        foreach ($headers as $k => $v) {
            $formatted[] = is_string($k) ? "$k: $v" : $v;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $formatted);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

                        // === LOG LỖI CURL ===
            log_message('error', 'API REQUEST ERROR: ' . $error);
            return [
                'status'  => 0,
                'error'   => $error,
                'body'    => null,
                'raw'     => null,
                'headers' => []
            ];
        }

        $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $rawHeader  = substr($response, 0, $headerSize);
        $rawBody    = substr($response, $headerSize);

        curl_close($ch);

                // === LOG RESPONSE STATUS + BODY ===
        log_message('debug', "API RESPONSE ($httpCode): " . $rawBody);
        return [
            'status'  => $httpCode,
            'error'   => null,
            'body'    => json_decode($rawBody, true),
            'raw'     => $rawBody,
            'headers' => explode("\r\n", trim($rawHeader))
        ];
    }
}
