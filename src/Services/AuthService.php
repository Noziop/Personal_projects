<?php

namespace App\Services;

use App\Models\User;
use PDO;

class AuthService
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function authenticate($username, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return new User($user);
        }

        return null;
    }

    public function getUserById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ? new User($user) : null;
    }

    public function getUserPermissions($userId)
    {
        $stmt = $this->db->prepare("
            SELECT p.name
            FROM permissions p
            JOIN user_permissions up ON p.id = up.permission_id
            WHERE up.user_id = :userId
        ");
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}