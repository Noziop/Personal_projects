<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\AuthService;
use Slim\Views\Twig;

class AuthController
{
    private $authService;
    private $view;

    public function __construct(AuthService $authService, Twig $view)
    {
        $this->authService = $authService;
        $this->view = $view;
    }

	public function loginForm(Request $request, Response $response)
	{
		return $this->view->render($response, 'auth/login.twig', [
			'user' => $request->getAttribute('user')
		]);
	}
	public function login(Request $request, Response $response)
	{
		$data = $request->getParsedBody();
		$username = $data['username'] ?? '';
		$password = $data['password'] ?? '';
	
		$user = $this->authService->authenticate($username, $password);
	
		if ($user) {
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