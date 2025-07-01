<?php
require_once __DIR__ . '/../GoogleAuthenticator/PHPGangsta/GoogleAuthenticator.php';

class GoogleAuth {
    private $ga;
    private $conn;

    public function __construct($conn) {
        $this->ga = new PHPGangsta_GoogleAuthenticator();
        $this->conn = $conn;
    }

    public function createSecret() {
        return $this->ga->createSecret();
    }

    public function getQRCodeUrl($username, $secret) {
        return $this->ga->getQRCodeGoogleUrl($username, $secret);
    }

    public function verifyCode($secret, $code) {
        return $this->ga->verifyCode($secret, $code, 2); // 2 = 2*30sec clock tolerance
    }

    public function saveSecret($userId, $secret) {
        $stmt = $this->conn->prepare("UPDATE users SET google_auth_secret = ?, two_factor_enabled = 1 WHERE id = ?");
        $stmt->bind_param("si", $secret, $userId);
        return $stmt->execute();
    }

    public function getSecret($userId) {
        $stmt = $this->conn->prepare("SELECT google_auth_secret FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['google_auth_secret'] ?? null;
    }

    public function isTwoFactorEnabled($userId) {
        $stmt = $this->conn->prepare("SELECT two_factor_enabled FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['two_factor_enabled'] ?? 0;
    }

    public function disableTwoFactor($userId) {
        $stmt = $this->conn->prepare("UPDATE users SET google_auth_secret = NULL, two_factor_enabled = 0 WHERE id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
} 
