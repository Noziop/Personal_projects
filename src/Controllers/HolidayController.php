<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Services\HolidayService;
use Psr\Log\LoggerInterface;

class HolidayController
{
    private $view;
    private $holidayService;
    private $logger;

    public function __construct(Twig $view, HolidayService $holidayService, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->holidayService = $holidayService;
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response): Response
    {
        $holidays = $this->holidayService->getAllHolidays();
        return $this->view->render($response, 'holidays/index.twig', ['holidays' => $holidays]);
    }

    public function sync(Request $request, Response $response): Response
    {
        $result = $this->holidayService->syncHolidays();
        if ($result) {
            $this->logger->info('Holidays synchronized successfully');
            // Add a flash message for success
            // $this->flash->addMessage('success', 'Holidays synchronized successfully');
        } else {
            $this->logger->error('Failed to synchronize holidays');
            // Add a flash message for error
            // $this->flash->addMessage('error', 'Failed to synchronize holidays');
        }
        return $response->withHeader('Location', '/holidays')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $deleted = $this->holidayService->deleteHoliday($args['id']);
        if ($deleted) {
            $this->logger->info('Holiday deleted', ['holiday_id' => $args['id']]);
            // Add a flash message for success
            // $this->flash->addMessage('success', 'Holiday deleted successfully');
        } else {
            $this->logger->error('Failed to delete holiday', ['holiday_id' => $args['id']]);
            // Add a flash message for error
            // $this->flash->addMessage('error', 'Failed to delete holiday');
        }
        return $response->withHeader('Location', '/holidays')->withStatus(302);
    }
}
