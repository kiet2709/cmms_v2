<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class JWT {
    protected $key;

    public function __construct() {
        $CI = &get_instance();
        $this->key = $CI->config->item('jwt_key');
    }

    protected function b64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function b64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public function encode(array $payload, $alg = 'HS256') {
        $header = ['typ' => 'JWT', 'alg' => $alg];
        $segments = [
            $this->b64url_encode(json_encode($header)),
            $this->b64url_encode(json_encode($payload))
        ];
        $signing_input = implode('.', $segments);
        $signature = hash_hmac('sha256', $signing_input, $this->key, true);
        $segments[] = $this->b64url_encode($signature);
        return implode('.', $segments);
    }

    public function decode($jwt) {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) throw new Exception('Invalid token');
        list($headb64, $payb64, $sigb64) = $parts;

        $header  = json_decode($this->b64url_decode($headb64), true);
        $payload = json_decode($this->b64url_decode($payb64), true);
        $signature = $this->b64url_decode($sigb64);

        // verify signature
        $expected = hash_hmac('sha256', "$headb64.$payb64", $this->key, true);
        if (!hash_equals($expected, $signature)) throw new Exception('Signature mismatch');

        // verify exp (nếu có)
        if (isset($payload['exp']) && time() >= $payload['exp']) {
            throw new Exception('Token expired');
        }
        return $payload;
    }
}
