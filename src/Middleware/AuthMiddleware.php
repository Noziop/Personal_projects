<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use App\Services\UserService;

class AuthMiddleware
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $session = $_SESSION ?? [];

        if (!isset($session['user_id'])) {
            $response = new Response();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $user = $this->userService->getUserById($session['user_id']);

        if (!$user) {
            $response = new Response();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $request = $request->withAttribute('user', $user);

        return $handler->handle($request);
    }

    public static function requireRole($roles)
    {
        return function (Request $request, RequestHandler $handler) use ($roles) {
            $user = $request->getAttribute('user');

            if (!$user || !in_array($user->getRole(), (array)$roles)) {
                $response = new Response();
                return $response->withStatus(403)->withHeader('Content-Type', 'application/json')->getBody()->write(json_encode(['error' => 'Accès non autorisé']));
            }

            return $handler->handle($request);
        };
    }
}
