<?php
require_once 'app/config/database.php';
require_once 'app/models/UserModel.php';
require_once 'app/helpers/JwtHelper.php';

class ApiAccountController
{
    private $userModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->userModel = new UserModel($this->db);
    }

    /**
     * POST /api/account/login
     */
    public function login()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Nhận dữ liệu JSON từ request body
        $input = json_decode(file_get_contents('php://input'), true);
        
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Vui lòng điền đầy đủ tên đăng nhập và mật khẩu.'
            ]);
            exit();
        }

        $user = $this->userModel->login($username, $password);

        if ($user) {
            // Chuẩn bị thông tin lưu vào Token (Payload)
            $payload = [
                'user_id' => $user->id,
                'username' => $user->username,
                'role' => $user->role ?? 'user'
            ];

            // Tạo mã JWT Token
            $token = JwtHelper::generate($payload);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Đăng nhập thành công.',
                'token' => $token,
                'user' => [
                    'username' => $user->username,
                    'role' => $user->role ?? 'user'
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Tên đăng nhập hoặc mật khẩu không chính xác.'
            ]);
        }
        exit();
    }

    /**
     * POST /api/account/register
     */
    public function register()
    {
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);

        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $confirm_password = $input['confirm_password'] ?? '';

        if (empty($username) || empty($password) || empty($confirm_password)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Vui lòng điền đầy đủ các thông tin đăng ký.'
            ]);
            exit();
        }

        if ($password !== $confirm_password) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Mật khẩu xác nhận không khớp.'
            ]);
            exit();
        }

        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Mật khẩu phải chứa ít nhất 6 ký tự.'
            ]);
            exit();
        }

        $result = $this->userModel->register($username, $password, 'user');

        if ($result === true) {
            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'message' => 'Đăng ký tài khoản thành công!'
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $result
            ]);
        }
        exit();
    }

    /**
     * GET /api/account/googleLogin
     */
    public function googleLogin()
    {
        $clientId = "382378400042-5kcacdm2tmk1f1bh752u55q1m152jfbg.apps.googleusercontent.com";
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $redirectUri = $protocol . $host . "/TranCongTien_4894/index.php?url=Account/googleCallback";
        
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => 'api_google_state'
        ];
        
        $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
        header("Location: " . $authUrl);
        exit();
    }

    /**
     * GET /api/account/githubLogin
     */
    public function githubLogin()
    {
        $clientId = "Ov23ling1RywvkEvKXAP";
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $redirectUri = $protocol . $host . "/TranCongTien_4894/index.php?url=Account/githubCallback";
        
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => 'user:email read:user',
            'state' => 'api_github_state'
        ];
        
        $authUrl = "https://github.com/login/oauth/authorize?" . http_build_query($params);
        header("Location: " . $authUrl);
        exit();
    }
}
?>
