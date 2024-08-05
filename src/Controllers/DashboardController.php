<?php
/**
 * Dashboard Controller
 *
 * This controller handles the dashboard functionality, including displaying
 * relevant information for different user roles.
 */

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

    /**
     * DashboardController constructor.
     *
     * @param Twig $view The Twig template engine
     * @param LoggerInterface $logger The logger interface
     * @param DashboardService $dashboardService The dashboard service
     */
    public function __construct(Twig $view, LoggerInterface $logger, DashboardService $dashboardService)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->dashboardService = $dashboardService;
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
            $this->logger->warning('Unauthorized access attempt to dashboard');
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        $this->logger->info('User accessed dashboard', ['username' => $user['username']]);

        try {
            $dashboardData = $this->dashboardService->getDashboardData($user['id']);
            return $this->view->render($response, 'dashboard.twig', $dashboardData);
        } catch (\Exception $e) {
            $this->logger->error('Error fetching dashboard data', ['error' => $e->getMessage()]);
            return $this->view->render($response->withStatus(500), 'error/500.twig', [
                'error' => 'An error occurred while loading the dashboard.'
            ]);
        }
    }
}
