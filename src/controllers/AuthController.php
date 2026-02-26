<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login(string $email, string $password): bool {
        $user = $this->userModel->findByEmail(trim($email));
        if (!$user) return false;
        if (!password_verify($password, $user['password_hash'])) return false;

        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        return true;
    }

    public function register(string $name, string $email, string $password): array {
        $name  = trim($name);
        $email = strtolower(trim($email));

        if (strlen($name) < 2)         return ['ok' => false, 'msg' => 'Name must be at least 2 characters.'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['ok' => false, 'msg' => 'Invalid email address.'];
        if (strlen($password) < 6)     return ['ok' => false, 'msg' => 'Password must be at least 6 characters.'];
        if ($this->userModel->findByEmail($email)) return ['ok' => false, 'msg' => 'Email is already registered.'];

        $userId = $this->userModel->create($name, $email, $password);
        session_regenerate_id(true);
        $_SESSION['user_id']   = $userId;
        $_SESSION['user_name'] = $name;
        return ['ok' => true];
    }

    public function logout(): void {
        $_SESSION = [];
        session_destroy();
    }

    public static function requireAuth(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: /cashtimeline/public/index');
            exit;
        }
    }

    public static function userId(): int {
        return (int)($_SESSION['user_id'] ?? 0);
    }
}
