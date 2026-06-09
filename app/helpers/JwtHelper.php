<?php
class JwtHelper
{
    private static $secret_key = "my_super_secret_jwt_key_123!@#"; // Khóa bí mật dùng để ký token

    /**
     * Tạo mã JWT Token
     *
     * @param array $payload Dữ liệu lưu trữ trong token
     * @param int $expiry_seconds Thời gian hết hạn của token (giây)
     * @return string Chuỗi JWT Token
     */
    public static function generate($payload, $expiry_seconds = 86400) // Mặc định hết hạn sau 24h
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiry_seconds;
        $payload_json = json_encode($payload);
        
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload_json);
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Xác thực và giải mã JWT Token
     *
     * @param string $jwt Chuỗi JWT Token cần xác thực
     * @return array|false Dữ liệu payload nếu hợp lệ, false nếu không hợp lệ
     */
    public static function validate($jwt)
    {
        if (empty($jwt)) {
            return false;
        }

        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) !== 3) {
            return false;
        }
        
        $header = base64_decode(self::base64UrlDecode($tokenParts[0]));
        $payload = base64_decode(self::base64UrlDecode($tokenParts[1]));
        $signature_provided = $tokenParts[2];
        
        // Kiểm tra thời gian hết hạn (exp)
        $payload_obj = json_decode($payload);
        if (!$payload_obj || (isset($payload_obj->exp) && $payload_obj->exp < time())) {
            return false;
        }
        
        // Tái dựng chữ ký để đối chiếu
        $base64UrlHeader = $tokenParts[0];
        $base64UrlPayload = $tokenParts[1];
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        if (hash_equals($base64UrlSignature, $signature_provided)) {
            return json_decode($payload, true);
        }
        
        return false;
    }

    private static function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        return str_replace(['-', '_'], ['+', '/'], $data);
    }
}
?>
