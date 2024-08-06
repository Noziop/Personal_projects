<?php
/**
 * StudentController.php
 * 
 * This file contains the StudentController class which handles all student-related
 * HTTP requests and responses.
 * 
 * @package App\Controllers
 */

namespace App\Controllers;

use App\Models\Student;
use App\Services\StudentService;
use App\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class StudentController
{
    private $studentService;
    private $logger;

    /**
     * StudentController constructor.
     * 
     * @param StudentService $studentService The student service
     * @param LoggerInterface $logger The logger interface
     */
    public function __construct(StudentService $studentService, LoggerInterface $logger)
    {
        $this->studentService = $studentService;
        $this->logger = $logger;
        $this->logger->debug('StudentController initialized');
    }

    /**
     * Get all students
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     * @throws HttpException
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
            throw new HttpException('Une erreur est survenue lors de la récupération des étudiants', 500);
        }
    }

    /**
     * Get a specific student by ID
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args Route arguments
     * @return Response
     * @throws HttpException
     */
    public function getStudent(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for getting student', ['id' => $id]);
        try {
            $student = $this->studentService->getStudentById($id);
            if (!$student) {
                $this->logger->warning('Student not found', ['id' => $id]);
                throw new HttpException('Étudiant non trouvé', 404);
            }
            $this->logger->info('Successfully retrieved student', ['id' => $id]);
            $response->getBody()->write(json_encode($student));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving student', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la récupération de l\'étudiant', 500);
        }
    }

    /**
     * Create a new student
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     * @throws HttpException
     */
    public function createStudent(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for creating a new student');
        try {
            $data = $request->getParsedBody();
            $this->logger->debug('Received data for new student', ['data' => $data]);
            if (!isset($data['last_name']) || !isset($data['first_name']) || !isset($data['email']) || !isset($data['cohort_id'])) {
                $this->logger->warning('Invalid data for student creation', ['data' => $data]);
                throw new HttpException('Données invalides pour la création de l\'étudiant', 400);
            }
            $student = new Student(
                $data['last_name'],
                $data['first_name'],
                $data['email'],
                $data['cohort_id']
            );
            $id = $this->studentService->createStudent($student);
            $this->logger->info('Successfully created new student', ['id' => $id]);
            $response->getBody()->write(json_encode(['id' => $id]));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error creating new student', ['error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la création de l\'étudiant', 500);
        }
    }

    /**
     * Update an existing student
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args Route arguments
     * @return Response
     * @throws HttpException
     */
    public function updateStudent(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for updating student', ['id' => $id]);
        try {
            $data = $request->getParsedBody();
            $this->logger->debug('Received data for student update', ['id' => $id, 'data' => $data]);
            if (!isset($data['last_name']) || !isset($data['first_name']) || !isset($data['email']) || !isset($data['cohort_id'])) {
                $this->logger->warning('Invalid data for student update', ['id' => $id, 'data' => $data]);
                throw new HttpException('Données invalides pour la mise à jour de l\'étudiant', 400);
            }
            $student = $this->studentService->getStudentById($id);
            if (!$student) {
                $this->logger->warning('Student not found for update', ['id' => $id]);
                throw new HttpException('Étudiant non trouvé', 404);
            }
            $student->setLastName($data['last_name']);
            $student->setFirstName($data['first_name']);
            $student->setEmail($data['email']);
            $student->setCohortId($data['cohort_id']);
            $success = $this->studentService->updateStudent($student);
            $this->logger->info('Successfully updated student', ['id' => $id, 'success' => $success]);
            return $response->withJson(['success' => $success]);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error updating student', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la mise à jour de l\'étudiant', 500);
        }
    }

    /**
     * Delete a student
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args Route arguments
     * @return Response
     * @throws HttpException
     */
    public function deleteStudent(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for deleting student', ['id' => $id]);
        try {
            $student = $this->studentService->getStudentById($id);
            if (!$student) {
                $this->logger->warning('Student not found for deletion', ['id' => $id]);
                throw new HttpException('Étudiant non trouvé', 404);
            }
            $success = $this->studentService->deleteStudent($id);
            $this->logger->info('Student deletion attempt completed', ['id' => $id, 'success' => $success]);
            if (!$success) {
                throw new HttpException('L\'étudiant n\'a pas pu être supprimé', 500);
            }
            return $response->withJson(['success' => $success]);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error deleting student', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la suppression de l\'étudiant', 500);
        }
    }
}