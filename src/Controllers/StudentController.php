<?php

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

    public function __construct(StudentService $studentService, LoggerInterface $logger)
    {
        $this->studentService = $studentService;
        $this->logger = $logger;
    }

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

    public function createStudent(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for creating a new student');
        try {
            $data = $request->getParsedBody();
            if (!isset($data['first_name']) || !isset($data['last_name']) || !isset($data['email']) || !isset($data['cohort_id'])) {
                throw new HttpException('Données invalides pour la création de l\'étudiant', 400);
            }
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
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error creating new student', ['error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la création de l\'étudiant', 500);
        }
    }

    public function updateStudent(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for updating student', ['id' => $id]);
        try {
            $data = $request->getParsedBody();
            if (!isset($data['first_name']) || !isset($data['last_name']) || !isset($data['email']) || !isset($data['cohort_id'])) {
                throw new HttpException('Données invalides pour la mise à jour de l\'étudiant', 400);
            }
            $student = $this->studentService->getStudentById($id);
            if (!$student) {
                $this->logger->warning('Student not found for update', ['id' => $id]);
                throw new HttpException('Étudiant non trouvé', 404);
            }
            $student->setFirstName($data['first_name']);
            $student->setLastName($data['last_name']);
            $student->setEmail($data['email']);
            $student->setCohortId((int)$data['cohort_id']);
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
            throw new HttpException('Une erreur est survenue lors de la récupération des étudiants pour cette cohorte', 500);
        }
    }
}
