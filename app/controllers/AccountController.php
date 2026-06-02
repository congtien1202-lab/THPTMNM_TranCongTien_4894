<?php
require_once 'app/config/database.php';
require_once 'app/models/UserModel.php';
require_once 'app/helpers/SessionHelper.php';

class AccountController
{
    private $userModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->userModel = new UserModel($this->db);
    }

    public function login()
    {
        if (SessionHelper::isLoggedIn()) {
            header('Location: /TranCongTien_4894/index.php?url=Product/list');
            exit();
        }

        $errors = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $errors = 'Vui lòng điền đầy đủ thông tin.';
            } else {
                $user = $this->userModel->login($username, $password);
                if ($user) {
                    SessionHelper::login($user);
                    header('Location: /TranCongTien_4894/index.php?url=Product/list');
                    exit();
                } else {
                    $errors = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
                }
            }
        }

        include 'app/views/account/login.php';
    }

    public function register()
    {
        if (SessionHelper::isLoggedIn()) {
            header('Location: /TranCongTien_4894/index.php?url=Product/list');
            exit();
        }

        $errors = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($username) || empty($password) || empty($confirm_password)) {
                $errors = 'Vui lòng điền đầy đủ các thông tin.';
            } elseif ($password !== $confirm_password) {
                $errors = 'Mật khẩu xác nhận không khớp.';
            } elseif (strlen($password) < 6) {
                $errors = 'Mật khẩu phải chứa ít nhất 6 ký tự.';
            } else {
                $result = $this->userModel->register($username, $password, 'user');
                if ($result === true) {
                    $_SESSION['register_success'] = 'Đăng ký tài khoản thành công! Hãy đăng nhập.';
                    header('Location: /TranCongTien_4894/index.php?url=Account/login');
                    exit();
                } else {
                    $errors = $result;
                }
            }
        }

        include 'app/views/account/register.php';
    }

    public function logout()
    {
        SessionHelper::logout();
        header('Location: /TranCongTien_4894/index.php?url=Product/list');
        exit();
    }

    private function getGoogleRedirectUri()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . $host . "/TranCongTien_4894/index.php?url=Account/googleCallback";
    }

    public function googleLogin()
    {
        $clientId = "382378400042-5kcacdm2tmk1f1bh752u55q1m152jfbg.apps.googleusercontent.com";
        $redirectUri = $this->getGoogleRedirectUri();
        
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => 'google_oauth_state'
        ];
        
        $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
        header("Location: " . $authUrl);
        exit();
    }

    public function googleCallback()
    {
        if (isset($_GET['code'])) {
            $code = $_GET['code'];
            $clientId = "382378400042-5kcacdm2tmk1f1bh752u55q1m152jfbg.apps.googleusercontent.com";
            $clientSecret = "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";
            $redirectUri = $this->getGoogleRedirectUri();

            // Trao đổi Authorization Code lấy Access Token
            $tokenUrl = "https://oauth2.googleapis.com/token";
            $postData = [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $tokenUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bỏ qua xác thực SSL cục bộ trên Laragon
            $response = curl_exec($ch);
            curl_close($ch);

            $tokenData = json_decode($response, true);
            if (isset($tokenData['access_token'])) {
                $accessToken = $tokenData['access_token'];

                // Gọi API lấy thông tin người dùng Google
                $userInfoUrl = "https://www.googleapis.com/oauth2/v3/userinfo";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer $accessToken"
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $userInfoResponse = curl_exec($ch);
                curl_close($ch);

                $userInfo = json_decode($userInfoResponse, true);
                if (isset($userInfo['email'])) {
                    $email = $userInfo['email'];
                    $name = $userInfo['name'] ?? $email;

                    // Đăng nhập hoặc tạo mới người dùng
                    $user = $this->userModel->findOrCreateGoogleUser($email, $name);
                    if ($user) {
                        SessionHelper::login($user);
                        header('Location: /TranCongTien_4894/index.php?url=Product/list');
                        exit();
                    }
                }
            }
        }

        // Chuyển hướng về login nếu có lỗi xảy ra
        header('Location: /TranCongTien_4894/index.php?url=Account/login');
        exit();
    }

    private function getGithubRedirectUri()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . $host . "/TranCongTien_4894/index.php?url=Account/githubCallback";
    }

    public function githubLogin()
    {
        $clientId = "Ov23ling1RywvkEvKXAP";
        $redirectUri = $this->getGithubRedirectUri();
        
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => 'user:email read:user',
            'state' => 'github_oauth_state'
        ];
        
        $authUrl = "https://github.com/login/oauth/authorize?" . http_build_query($params);
        header("Location: " . $authUrl);
        exit();
    }

    public function githubCallback()
    {
        if (isset($_GET['code'])) {
            $code = $_GET['code'];
            $clientId = "Ov23ling1RywvkEvKXAP";
            $clientSecret = "86199e4b620a7bc634122c1892556f466ee80fcb";
            $redirectUri = $this->getGithubRedirectUri();

            // Trao đổi Authorization Code lấy Access Token
            $tokenUrl = "https://github.com/login/oauth/access_token";
            $postData = [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $tokenUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);

            $tokenData = json_decode($response, true);
            if (isset($tokenData['access_token'])) {
                $accessToken = $tokenData['access_token'];

                // Gọi API lấy thông tin người dùng từ GitHub
                $userInfoUrl = "https://api.github.com/user";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer $accessToken",
                    "User-Agent: PHP-OAuth-Client" // GitHub bắt buộc phải có User-Agent header
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $userInfoResponse = curl_exec($ch);
                curl_close($ch);

                $userInfo = json_decode($userInfoResponse, true);
                if (isset($userInfo['login'])) {
                    $githubUsername = $userInfo['login'];

                    // Đăng nhập hoặc tạo mới tài khoản GitHub
                    $user = $this->userModel->findOrCreateGithubUser($githubUsername);
                    if ($user) {
                        SessionHelper::login($user);
                        header('Location: /TranCongTien_4894/index.php?url=Product/list');
                        exit();
                    }
                }
            }
        }

        // Chuyển hướng về trang đăng nhập nếu có lỗi
        header('Location: /TranCongTien_4894/index.php?url=Account/login');
        exit();
    }
}
?>
