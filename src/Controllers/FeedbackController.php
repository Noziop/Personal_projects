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
	
		$feedbacks = $this->feedbackService->getAllFeedbacks($eventType, $date, $studentId);
	
		return $this->view->render($response, 'feedback/manage.twig', [
			'feedbacks' => $feedbacks,
			'eventTypes' => ['SOD', 'Stand-up'],
			'students' => $this->feedbackService->getAllStudents(),
		]);
	}

	public function view(Request $request, Response $response, array $args): Response
	{
		$id = $args['id'];
		$type = $args['type'] ?? null; // Utilisez l'opérateur de fusion null
	
		if (!$type) {
			$this->logger->error('Feedback type not specified');
			return $response->withStatus(400)->withHeader('Location', '/feedback/manage');
		}
	
		$feedback = $this->feedbackService->getFeedbackById($id, $type);
	
		if (!$feedback) {
			$this->logger->warning('Feedback not found', ['id' => $id, 'type' => $type]);
			return $response->withStatus(404)->withHeader('Location', '/feedback/manage');
		}
	
		$template = $type === 'SOD' ? 'feedback/view_sod.twig' : 'feedback/view_standup.twig';
	
		return $this->view->render($response, $template, [
			'feedback' => $feedback
		]);
	}

}