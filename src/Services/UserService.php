<?php

namespace App\Services;

use App\Models\User;
use App\Models\Student;
use Psr\Log\LoggerInterface;

class UserService
{
    private $userModel;
	private $studentModel;
    private $logger;

    public function __construct(User $userModel, Student $studentModel, LoggerInterface $logger)
    {
        $this->userModel = $userModel;
		$this->studentModel = $studentModel;
        $this->logger = $logger;
    }

    public function createUser($data)
    {
        $this->logger->info('Creating new user', [
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role']
        ]);

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        return $this->userModel->create($data);
    }

	public function getUserById($id)
	{
		$this->logger->info('Fetching user by ID', ['id' => $id]);
		return $this->userModel->findById($id);
	}

    public function getUserByUsername($username)
    {
        $this->logger->info('Fetching user by username', ['username' => $username]);
        return $this->userModel->findByUsername($username);
    }

    public function getUserByEmail($email)
    {
        $this->logger->info('Fetching user by email', ['email' => $email]);
        return $this->userModel->findByEmail($email);
    }


    public function updateUser($id, $data)
    {
        $this->logger->info('Updating user', [
            'id' => $id,
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role']
        ]);

        return $this->userModel->update($id, $data);
    }

    public function updatePassword($id, $newPassword)
    {
        $this->logger->info('Updating user password', ['id' => $id]);

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->userModel->updatePassword($id, $hashedPassword);
    }

    public function deleteUser($id)
    {
        $this->logger->info('Deleting user', ['id' => $id]);
        return $this->userModel->delete($id);
    }

    public function getAllUsers()
    {
        $this->logger->info('Fetching all users');
        return $this->userModel->findAll();
    }

    public function getUsersByRole($role)
    {
        $this->logger->info('Fetching users by role', ['role' => $role]);
        return $this->userModel->findByRole($role);
    }

    public function authenticateUser($username, $password)
    {
        $this->logger->info('Authenticating user', ['username' => $username]);

        $user = $this->userModel->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            $this->logger->info('User authenticated successfully', ['username' => $username]);
            $this->userModel->updateLastLogin($user['id']);
            return $user;
        }

        $this->logger->warning('Authentication failed', ['username' => $username]);
        return null;
    }

    public function isUsernameAvailable($username)
    {
        return $this->userModel->findByUsername($username) === null;
    }

    public function isEmailAvailable($email)
    {
        return $this->userModel->findByEmail($email) === null;
    }

    public function setUserActive($id, $isActive)
    {
        $this->logger->info('Setting user active status', ['id' => $id, 'is_active' => $isActive]);
        return $this->userModel->setActive($id, $isActive);
    }

    public function getUsersCount()
    {
        return $this->userModel->getTotalCount();
    }

	public function getStudentByUserId($userId)
	{
		$this->logger->info("Getting student by user ID", ['user_id' => $userId]);
		return $this->studentModel->findByUserId($userId);
	}
	public function getStudentIdByUserId($userId)
    {
        $student = $this->getStudentByUserId($userId);
        return $student ? $student['id'] : null;
    }
}