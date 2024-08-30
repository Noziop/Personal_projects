<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Services\FeedbackService;
use App\Services\StandupFeedbackService;
use Psr\Log\LoggerInterface;

class FeedbackController
{
    private $view;
    private $feedbackService;
    private $standupFeedbackService;
    private $logger;

    public function __construct(
        Twig $view,
        FeedbackService $feedbackService,
        StandupFeedbackService $standupFeedbackService,
        LoggerInterface $logger
    ) {
        $this->view = $view;
        $this->feedbackService = $feedbackService;
        $this->standupFeedbackService = $standupFeedbackService;
        $this->logger = $logger;
    }

    public function manage(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $eventType = $queryParams['event_type'] ?? null;
        $date = $queryParams['date'] ?? null;
        $studentId = $queryParams['student_id'] ?? null;

        $feedbacks = $this->feedbackService->getFeedback($eventType, $date, $studentId);
        
        // Si le type d'événement est 'standup' ou non spécifié, récupérer également les stand-ups
        if ($eventType === 'standup' || $eventType === null) {
            $standupFeedbacks = $this->standupFeedbackService->getFeedbackByDateRange($date, $date);
            $feedbacks = array_merge($feedbacks, $standupFeedbacks);
        }

        return $this->view->render($response, 'feedback/manage.twig', [
            'feedbacks' => $feedbacks,
            'eventTypes' => ['SOD', 'Stand up', 'PLD'],
            'students' => $this->feedbackService->getAllStudents(),
        ]);
    }


	public function view(Request $request, Response $response, array $args): Response
	{
		$id = $args['id'];
		$type = $args['type'];
	
		$feedback = $this->feedbackService->getFeedbackById($id, $type);
	
		if (!$feedback) {
			$this->logger->warning('Feedback not found', ['id' => $id, 'type' => $type]);
			return $response->withHeader('Location', '/feedback/manage')->withStatus(302);
		}
	
		return $this->view->render($response, 'feedback/view.twig', [
			'feedback' => $feedback
		]);
	}

    public function create(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $eventType = $data['event_type'] ?? null;

            if (!$eventType) {
                $this->logger->error('Event type not specified for feedback creation');
                // Gérer l'erreur (par exemple, ajouter un message flash et rediriger)
                return $response->withHeader('Location', '/feedback/create')->withStatus(302);
            }

            $result = $this->feedbackService->createFeedback($data, $eventType);

            if ($result) {
                $this->logger->info('Feedback created', ['event_type' => $eventType]);
                // Rediriger vers la page de gestion des feedbacks avec un message de succès
                return $response->withHeader('Location', '/feedback/manage')->withStatus(302);
            } else {
                $this->logger->error('Failed to create feedback', ['event_type' => $eventType]);
                // Gérer l'échec (par exemple, ajouter un message flash et rediriger)
                return $response->withHeader('Location', '/feedback/create')->withStatus(302);
            }
        }

        return $this->view->render($response, 'feedback/create.twig', [
            'eventTypes' => $this->feedbackService->getEventTypes(),
            'students' => $this->feedbackService->getAllStudents(),
        ]);
    }

    // Vous pouvez ajouter d'autres méthodes ici pour la mise à jour et la suppression des feedbacks
}