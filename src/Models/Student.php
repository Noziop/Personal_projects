<?php

namespace App\Models;

use PDO;

/**
 * Student Model
 *
 * This class represents a student in the application.
 */
class Student
{
    private $id;
    private $userId;
    private $cohortId;
    private $firstName;
    private $lastName;
    private $email;
    private $createdAt;

    private $db;

    /**
     * Student constructor.
     *
     * @param PDO $db The database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a student by their ID
     *
     * @param int $id The student ID
     * @return Student|null The student object if found, null otherwise
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $studentData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($studentData) {
            return $this->hydrate($studentData);
        }

        return null;
    }

    /**
     * Get all students
     *
     * @return array An array of Student objects
     */
    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM students ORDER BY last_name, first_name");
        $studentsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $students = [];
        foreach ($studentsData as $studentData) {
            $students[] = (new Student($this->db))->hydrate($studentData);
        }

        return $students;
    }

    /**
     * Create a new student
     *
     * @param array $data The student data
     * @return bool True if the student was created successfully, false otherwise
     */
    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO students (user_id, cohort_id, first_name, last_name, email) VALUES (:user_id, :cohort_id, :first_name, :last_name, :email)");
        return $stmt->execute([
            'user_id' => $data['user_id'],
            'cohort_id' => $data['cohort_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email']
        ]);
    }

    /**
     * Update an existing student
     *
     * @param array $data The student data to update
     * @return bool True if the student was updated successfully, false otherwise
     */
    public function update($data)
    {
        $stmt = $this->db->prepare("UPDATE students SET cohort_id = :cohort_id, first_name = :first_name, last_name = :last_name, email = :email WHERE id = :id");
        return $stmt->execute([
            'id' => $this->id,
            'cohort_id' => $data['cohort_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email']
        ]);
    }

    /**
     * Delete the student
     *
     * @return bool True if the student was deleted successfully, false otherwise
     */
    public function delete()
    {
        $stmt = $this->db->prepare("DELETE FROM students WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }

    /**
     * Get the student's cohort
     *
     * @return Cohort|null The Cohort object if found, null otherwise
     */
    public function getCohort()
    {
        return (new Cohort($this->db))->findById($this->cohortId);
    }

    /**
     * Get the student's unavailabilities
     *
     * @return array An array of Unavailability objects
     */
    public function getUnavailabilities()
    {
        $stmt = $this->db->prepare("SELECT * FROM unavailabilities WHERE student_id = :student_id");
        $stmt->execute(['student_id' => $this->id]);
        $unavailabilitiesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $unavailabilities = [];
        foreach ($unavailabilitiesData as $unavailabilityData) {
            $unavailabilities[] = (new Unavailability($this->db))->hydrate($unavailabilityData);
        }

        return $unavailabilities;
    }

    /**
     * Hydrate the student object with data
     *
     * @param array $data The data to hydrate the object with
     * @return Student The hydrated student object
     */
    private function hydrate($data)
    {
        $this->id = $data['id'];
        $this->userId = $data['user_id'];
        $this->cohortId = $data['cohort_id'];
        $this->firstName = $data['first_name'];
        $this->lastName = $data['last_name'];
        $this->email = $data['email'];
        $this->createdAt = $data['created_at'];

        return $this;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->userId; }
    public function getCohortId() { return $this->cohortId; }
    public function getFirstName() { return $this->firstName; }
    public function getLastName() { return $this->lastName; }
    public function getEmail() { return $this->email; }
    public function getCreatedAt() { return $this->createdAt; }

    // Additional methods
    public function getFullName() { return $this->firstName . ' ' . $this->lastName; }
}