<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Services\StandupFeedbackService;
use App\Services\StudentService;
use App\Services\UserService;
use App\Services\CohortService;
use Psr\Log\LoggerInterface;

class StandupFeedbackController
{
    private $view;
    private $standupFeedbackService;
    private $studentService;
    private $userService;
    private $cohortService;
    private $logger;

    public function __construct(
        Twig $view,
        StandupFeedbackService $standupFeedbackService,
        StudentService $studentService,
        UserService $userService,
        CohortService $cohortService,
        LoggerInterface $logger
    ) {
        $this->view = $view;
        $this->standupFeedbackService = $standupFeedbackService;
        $this->studentService = $studentService;
        $this->userService = $userService;
        $this->cohortService = $cohortService;
        $this->logger = $logger;
    }

    public function showForm(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) {
            return $response->withStatus(401);
        }

        $student = $this->studentService->getStudentByUserId($userId);
        if (!$student) {
            return $response->withStatus(400);
        }

        $cohort = $this->cohortService->getCohortById($student['cohort_id']);
        $cohortStudents = $this->studentService->getStudentsByCohort($student['cohort_id']);

        return $this->view->render($response, 'standup_feedback/form.twig', [
            'currentDate' => date('Y-m-d'),
            'cohort' => $cohort,
            'cohortStudents' => $cohortStudents
        ]);
    }

    public function submitFeedback(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $userId = $_SESSION['user']['id'] ?? null;

        if (!$userId) {
            $this->logger->error('Unauthorized access to standup feedback submission');
            return $response->withStatus(401);
        }

        $student = $this->studentService->getStudentByUserId($userId);
        if (!$student) {
            $this->logger->error('User is not a student', ['user_id' => $userId]);
            return $response->withStatus(400);
        }

        $feedbackData = [
            'cohort_id' => $student['cohort_id'],
            'date' => $data['date'],
            'absent' => 0,
            'on_site' => 0,
            'achievements' => '',
            'today_goals' => '',
            'need_help' => 0,
            'problem_nature' => '',
            'other_remarks' => '',
            'content' => json_encode($data['students']),
            'scrum_master_names' => $data['summary']['scrum_master'],
            'bugs_report' => $data['summary']['bugs_report'],
            'cohort_difficulties' => $data['summary']['cohort_difficulties'],
            'shared_tips' => $data['summary']['shared_tips'],
            'conclusion' => $data['summary']['conclusion'],
            'other_reports' => $data['summary']['other_reports']
        ];

        $result = $this->standupFeedbackService->createFeedback($feedbackData);

        if ($result) {
            $this->logger->info('Standup feedback submitted successfully', ['cohort_id' => $student['cohort_id']]);
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        } else {
            $this->logger->error('Failed to submit standup feedback', ['cohort_id' => $student['cohort_id']]);
            return $response->withStatus(500);
        }
    }

    public function viewFeedback(Request $request, Response $response, array $args): Response
    {
        $feedbackId = $args['id'];
        $feedback = $this->standupFeedbackService->getFeedbackById($feedbackId);

        if (!$feedback) {
            return $response->withStatus(404);
        }

        $cohort = $this->cohortService->getCohortById($feedback['cohort_id']);
        $studentsData = json_decode($feedback['content'], true);

        return $this->view->render($response, 'standup_feedback/view.twig', [
            'feedback' => $feedback,
            'cohort' => $cohort,
            'studentsData' => $studentsData
        ]);
    }
}