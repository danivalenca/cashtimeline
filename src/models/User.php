<?php

require_once __DIR__ . '/../../config/database.php';

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): array|false {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function find(int $id): array|false {
        return $this->findById($id);
    }

    public function create(string $name, string $email, string $password): int {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)'
        );
        $stmt->execute([$name, $email, $hash]);
        return (int) $this->db->lastInsertId();
    }
    
    public function updateContact(int $id, string $email, string $phone): bool {
        $stmt = $this->db->prepare(
            'UPDATE users SET email = ?, phone = ? WHERE id = ?'
        );
        return $stmt->execute([$email, $phone, $id]);
    }
}
