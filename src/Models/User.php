<?php

namespace App\Models;

class User
{
    private $id;
    private $username;
    private $firstName;
    private $lastName;
    private $password;
    private $role;
    private $email;
    private $createdAt;
    private $updatedAt;
    private $lastLogin;
    private $isActive;
    private $permissions = [];

    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    public function hydrate(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getFirstName() { return $this->firstName; }
    public function getLastName() { return $this->lastName; }
    public function getPassword() { return $this->password; }
    public function getRole() { return $this->role; }
    public function getEmail() { return $this->email; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }
    public function getLastLogin() { return $this->lastLogin; }
    public function isActive() { return $this->isActive; }
    public function getPermissions() { return $this->permissions; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setUsername($username) { $this->username = $username; }
    public function setFirstName($firstName) { $this->firstName = $firstName; }
    public function setLastName($lastName) { $this->lastName = $lastName; }
    public function setPassword($password) { $this->password = password_hash($password, PASSWORD_DEFAULT); }
    public function setRole($role) { $this->role = $role; }
    public function setEmail($email) { $this->email = $email; }
    public function setCreatedAt($createdAt) { $this->createdAt = $createdAt; }
    public function setUpdatedAt($updatedAt) { $this->updatedAt = $updatedAt; }
    public function setLastLogin($lastLogin) { $this->lastLogin = $lastLogin; }
    public function setIsActive($isActive) { $this->isActive = $isActive; }
    public function setPermissions(array $permissions) { $this->permissions = $permissions; }

    public function addPermission($permission)
    {
        if (!in_array($permission, $this->permissions)) {
            $this->permissions[] = $permission;
        }
    }

    public function removePermission($permission)
    {
        $key = array_search($permission, $this->permissions);
        if ($key !== false) {
            unset($this->permissions[$key]);
        }
    }

    public function hasPermission($permission)
    {
        return in_array($permission, $this->permissions);
    }

    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
