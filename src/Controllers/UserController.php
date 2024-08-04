<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Services\UserService;

/**
 * UserController
 * 
 * This controller handles user-related operations such as registration, login, and user management.
 */
class UserController
{
    private $view;
    private $logger;
    private $userService;

    /**
     * Constructor
     * 
     * @param Twig $view The Twig template engine instance
     * @param LoggerInterface $logger The logger instance
     * @param UserService $userService The user service instance
     */
    public function __construct(Twig $view, LoggerInterface $logger, UserService $userService)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->userService = $userService;
    }

    /**
     * Display the login form
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response The rendered login form
     */
    public function loginForm(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'auth/login.twig');
    }

    /**
     * Handle the login process
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response A redirect response
     */
    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if ($this->userService->authenticate($username, $password)) {
            $this->logger->info("User logged in: $username");
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $this->logger->warning("Failed login attempt for username: $username");
        return $this->view->render($response, 'auth/login.twig', ['error' => 'Invalid credentials']);
    }

    /**
     * Handle the logout process
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response A redirect response to the home page
     */
    public function logout(Request $request, Response $response): Response
    {
        // TODO: Implement logout logic (e.g., destroying session)
        $this->logger->info("User logged out");
        return $response->withHeader('Location', '/')->withStatus(302);
    }

    /**
     * Display the user list (admin only)
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response The rendered user list page
     */
    public function listUsers(Request $request, Response $response): Response
    {
        $users = $this->userService->getAllUsers();
        return $this->view->render($response, 'admin/user-list.twig', ['users' => $users]);
    }

    // TODO: Add methods for user registration, profile editing, etc.
}