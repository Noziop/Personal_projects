<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use App\Services\AuthService;

class AuthMiddleware
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(Request $request, RequestHandler $handler)
    {
        $userId = $_SESSION['user_id'] ?? null;

        if ($userId) {
            $user = $this->authService->getUserById($userId);
            if ($user) {
                $request = $request->withAttribute('user', $user);
                return $handler->handle($request);
            }
        }

        // Créer une nouvelle réponse
        $response = new Response();
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}