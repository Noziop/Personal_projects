<?php

/**
 * StudentService
 *
 * This service is responsible for managing students in the SOD (Speaker of the Day) application.
 */

namespace App\Services;

use App\Models\Student;
use PDO;

class StudentService
{
    /**
     * @var PDO The database connection
     */
    private $db;

    /**
     * StudentService constructor.
     *
     * @param PDO $db The database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all students.
     *
     * @return array An array of Student objects
     */
    public function getAllStudents(): array
    {
        $stmt = $this->db->query("SELECT * FROM students");
        return $stmt->fetchAll(PDO::FETCH_CLASS, Student::class);
    }

    /**
     * Get a student by their ID.
     *
     * @param int $id The ID of the student
     * @return Student|null The Student object if found, null otherwise
     */
    public function getStudentById(int $id): ?Student
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $student = $stmt->fetchObject(Student::class);
        return $student ?: null;
    }

    /**
     * Create a new student.
     *
     * @param Student $student The Student object to create
     * @return int The ID of the newly created student
     */
    public function createStudent(Student $student): int
    {
        $stmt = $this->db->prepare("INSERT INTO students (first_name, last_name, email, cohort_id) VALUES (:first_name, :last_name, :email, :cohort_id)");
        $stmt->execute([
            ':first_name' => $student->getFirstName(),
            ':last_name' => $student->getLastName(),
            ':email' => $student->getEmail(),
            ':cohort_id' => $student->getCohortId()
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing student.
     *
     * @param Student $student The Student object to update
     * @return bool True if the update was successful, false otherwise
     */
    public function updateStudent(Student $student): bool
    {
        $stmt = $this->db->prepare("UPDATE students SET first_name = :first_name, last_name = :last_name, email = :email, cohort_id = :cohort_id WHERE id = :id");
        return $stmt->execute([
            ':id' => $student->getId(),
            ':first_name' => $student->getFirstName(),
            ':last_name' => $student->getLastName(),
            ':email' => $student->getEmail(),
            ':cohort_id' => $student->getCohortId()
        ]);
    }

    /**
     * Delete a student by their ID.
     *
     * @param int $id The ID of the student to delete
     * @return bool True if the deletion was successful, false otherwise
     */
    public function deleteStudent(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM students WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get all students in a specific cohort.
     *
     * @param int $cohortId The ID of the cohort
     * @return array An array of Student objects
     */
    public function getStudentsByCohort(int $cohortId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE cohort_id = :cohort_id");
        $stmt->execute([':cohort_id' => $cohortId]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, Student::class);
    }
}
