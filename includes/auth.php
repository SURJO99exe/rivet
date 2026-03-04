<?php
require_once __DIR__ . '/../config/config.php';

class Auth {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register($username, $email, $password, $country = 'Global', $referral_by = null) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $referral_code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

        try {
            // Check if email or username already exists
            $check = $this->pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $check->execute([$username, $email]);
            if ($check->fetch()) {
                return "Username or email already exists.";
            }

            $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, country, referral_code, referred_by) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password, $country, $referral_code, $referral_by])) {
                return true;
            }
            return "Registration failed. Please try again.";
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function login($username_or_email, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'");
        $stmt->execute([$username_or_email, $username_or_email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            return true;
        }
        return false;
    }

    public function logout() {
        session_destroy();
        return true;
    }
}

$auth = new Auth($pdo);
?>
