<?php
require_once __DIR__ . '/../config/config.php';

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getUserDetails($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }

    public function getBalance($user_id) {
        $stmt = $this->pdo->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    public function updateBalance($user_id, $amount) {
        $stmt = $this->pdo->prepare("UPDATE users SET balance = balance + ?, total_earned = total_earned + ? WHERE id = ?");
        return $stmt->execute([$amount, $amount, $user_id]);
    }
}

$userClass = new User($pdo);
?>
