<?php

/**
 * StudentService
 *
 * This service is responsible for managing students in the SOD (Speaker of the Day) application.
 */

namespace App\Services;

use App\Models\Student;
use PDO;
use Psr\Log\LoggerInterface;

class StudentService
{
    /**
     * @var PDO The database connection
     */
    private $db;

    /**
     * @var LoggerInterface The logger
     */
    private $logger;

    /**
     * StudentService constructor.
     *
     * @param PDO $db The database connection
     * @param LoggerInterface $logger The logger
     */
    public function __construct(PDO $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Get all students.
     *
     * @return array An array of Student objects
     * @throws \PDOException If there's an error executing the query
     */
    public function getAllStudents(): array
    {
        $this->logger->info('Fetching all students');
        try {
            $stmt = $this->db->query("SELECT * FROM students");
            $students = $stmt->fetchAll(PDO::FETCH_CLASS, Student::class);
            $this->logger->info('Fetched ' . count($students) . ' students');
            return $students;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching all students: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a student by their ID.
     *
     * @param int $id The ID of the student
     * @return Student|null The Student object if found, null otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function getStudentById(int $id): ?Student
    {
        $this->logger->info('Fetching student with id: ' . $id);
        try {
            $stmt = $this->db->prepare("SELECT * FROM students WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $student = $stmt->fetchObject(Student::class);
            if ($student) {
                $this->logger->info('Student found', ['id' => $id]);
            } else {
                $this->logger->warning('Student not found', ['id' => $id]);
            }
            return $student ?: null;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching student: ' . $e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Create a new student.
     *
     * @param Student $student The Student object to create
     * @return int The ID of the newly created student
     * @throws \PDOException If there's an error executing the query
     */
    public function createStudent(Student $student): int
    {
        $this->logger->info('Creating new student', ['name' => $student->getFullName()]);
        try {
            $stmt = $this->db->prepare("INSERT INTO students (first_name, last_name, email, cohort_id) VALUES (:first_name, :last_name, :email, :cohort_id)");
            $stmt->execute([
                ':first_name' => $student->getFirstName(),
                ':last_name' => $student->getLastName(),
                ':email' => $student->getEmail(),
                ':cohort_id' => $student->getCohortId()
            ]);
            $id = (int) $this->db->lastInsertId();
            $this->logger->info('Student created', ['id' => $id]);
            return $id;
        } catch (\PDOException $e) {
            $this->logger->error('Error creating student: ' . $e->getMessage(), ['name' => $student->getFullName()]);
            throw $e;
        }
    }

    /**
     * Update an existing student.
     *
     * @param Student $student The Student object to update
     * @return bool True if the update was successful, false otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function updateStudent(Student $student): bool
    {
        $this->logger->info('Updating student', ['id' => $student->getId()]);
        try {
            $stmt = $this->db->prepare("UPDATE students SET first_name = :first_name, last_name = :last_name, email = :email, cohort_id = :cohort_id WHERE id = :id");
            $success = $stmt->execute([
                ':id' => $student->getId(),
                ':first_name' => $student->getFirstName(),
                ':last_name' => $student->getLastName(),
                ':email' => $student->getEmail(),
                ':cohort_id' => $student->getCohortId()
            ]);
            if ($success) {
                $this->logger->info('Student updated successfully', ['id' => $student->getId()]);
            } else {
                $this->logger->warning('Failed to update student', ['id' => $student->getId()]);
            }
            return $success;
        } catch (\PDOException $e) {
            $this->logger->error('Error updating student: ' . $e->getMessage(), ['id' => $student->getId()]);
            throw $e;
        }
    }

    /**
     * Delete a student by their ID.
     *
     * @param int $id The ID of the student to delete
     * @return bool True if the deletion was successful, false otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function deleteStudent(int $id): bool
    {
        $this->logger->info('Deleting student', ['id' => $id]);
        try {
            $stmt = $this->db->prepare("DELETE FROM students WHERE id = :id");
            $success = $stmt->execute([':id' => $id]);
            if ($success) {
                $this->logger->info('Student deleted successfully', ['id' => $id]);
            } else {
                $this->logger->warning('Failed to delete student', ['id' => $id]);
            }
            return $success;
        } catch (\PDOException $e) {
            $this->logger->error('Error deleting student: ' . $e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Get all students in a specific cohort.
     *
     * @param int $cohortId The ID of the cohort
     * @return array An array of Student objects
     * @throws \PDOException If there's an error executing the query
     */
    public function getStudentsByCohort(int $cohortId): array
    {
        $this->logger->info('Fetching students for cohort', ['cohort_id' => $cohortId]);
        try {
            $stmt = $this->db->prepare("SELECT * FROM students WHERE cohort_id = :cohort_id");
            $stmt->execute([':cohort_id' => $cohortId]);
            $students = $stmt->fetchAll(PDO::FETCH_CLASS, Student::class);
            $this->logger->info('Fetched ' . count($students) . ' students for cohort', ['cohort_id' => $cohortId]);
            return $students;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching students for cohort: ' . $e->getMessage(), ['cohort_id' => $cohortId]);
            throw $e;
        }
    }
}
