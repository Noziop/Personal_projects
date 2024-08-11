<?php

namespace App\Models;

use PDO;

class User
{
    private $id;
    private $username;
    private $firstName;
    private $lastName;
    private $email;
    private $password;
    private $role;
    private $createdAt;
    private $updatedAt;
    private $lastLogin;
    private $isActive;
    private $slackId;

    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll()
    {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY username");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByRole($role)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE role = :role ORDER BY username");
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO users (username, first_name, last_name, email, password, role, slack_id) VALUES (:username, :first_name, :last_name, :email, :password, :role, :slack_id)");
        return $stmt->execute([
            'username' => $data['username'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'slack_id' => $data['slack_id'] ?? null
        ]);
    }

	public function update($id, $data)
	{
		$sql = "UPDATE users SET username = :username, first_name = :first_name, last_name = :last_name, email = :email, role = :role, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
		$stmt = $this->db->prepare($sql);
		return $stmt->execute([
			'id' => $id,
			'username' => $data['username'],
			'first_name' => $data['first_name'],
			'last_name' => $data['last_name'],
			'email' => $data['email'],
			'role' => $data['role']
		]);
	}

    public function updatePassword($id, $password)
    {
        $stmt = $this->db->prepare("UPDATE users SET password = :password, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'password' => $password
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function updateLastLogin($id)
    {
        $stmt = $this->db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function setActive($id, $isActive)
    {
        $stmt = $this->db->prepare("UPDATE users SET is_active = :is_active, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'is_active' => $isActive ? 1 : 0
        ]);
    }

    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'role' => $this->role,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'last_login' => $this->lastLogin,
            'is_active' => $this->isActive,
            'slack_id' => $this->slackId
        ];
    }

    private function hydrate($data)
    {
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->firstName = $data['first_name'];
        $this->lastName = $data['last_name'];
        $this->email = $data['email'];
        $this->password = $data['password'];
        $this->role = $data['role'];
        $this->createdAt = $data['created_at'];
        $this->updatedAt = $data['updated_at'];
        $this->lastLogin = $data['last_login'];
        $this->isActive = $data['is_active'];
        $this->slackId = $data['slack_id'];

        return $this;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getFirstName() { return $this->firstName; }
    public function getLastName() { return $this->lastName; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getRole() { return $this->role; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }
    public function getLastLogin() { return $this->lastLogin; }
    public function isActive() { return $this->isActive; }
    public function getSlackId() { return $this->slackId; }
}