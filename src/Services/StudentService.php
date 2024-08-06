<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Unavailability;
use Psr\Log\LoggerInterface;

class StudentService
{
    private $studentModel;
    private $unavailabilityModel;
    private $logger;

    public function __construct(Student $studentModel, Unavailability $unavailabilityModel, LoggerInterface $logger)
    {
        $this->studentModel = $studentModel;
        $this->unavailabilityModel = $unavailabilityModel;
        $this->logger = $logger;
    }

    public function createStudent($cohortId, $lastName, $firstName, $email, $slackId = null)
    {
        $this->logger->info('Creating new student', [
            'cohort_id' => $cohortId,
            'last_name' => $lastName,
            'first_name' => $firstName,
            'email' => $email,
            'slack_id' => $slackId
        ]);

        return $this->studentModel->create($cohortId, $lastName, $firstName, $email, $slackId);
    }

    public function getStudentById($id)
    {
        $this->logger->info('Fetching student by ID', ['id' => $id]);
        return $this->studentModel->findById($id);
    }

    public function updateStudent($id, $cohortId, $lastName, $firstName, $email, $slackId = null)
    {
        $this->logger->info('Updating student', [
            'id' => $id,
            'cohort_id' => $cohortId,
            'last_name' => $lastName,
            'first_name' => $firstName,
            'email' => $email,
            'slack_id' => $slackId
        ]);

        return $this->studentModel->update($id, $cohortId, $lastName, $firstName, $email, $slackId);
    }

    public function deleteStudent($id)
    {
        $this->logger->info('Deleting student', ['id' => $id]);
        return $this->studentModel->delete($id);
    }

    public function getAllStudents()
    {
        $this->logger->info('Fetching all students');
        return $this->studentModel->findAll();
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

    public function searchStudents($searchTerm)
    {
        $this->logger->info('Searching students', ['search_term' => $searchTerm]);
        return $this->studentModel->search($searchTerm);
    }

    public function updateUnavailability($studentId, $unavailabilityData)
    {
        $this->logger->info('Updating student unavailability', ['student_id' => $studentId]);

        // First, delete all existing unavailability for this student
        $this->unavailabilityModel->deleteByStudentId($studentId);

        // Then, create new unavailability entries
        foreach ($unavailabilityData as $date) {
            $this->unavailabilityModel->create([
                'student_id' => $studentId,
                'date' => $date
            ]);
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
