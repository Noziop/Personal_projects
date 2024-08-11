<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Models\Unavailability;
use Psr\Log\LoggerInterface;
use DateTime;

class StudentService
{
    private $studentModel;
    private $userModel;
    private $unavailabilityModel;
    private $logger;

    public function __construct(Student $studentModel, User $userModel, Unavailability $unavailabilityModel, LoggerInterface $logger)
    {
        $this->studentModel = $studentModel;
        $this->userModel = $userModel;
        $this->unavailabilityModel = $unavailabilityModel;
        $this->logger = $logger;
    }

	public function createStudent($cohortId, $lastName, $firstName, $email, $password, $slackId = null)
	{
		$this->logger->info('Creating new student', [
			'cohort_id' => $cohortId,
			'last_name' => $lastName,
			'first_name' => $firstName,
			'email' => $email,
			'slack_id' => $slackId
		]);
	
		// Créer d'abord l'utilisateur
		$userData = [
			'username' => $email,
			'first_name' => $firstName,
			'last_name' => $lastName,
			'email' => $email,
			'password' => $password,
			'role' => 'student',
			'slack_id' => $slackId
		];
		$userId = $this->userService->createUser($userData);
	
		if (!$userId) {
			$this->logger->error('Failed to create user for student');
			return false;
		}
	
		// Ensuite, créer l'étudiant
		return $this->studentModel->create($userId, $cohortId);
	}

    public function getStudentById($id)
    {
        $this->logger->info('Fetching student by ID', ['id' => $id]);
        return $this->studentModel->findById($id);
    }

	public function updateStudent($id, $firstName, $lastName, $email, $cohortId, $username, $role)
	{
		$student = $this->getStudentById($id);
		if (!$student) {
			$this->logger->error('Student not found', ['id' => $id]);
			return false;
		}
	
		// Mettre à jour l'utilisateur
		$userUpdated = $this->userModel->update($student['user_id'], [
			'first_name' => $firstName,
			'last_name' => $lastName,
			'email' => $email,
			'username' => $username,
			'role' => $role
		]);
	
		// Mettre à jour l'étudiant
		$studentUpdated = $this->studentModel->update($id, $cohortId);
	
		return $userUpdated && $studentUpdated;
	}

    public function deleteStudent($id)
    {
        $this->logger->info('Deleting student', ['id' => $id]);
        $student = $this->getStudentById($id);
        if (!$student) {
            $this->logger->error('Student not found', ['id' => $id]);
            return false;
        }

        // Supprimer l'étudiant et l'utilisateur associé
        $this->studentModel->delete($id);
        return $this->userModel->delete($student['user_id']);
    }

    public function getAllStudents()
    {
        $this->logger->info('Fetching all students');
        $students = $this->studentModel->findAll();
        $this->logger->info('Fetched students', ['count' => count($students)]);
        return $students;
    }

    public function getStudentsByCohort($cohortId)
    {
        $this->logger->info('Fetching students by cohort', ['cohort_id' => $cohortId]);
        return $this->studentModel->findByCohort($cohortId);
    }

    public function getStudentByEmail($email)
    {
        $this->logger->info('Fetching student by email', ['email' => $email]);
        return $this->studentModel->findByEmail($email);
    }

    public function getStudentBySlackId($slackId)
    {
        $this->logger->info('Fetching student by Slack ID', ['slack_id' => $slackId]);
        return $this->studentModel->findBySlackId($slackId);
    }

	public function getStudentByUserId($userId)
	{
		$this->logger->info('Fetching student by user ID', ['user_id' => $userId]);
		$student = $this->studentModel->findByUserId($userId);
		if ($student instanceof Student) {
			return [
				'id' => $student->getId(),
				'first_name' => $student->getFirstName(),
				'last_name' => $student->getLastName(),
				'email' => $student->getEmail(),
				'slack_id' => $student->getSlackId(),
				'cohort_id' => $student->getCohortId()
			];
		}
		return $student; // Si c'est déjà un tableau, retournez-le tel quel
	}

    public function searchStudents($searchTerm)
    {
        $this->logger->info('Searching students', ['search_term' => $searchTerm]);
        return $this->studentModel->search($searchTerm);
    }

    public function updateUnavailability($studentId, $unavailabilityDates)
    {
        // Supprimer d'abord toutes les indisponibilités existantes pour cet étudiant
        $this->unavailabilityModel->deleteByStudentId($studentId);
    
        // Si de nouvelles dates d'indisponibilité sont fournies, les ajouter
        if ($unavailabilityDates) {
            foreach ($unavailabilityDates as $date) {
                $startDate = new DateTime($date[0]);
                $endDate = new DateTime($date[1]);
                $this->unavailabilityModel->create($studentId, $startDate, $endDate);
            }
        }
    
        return true;
    }

    public function getUnavailabilityForStudent($studentId)
    {
        $this->logger->info('Fetching unavailability for student', ['student_id' => $studentId]);
        return $this->unavailabilityModel->findByStudentId($studentId);
    }

    public function getTotalStudentsCount()
    {
        $this->logger->info('Fetching total student count');
        return $this->studentModel->getTotalCount();
    }
}