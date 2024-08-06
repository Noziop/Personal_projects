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
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            return $this->hydrate($userData);
        }

        return null;
    }

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            return $this->hydrate($userData);
        }

        return null;
    }

    public function findAll()
    {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY username");
        $usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($usersData as $userData) {
            $users[] = $this->hydrate($userData);
        }

        return $users;
    }

    public function findByRole($role)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE role = :role ORDER BY username");
        $stmt->execute(['role' => $role]);
        $usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($usersData as $userData) {
            $users[] = $this->hydrate($userData);
        }

        return $users;
    }

    public function create($username, $firstName, $lastName, $email, $password, $role)
    {
        $stmt = $this->db->prepare("INSERT INTO users (username, first_name, last_name, email, password, role) VALUES (:username, :first_name, :last_name, :email, :password, :role)");
        return $stmt->execute([
            'username' => $username,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ]);
    }

    public function update($id, $username, $firstName, $lastName, $email, $role)
    {
        $stmt = $this->db->prepare("UPDATE users SET username = :username, first_name = :first_name, last_name = :last_name, email = :email, role = :role WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'username' => $username,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'role' => $role
        ]);
    }

    public function updatePassword($id, $password)
    {
        $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
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

        return $this;
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
            'role' => $this->role,
            // Ajoutez ici tous les autres attributs nécessaires, sauf le mot de passe
        ];
    }

    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getFirstName() { return $this->firstName; }
    public function getLastName() { return $this->lastName; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getRole() { return $this->role; }
    public function getCreatedAt() { return $this->createdAt; }
}
