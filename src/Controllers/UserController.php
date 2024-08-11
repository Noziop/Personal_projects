<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Services\UserService;

class UserController
{
    private $view;
    private $logger;
    private $userService;

    public function __construct(Twig $view, LoggerInterface $logger, UserService $userService)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->userService = $userService;
    }

    public function loginForm(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'auth/login.twig');
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $user = $this->userService->authenticateUser($username, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $this->logger->info("User logged in", ['username' => $username]);
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $this->logger->warning("Failed login attempt", ['username' => $username]);
        return $this->view->render($response, 'auth/login.twig', ['error' => 'Invalid credentials']);
    }

    public function logout(Request $request, Response $response): Response
    {
        session_destroy();
        $this->logger->info("User logged out");
        return $response->withHeader('Location', '/')->withStatus(302);
    }

    public function listUsers(Request $request, Response $response): Response
    {
        $users = $this->userService->getAllUsers();
        return $this->view->render($response, 'admin/user-list.twig', ['users' => $users]);
    }

    public function createUserForm(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'admin/user-create.twig');
    }

    public function createUser(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $result = $this->userService->createUser($data);

        if ($result) {
            $this->logger->info("New user created", ['username' => $data['username']]);
            return $response->withHeader('Location', '/admin/users')->withStatus(302);
        }

        $this->logger->error("Failed to create user", ['username' => $data['username']]);
        return $this->view->render($response, 'admin/user-create.twig', ['error' => 'Failed to create user', 'data' => $data]);
    }

    public function editUserForm(Request $request, Response $response, array $args): Response
    {
        $userId = $args['id'];
        $user = $this->userService->getUserById($userId);

        if (!$user) {
            return $response->withStatus(404);
        }

        return $this->view->render($response, 'admin/user-edit.twig', ['user' => $user]);
    }

    public function editUser(Request $request, Response $response, array $args): Response
    {
        $userId = $args['id'];
        $data = $request->getParsedBody();
        $result = $this->userService->updateUser($userId, $data);

        if ($result) {
            $this->logger->info("User updated", ['user_id' => $userId]);
            return $response->withHeader('Location', '/admin/users')->withStatus(302);
        }

        $this->logger->error("Failed to update user", ['user_id' => $userId]);
        return $this->view->render($response, 'admin/user-edit.twig', ['error' => 'Failed to update user', 'user' => $data]);
    }

    public function deleteUser(Request $request, Response $response, array $args): Response
    {
        $userId = $args['id'];
        $result = $this->userService->deleteUser($userId);

        if ($result) {
            $this->logger->info("User deleted", ['user_id' => $userId]);
            return $response->withHeader('Location', '/admin/users')->withStatus(302);
        }

        $this->logger->error("Failed to delete user", ['user_id' => $userId]);
        return $response->withStatus(500)->withJson(['error' => 'Failed to delete user']);
    }

    public function changePasswordForm(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'user/change-password.twig');
    }

    public function changePassword(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withStatus(401);
        }

        $data = $request->getParsedBody();
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';

        $user = $this->userService->getUserById($userId);
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return $this->view->render($response, 'user/change-password.twig', ['error' => 'Current password is incorrect']);
        }

        $result = $this->userService->updatePassword($userId, $newPassword);
        if ($result) {
            $this->logger->info("Password changed", ['user_id' => $userId]);
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $this->logger->error("Failed to change password", ['user_id' => $userId]);
        return $this->view->render($response, 'user/change-password.twig', ['error' => 'Failed to change password']);
    }
}