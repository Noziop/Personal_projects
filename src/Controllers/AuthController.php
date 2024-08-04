<?php
/**
 * Authentication Controller
 *
 * This controller handles user authentication, including login and logout functionality.
 */

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Models\User;
use Psr\Log\LoggerInterface;
use Slim\Routing\RouteContext;

class AuthController
{
    private $view;
    private $user;
    private $logger;

    /**
     * AuthController constructor.
     *
     * @param Twig $view The Twig template engine
     * @param User $user The User model
     * @param LoggerInterface $logger The logger interface
     */
    public function __construct(Twig $view, User $user, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->user = $user;
        $this->logger = $logger;
    }

    /**
     * Display the login page
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function loginPage(Request $request, Response $response): Response
    {
        // If user is already logged in, redirect to dashboard
        if (isset($_SESSION['user'])) {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }
        return $this->view->render($response, 'auth/login.twig');
    }

    /**
     * Handle the login process
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $this->logger->info('Login attempt', ['username' => $username]);

        $user = $this->user->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            $this->logger->info('Login successful', ['username' => $username]);
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $this->logger->warning('Login failed', ['username' => $username]);
        return $this->view->render($response->withStatus(401), 'auth/login.twig', [
            'error' => 'Invalid username or password'
        ]);
    }

    /**
     * Handle the logout process
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function logout(Request $request, Response $response): Response
    {
        unset($_SESSION['user']);
        $this->logger->info('User logged out');
        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    /**
     * Display the dashboard
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function dashboard(Request $request, Response $response): Response
    {
        // Ensure user is logged in
        if (!isset($_SESSION['user'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }
        return $this->view->render($response, 'dashboard.twig', [
            'user' => $_SESSION['user']
        ]);
    }
}
