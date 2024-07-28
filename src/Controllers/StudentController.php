<?php

/**
 * StudentController
 *
 * This controller handles all student-related operations in the SOD (Speaker of the Day) application.
 */

namespace App\Controllers;

use App\Models\Student;
use App\Services\StudentService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class StudentController
{
    /**
     * @var StudentService The student service
     */
    private $studentService;

    /**
     * @var LoggerInterface The logger
     */
    private $logger;

    /**
     * StudentController constructor.
     *
     * @param StudentService $studentService The student service
     * @param LoggerInterface $logger The logger
     */
    public function __construct(StudentService $studentService, LoggerInterface $logger)
    {
        $this->studentService = $studentService;
        $this->logger = $logger;
    }

    /**
     * Get all students.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function getAllStudents(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for getting all students');
        try {
            $students = $this->studentService->getAllStudents();
            $this->logger->info('Successfully retrieved all students', ['count' => count($students)]);
            $response->getBody()->write(json_encode($students));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving all students', ['error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while retrieving students']);
        }
    }

    /**
     * Get a student by ID.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function getStudent(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for getting student', ['id' => $id]);
        try {
            $student = $this->studentService->getStudentById($id);
            if (!$student) {
                $this->logger->warning('Student not found', ['id' => $id]);
                return $response->withStatus(404)->withJson(['error' => 'Student not found']);
            }
            $this->logger->info('Successfully retrieved student', ['id' => $id]);
            $response->getBody()->write(json_encode($student));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving student', ['id' => $id, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while retrieving the student']);
        }
    }

    /**
     * Create a new student.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function createStudent(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for creating a new student');
        try {
            $data = $request->getParsedBody();
            $student = new Student(
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                (int)$data['cohort_id']
            );
            $id = $this->studentService->createStudent($student);
            $this->logger->info('Successfully created new student', ['id' => $id]);
            $response->getBody()->write(json_encode(['id' => $id]));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error creating new student', ['error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while creating the student']);
        }
    }

    /**
     * Update an existing student.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function updateStudent(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for updating student', ['id' => $id]);
        try {
            $data = $request->getParsedBody();
            $student = $this->studentService->getStudentById($id);
            if (!$student) {
                $this->logger->warning('Student not found for update', ['id' => $id]);
                return $response->withStatus(404)->withJson(['error' => 'Student not found']);
            }
            $student->setFirstName($data['first_name']);
            $student->setLastName($data['last_name']);
            $student->setEmail($data['email']);
            $student->setCohortId((int)$data['cohort_id']);
            $success = $this->studentService->updateStudent($student);
            $this->logger->info('Successfully updated student', ['id' => $id, 'success' => $success]);
            return $response->withJson(['success' => $success]);
        } catch (\Exception $e) {
            $this->logger->error('Error updating student', ['id' => $id, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while updating the student']);
        }
    }

    /**
     * Delete a student.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function deleteStudent(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for deleting student', ['id' => $id]);
        try {
            $success = $this->studentService->deleteStudent($id);
            $this->logger->info('Student deletion attempt completed', ['id' => $id, 'success' => $success]);
            return $response->withJson(['success' => $success]);
        } catch (\Exception $e) {
            $this->logger->error('Error deleting student', ['id' => $id, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while deleting the student']);
        }
    }

    /**
     * Get all students in a specific cohort.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function getStudentsByCohort(Request $request, Response $response, array $args): Response
    {
        $cohortId = (int)$args['cohort_id'];
        $this->logger->info('Request received for getting students by cohort', ['cohort_id' => $cohortId]);
        try {
            $students = $this->studentService->getStudentsByCohort($cohortId);
            $this->logger->info('Successfully retrieved students by cohort', ['cohort_id' => $cohortId, 'count' => count($students)]);
            $response->getBody()->write(json_encode($students));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving students by cohort', ['cohort_id' => $cohortId, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while retrieving students for the cohort']);
        }
    }
}
