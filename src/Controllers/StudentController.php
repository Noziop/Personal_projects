<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Services\StudentService;
use App\Services\CohortService;
use Psr\Log\LoggerInterface;

class StudentController
{
    private $view;
    private $studentService;
    private $cohortService;
    private $logger;

    public function __construct(Twig $view, StudentService $studentService, CohortService $cohortService, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->studentService = $studentService;
        $this->cohortService = $cohortService;
        $this->logger = $logger;
    }

	public function index(Request $request, Response $response): Response
	{
		$students = $this->studentService->getAllStudents();
		$cohorts = $this->cohortService->getAllCohorts(); // Assurez-vous d'avoir injecté CohortService dans le constructeur
		$this->logger->info('Fetched students', ['count' => count($students)]);
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
            $student = $this->studentService->createStudent($data);
            if ($student) {
                $this->logger->info('Student created', ['student_id' => $student['id']]);
                return $response->withHeader('Location', '/students')->withStatus(302);
            }
        }
        
        return $this->view->render($response, 'students/create.twig', ['cohorts' => $cohorts]);
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        $student = $this->studentService->getStudentById($args['id']);
        $cohorts = $this->cohortService->getAllCohorts();
        
        if (!$student) {
            $this->logger->warning('Student not found', ['student_id' => $args['id']]);
            return $response->withStatus(404);
        }

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $updated = $this->studentService->updateStudent($args['id'], $data);
            if ($updated) {
                $this->logger->info('Student updated', ['student_id' => $args['id']]);
                return $response->withHeader('Location', '/students')->withStatus(302);
            }
        }

        return $this->view->render($response, 'students/edit.twig', ['student' => $student, 'cohorts' => $cohorts]);
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
        $student = $this->studentService->getStudentById($args['id']);
        
        if (!$student) {
            $this->logger->warning('Student not found', ['student_id' => $args['id']]);
            return $response->withStatus(404);
        }

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $updated = $this->studentService->updateUnavailability($args['id'], $data['unavailability']);
            if ($updated) {
                $this->logger->info('Student unavailability updated', ['student_id' => $args['id']]);
                return $response->withHeader('Location', '/students')->withStatus(302);
            }
        }

        return $this->view->render($response, 'students/unavailability.twig', ['student' => $student]);
    }
}
