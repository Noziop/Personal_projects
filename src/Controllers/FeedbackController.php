<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Services\FeedbackService;
use Psr\Log\LoggerInterface;

class FeedbackController
{
    private $view;
    private $feedbackService;
    private $logger;

    public function __construct(Twig $view, FeedbackService $feedbackService, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->feedbackService = $feedbackService;
        $this->logger = $logger;
    }

    public function manage(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $eventType = $queryParams['event_type'] ?? null;
        $date = $queryParams['date'] ?? null;
        $studentId = $queryParams['student_id'] ?? null;

        $feedback = $this->feedbackService->getFeedback($eventType, $date, $studentId);

        return $this->view->render($response, 'feedback/manage.twig', [
            'feedback' => $feedback,
            'eventTypes' => ['SOD', 'Stand up', 'PLD'],
            'students' => $this->feedbackService->getAllStudents(),
        ]);
    }
}