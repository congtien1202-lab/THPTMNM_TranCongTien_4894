<?php
class UserModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function userExists($username)
    {
        $query = "SELECT COUNT(*) FROM users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function register($username, $password, $role = 'user')
    {
        if ($this->userExists($username)) {
            return "Tên đăng nhập đã tồn tại.";
        }

        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $role);
            
            if ($stmt->execute()) {
                return true;
            }
            return "Đã xảy ra lỗi khi tạo tài khoản.";
        } catch (PDOException $e) {
            return "Lỗi CSDL: " . $e->getMessage();
        }
    }

    public function login($username, $password)
    {
        try {
            $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($user && password_verify($password, $user->password)) {
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function findOrCreateGoogleUser($email, $name)
    {
        try {
            // Sử dụng email Google làm tên đăng nhập chính thức
            $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($user) {
                return $user;
            }
            
            // Nếu người dùng chưa tồn tại, tạo tài khoản mới với mật khẩu ngẫu nhiên băm bảo mật
            $randomPassword = bin2hex(random_bytes(16));
            $hashedPassword = password_hash($randomPassword, PASSWORD_BCRYPT);
            
            $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, 'user')";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $email);
            $stmt->bindParam(':password', $hashedPassword);
            
            if ($stmt->execute()) {
                $newId = $this->db->lastInsertId();
                return (object)[
                    'id' => $newId,
                    'username' => $email,
                    'role' => 'user'
                ];
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function findOrCreateGithubUser($githubUsername)
    {
        $dbUsername = 'github_' . $githubUsername;
        try {
            // Kiểm tra xem tài khoản github này đã liên kết trước đây chưa
            $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $dbUsername);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($user) {
                return $user;
            }
            
            // Đăng ký mới tài khoản liên kết github với mật khẩu ngẫu nhiên băm bảo mật
            $randomPassword = bin2hex(random_bytes(16));
            $hashedPassword = password_hash($randomPassword, PASSWORD_BCRYPT);
            
            $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, 'user')";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $dbUsername);
            $stmt->bindParam(':password', $hashedPassword);
            
            if ($stmt->execute()) {
                $newId = $this->db->lastInsertId();
                return (object)[
                    'id' => $newId,
                    'username' => $dbUsername,
                    'role' => 'user'
                ];
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
