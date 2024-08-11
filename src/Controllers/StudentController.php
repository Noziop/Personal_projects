<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Services\StudentService;
use App\Services\CohortService;
use App\Services\UserService;
use Psr\Log\LoggerInterface;

class StudentController
{
    private $view;
    private $studentService;
    private $cohortService;
    private $userService;
    private $logger;

    public function __construct(Twig $view, StudentService $studentService, CohortService $cohortService, UserService $userService, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->studentService = $studentService;
        $this->cohortService = $cohortService;
        $this->userService = $userService;
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response): Response
    {
        $students = $this->studentService->getAllStudents();
        $cohorts = $this->cohortService->getAllCohorts();
        return $this->view->render($response, 'students/index.twig', [
            'students' => $students,
            'cohorts' => $cohorts
        ]);
    }

    public function create(Request $request, Response $response): Response
    {
        $cohorts = $this->cohortService->getAllCohorts();
        
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $password = $this->generateRandomPassword();
            $student = $this->studentService->createStudent(
                $data['cohort_id'],
                $data['last_name'],
                $data['first_name'],
                $data['email'],
                $data['slack_id'] ?? null,
                $password
            );
            if ($student) {
                $this->logger->info('Student created', ['student_id' => $student]);
                $this->sendWelcomeEmail($data['email'], $password);
                return $response->withHeader('Location', '/students')->withStatus(302);
            }
        }
        
        return $this->view->render($response, 'students/create.twig', ['cohorts' => $cohorts]);
    }

	public function edit(Request $request, Response $response, array $args): Response
	{
		$studentId = $args['id'];
		$student = $this->studentService->getStudentById($studentId);
		$cohorts = $this->cohortService->getAllCohorts();
	
		if (!$student) {
			return $response->withStatus(404);
		}
	
		if ($request->getMethod() === 'POST') {
			$data = $request->getParsedBody();
			$updated = $this->studentService->updateStudent(
				$studentId,
				$data['first_name'],
				$data['last_name'],
				$data['email'],
				$data['cohort_id'],
				$data['username'],
				$data['role']
			);
	
			if ($updated) {
				return $response->withHeader('Location', '/students')->withStatus(302);
			}
		}
	
		return $this->view->render($response, 'students/edit.twig', [
			'student' => $student,
			'cohorts' => $cohorts
		]);
	}

    public function delete(Request $request, Response $response, array $args): Response
    {
        $deleted = $this->studentService->deleteStudent($args['id']);
        if ($deleted) {
            $this->logger->info('Student deleted', ['student_id' => $args['id']]);
            return $response->withHeader('Location', '/students')->withStatus(302);
        }
        return $response->withStatus(404);
    }

	public function manageUnavailability(Request $request, Response $response, array $args): Response
	{
		$studentId = $args['id'];
		$student = $this->studentService->getStudentById($studentId);
	
		if ($request->getMethod() === 'POST') {
			$data = $request->getParsedBody();
			$startDate = $data['start_date'] ?? null;
			$endDate = $data['end_date'] ?? null;
			
			if ($startDate && $endDate) {
				$this->studentService->updateUnavailability($studentId, [[$startDate, $endDate]]);
			}
	
			return $response->withHeader('Location', '/students')->withStatus(302);
		}
	
		// Récupérer les indisponibilités actuelles de l'étudiant
		$student['unavailabilities'] = $this->studentService->getUnavailabilityForStudent($studentId);
	
		return $this->view->render($response, 'students/unavailability.twig', [
			'student' => $student
		]);
	}

    private function generateRandomPassword($length = 10) {
        return bin2hex(random_bytes($length));
    }

    private function sendWelcomeEmail($email, $password) {
        // Implémentez l'envoi d'email ici
        // Vous pouvez utiliser une bibliothèque comme PHPMailer ou SwiftMailer
        $this->logger->info('Welcome email sent', ['email' => $email]);
    }
}