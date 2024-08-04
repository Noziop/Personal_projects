<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;

/**
 * HomeController
 * 
 * This controller handles the home page and dashboard of the application.
 */
class HomeController
{
    private $view;
    private $logger;

    /**
     * Constructor
     * 
     * @param Twig $view The Twig template engine instance
     * @param LoggerInterface $logger The logger instance
     */
    public function __construct(Twig $view, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->logger = $logger;
    }

    /**
     * Display the home page
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response The rendered home page
     */
    public function index(Request $request, Response $response): Response
    {
        $this->logger->info('Home page accessed');
        return $this->view->render($response, 'home.twig');
    }

    /**
     * Display the dashboard
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response The rendered dashboard page
     */
    public function dashboard(Request $request, Response $response): Response
    {
        $this->logger->info('Dashboard accessed');
        // TODO: Add logic to fetch necessary data for the dashboard
        return $this->view->render($response, 'dashboard.twig');
    }
}