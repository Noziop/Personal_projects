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

class StudentController
{
    /**
     * @var StudentService The student service
     */
    private $studentService;

    /**
     * StudentController constructor.
     *
     * @param StudentService $studentService The student service
     */
    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
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
        $students = $this->studentService->getAllStudents();
        $response->getBody()->write(json_encode($students));
        return $response->withHeader('Content-Type', 'application/json');
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
        $student = $this->studentService->getStudentById((int)$args['id']);
        if (!$student) {
            return $response->withStatus(404)->withJson(['error' => 'Student not found']);
        }
        $response->getBody()->write(json_encode($student));
        return $response->withHeader('Content-Type', 'application/json');
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
        $data = $request->getParsedBody();
        $student = new Student(
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            (int)$data['cohort_id']
        );
        $id = $this->studentService->createStudent($student);
        $response->getBody()->write(json_encode(['id' => $id]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
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
        $data = $request->getParsedBody();
        $student = $this->studentService->getStudentById((int)$args['id']);
        if (!$student) {
            return $response->withStatus(404)->withJson(['error' => 'Student not found']);
        }
        $student->setFirstName($data['first_name']);
        $student->setLastName($data['last_name']);
        $student->setEmail($data['email']);
        $student->setCohortId((int)$data['cohort_id']);
        $success = $this->studentService->updateStudent($student);
        return $response->withJson(['success' => $success]);
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
        $success = $this->studentService->deleteStudent((int)$args['id']);
        return $response->withJson(['success' => $success]);
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
        $students = $this->studentService->getStudentsByCohort((int)$args['cohort_id']);
        $response->getBody()->write(json_encode($students));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
