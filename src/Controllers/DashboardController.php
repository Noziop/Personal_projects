<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Services\DashboardService;

class DashboardController
{
    private $view;
    private $logger;
    private $dashboardService;

    public function __construct(Twig $view, LoggerInterface $logger, DashboardService $dashboardService)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->dashboardService = $dashboardService;
    }

	public function index(Request $request, Response $response): Response
	{
		$this->logger->info('Entering DashboardController::index');
		
		$user = $_SESSION['user'] ?? null;
	
		if (!$user) {
			$this->logger->warning('Unauthorized access attempt to dashboard');
			return $response->withHeader('Location', '/')->withStatus(302);
		}
	
		$this->logger->info('User accessed dashboard', ['username' => $user['username']]);
	
		try {
			$this->logger->info('Fetching dashboard data');
			$dashboardData = $this->dashboardService->getDashboardData($user['id']);
			$this->logger->info('Dashboard data fetched successfully', ['data' => json_encode($dashboardData)]);
			
			$this->logger->info('Rendering dashboard template');
			$renderedResponse = $this->view->render($response, 'dashboard.twig', $dashboardData);
			$this->logger->info('Dashboard template rendered successfully');
			
			return $renderedResponse;
		} catch (\Exception $e) {
			$this->logger->error('Error in DashboardController', [
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
			return $this->view->render($response->withStatus(500), 'error/500.twig', [
				'error' => 'An error occurred while loading the dashboard.'
			]);
		}
	}	
}
