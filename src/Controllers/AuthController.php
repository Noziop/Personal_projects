<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\UserService;
use Slim\Views\Twig;

class AuthController
{
    private $userService;
    private $view;

    public function __construct(UserService $userService, Twig $view)
    {
        $this->userService = $userService;
        $this->view = $view;
    }

    public function loginForm(Request $request, Response $response)
    {
        return $this->view->render($response, 'auth/login.twig');
    }

    public function login(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $user = $this->userService->getUserByUsername($username);

        if ($user && password_verify($password, $user->getPassword())) {
            $_SESSION['user_id'] = $user->getId();
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        return $this->view->render($response, 'auth/login.twig', [
            'error' => 'Nom d\'utilisateur ou mot de passe incorrect'
        ]);
    }

    public function logout(Request $request, Response $response)
    {
        unset($_SESSION['user_id']);
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
