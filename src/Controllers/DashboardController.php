<?php
/**
 * File: src/Controllers/DashboardController.php
 * 
 * This file contains the DashboardController class which handles dashboard-related actions.
 */

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;

class DashboardController
{
    private $view;
    private $logger;

    /**
     * DashboardController constructor.
     *
     * @param Twig $view The Twig template engine
     * @param LoggerInterface $logger The logger interface
     */
    public function __construct(Twig $view, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->logger = $logger;
    }

    /**
     * Display the dashboard
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
	public function index(Request $request, Response $response): Response
	{
		$user = $_SESSION['user'] ?? null;
	
		if (!$user) {
			return $response->withHeader('Location', '/')->withStatus(302);
		}
	
		$this->logger->info('User accessed dashboard', ['username' => $user['username']]);
	
		return $this->view->render($response, 'dashboard.twig', [
			'user' => $user
		]);
	}
}
