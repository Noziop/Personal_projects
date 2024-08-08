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
    $studentId = $args['id'];
    $student = $this->studentService->getStudentById($studentId);
    $cohorts = $this->cohortService->getAllCohorts();

    if (!$student) {
        // Gérer le cas où l'étudiant n'est pas trouvé
        return $response->withStatus(404);
    }

    if ($request->getMethod() === 'POST') {
        $data = $request->getParsedBody();
        $updated = $this->studentService->updateStudent(
            $studentId,
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['cohort_id']
        );

        if ($updated) {
            // Rediriger vers la liste des étudiants après la mise à jour
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
			$unavailability = $request->getParsedBody()['unavailability'] ?? null;
			$dates = $unavailability ? explode(' to ', $unavailability) : null;
			
			$this->studentService->updateUnavailability($studentId, $dates ? [$dates] : null);
	
			return $response->withHeader('Location', '/students')->withStatus(302);
		}
	
		return $this->view->render($response, 'students/unavailability.twig', [
			'student' => $student
		]);
	}
}
