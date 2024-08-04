<?php

namespace App\Models;

use PDO;

class User
{
    private $id;
    private $username;
    private $email;
    private $password;
    private $role;
    private $createdAt;

    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            return $this->hydrate($userData);
        }

        return null;
    }

    public function findByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function authenticate($username, $password)
    {
        $userData = $this->findByUsername($username);
        if ($userData && password_verify($password, $userData['password'])) {
            unset($userData['password']); // Remove password from the returned data
            return $userData;
        }
        return false;
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
        return $stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'student'
        ]);
    }

    public function update($data)
    {
        $stmt = $this->db->prepare("UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id");
        return $stmt->execute([
            'id' => $this->id,
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role']
        ]);
    }

    public function delete()
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }

    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    private function hydrate($data)
    {
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->email = $data['email'];
        $this->password = $data['password'];
        $this->role = $data['role'];
        $this->createdAt = $data['created_at'];

        return $this;
    }

    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
    public function getCreatedAt() { return $this->createdAt; }
}
