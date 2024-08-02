<?php

namespace App\Services;

use App\Models\User;
use PDO;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\DuplicateUserException;

class UserService
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createUser(User $user)
    {
        try {
            $sql = "INSERT INTO users (username, first_name, last_name, password, role, email, is_active) 
                    VALUES (:username, :firstName, :lastName, :password, :role, :email, :isActive)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'username' => $user->getUsername(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'password' => $user->getPassword(),
                'role' => $user->getRole(),
                'email' => $user->getEmail(),
                'isActive' => $user->isActive()
            ]);

            $userId = $this->db->lastInsertId();
            $user->setId($userId);

            $this->saveUserPermissions($user);

            return $userId;
        } catch (\PDOException $e) {
            if ($e->getCode() == '23000') {
                throw new DuplicateUserException("Un utilisateur avec ce nom d'utilisateur ou cet email existe déjà.");
            }
            throw $e;
        }
    }

    public function getUserById($id)
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        if ($userData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = new User($userData);
            $this->loadUserPermissions($user);
            return $user;
        }
        
        throw new UserNotFoundException("Utilisateur avec l'ID $id non trouvé.");
    }

    public function getUserByUsername($username)
    {
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        
        if ($userData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = new User($userData);
            $this->loadUserPermissions($user);
            return $user;
        }
        
        throw new UserNotFoundException("Utilisateur avec le nom d'utilisateur $username non trouvé.");
    }

    public function updateUser(User $user)
    {
        $sql = "UPDATE users SET username = :username, first_name = :firstName, last_name = :lastName, 
                role = :role, email = :email, is_active = :isActive, updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'role' => $user->getRole(),
            'email' => $user->getEmail(),
            'isActive' => $user->isActive()
        ]);

        if ($result) {
            $this->saveUserPermissions($user);
        }

        return $result;
    }

    public function deleteUser($id)
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function getAllUsers()
    {
        $sql = "SELECT * FROM users";
        $stmt = $this->db->query($sql);
        $usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($usersData as $userData) {
            $user = new User($userData);
            $this->loadUserPermissions($user);
            $users[] = $user;
        }

        return $users;
    }

    public function updatePassword(User $user, $newPassword)
    {
        $sql = "UPDATE users SET password = :password, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $user->getId(),
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }

    public function updateLastLogin(User $user)
    {
        $sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $user->getId()]);
    }

    private function saveUserPermissions(User $user)
    {
        $this->db->beginTransaction();

        try {
            $sql = "DELETE FROM user_permissions WHERE user_id = :userId";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['userId' => $user->getId()]);

            $sql = "INSERT INTO user_permissions (user_id, permission_id) VALUES (:userId, :permissionId)";
            $stmt = $this->db->prepare($sql);

            foreach ($user->getPermissions() as $permission) {
                $stmt->execute([
                    'userId' => $user->getId(),
                    'permissionId' => $this->getPermissionId($permission)
                ]);
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function loadUserPermissions(User $user)
    {
        $sql = "SELECT p.name FROM permissions p 
                JOIN user_permissions up ON p.id = up.permission_id 
                WHERE up.user_id = :userId";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['userId' => $user->getId()]);
        
        $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $user->setPermissions($permissions);
    }

    private function getPermissionId($permissionName)
    {
        $sql = "SELECT id FROM permissions WHERE name = :name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['name' => $permissionName]);
        return $stmt->fetchColumn();
    }
}
