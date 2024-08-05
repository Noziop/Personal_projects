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
        error_log("Entering DashboardController::index");
        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            error_log("User not found in session");
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        $this->logger->info('User accessed dashboard', ['username' => $user['username']]);

        try {
            error_log("Fetching dashboard data");
            $dashboardData = $this->dashboardService->getDashboardData($user['id']);
            error_log("Dashboard data fetched successfully");
            error_log("Rendering dashboard template");
            return $this->view->render($response, 'dashboard.twig', $dashboardData);
        } catch (\Exception $e) {
            error_log("Error in DashboardController: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->logger->error('Error fetching dashboard data', ['error' => $e->getMessage()]);
            return $this->view->render($response->withStatus(500), 'error/500.twig', [
                'error' => 'An error occurred while loading the dashboard.'
            ]);
        }
    }
}
