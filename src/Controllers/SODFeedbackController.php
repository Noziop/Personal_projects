<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Services\SODFeedbackService;
use Psr\Log\LoggerInterface;
use App\Services\UserService;

class SODFeedbackController
{
    private $view;
    private $sodFeedbackService;
    private $userService;
    private $logger;

    public function __construct(Twig $view, SODFeedbackService $sodFeedbackService, UserService $userService, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->sodFeedbackService = $sodFeedbackService;
        $this->userService = $userService;
        $this->logger = $logger;
    }


	public function showForm(Request $request, Response $response, array $args): Response
	{
		// Récupérer la liste des étudiants
		$students = $this->userService->getUsersByRole('student');
		
		// Récupérer l'ID de l'évaluateur (l'utilisateur connecté)
		$evaluatorId = $_SESSION['user']['id'] ?? null;
	
		return $this->view->render($response, 'sod_feedback/form.twig', [
			'students' => $students,
			'evaluatorId' => $evaluatorId
		]);
	}
	

    public function submitFeedback(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $evaluatorId = $_SESSION['user']['id'] ?? null;

        if (!$evaluatorId) {
            $this->logger->error('Unauthorized access to SOD feedback submission');
            return $response->withStatus(401);
        }

        $feedback = [
            'student_id' => $data['student_id'],
            'evaluator_id' => $evaluatorId,
            'sod_date' => $data['sod_date'],
            'content' => json_encode([
                'time' => $data['time'],
                'audience_engagement' => $data['audience_engagement'],
                'spatial_position' => $data['spatial_position'],
                'self_confidence' => $data['self_confidence'],
                'audibility' => $data['audibility'],
                'no_filler_words' => $data['no_filler_words'],
                'energy' => $data['energy'],
                'english' => $data['english'],
                'presentation' => $data['presentation'],
                'subject' => $data['subject'],
                'question_responses' => $data['question_responses'],
                'total' => array_sum($data) - $data['student_id'] - strtotime($data['sod_date'])
            ])
        ];

        $result = $this->sodFeedbackService->createFeedback($feedback);

        if ($result) {
            $this->logger->info('SOD feedback submitted successfully', ['student_id' => $data['student_id']]);
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        } else {
            $this->logger->error('Failed to submit SOD feedback', ['student_id' => $data['student_id']]);
            return $response->withStatus(500);
        }
    }
}
