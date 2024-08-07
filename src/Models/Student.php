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
    private $slackId;
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
	public function findAll()
	{
		$query = "
			SELECT 
				s.*, 
				c.name as cohort_name, 
				(SELECT COUNT(*) FROM unavailabilities u WHERE u.student_id = s.id) as unavailability,
				(SELECT COUNT(*) FROM sod_schedules ss WHERE ss.student_id = s.id) as sod_count
			FROM students s 
			LEFT JOIN cohorts c ON s.cohort_id = c.id
		";
		$stmt = $this->db->query($query);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

    /**
     * Create a new student
     *
     * @param int $cohortId The cohort ID
     * @param string $lastName The student's last name
     * @param string $firstName The student's first name
     * @param string $email The student's email
     * @param string|null $slackId The student's Slack ID (optional)
     * @return bool True if the student was created successfully, false otherwise
     */
    public function create($cohortId, $lastName, $firstName, $email, $slackId = null)
    {
        $stmt = $this->db->prepare("INSERT INTO students (cohort_id, last_name, first_name, email, slack_id) VALUES (:cohort_id, :last_name, :first_name, :email, :slack_id)");
        return $stmt->execute([
            'cohort_id' => $cohortId,
            'last_name' => $lastName,
            'first_name' => $firstName,
            'email' => $email,
            'slack_id' => $slackId
        ]);
    }

    /**
     * Update an existing student
     *
     * @param int $id The student ID
     * @param int $cohortId The cohort ID
     * @param string $lastName The student's last name
     * @param string $firstName The student's first name
     * @param string $email The student's email
     * @param string|null $slackId The student's Slack ID (optional)
     * @return bool True if the student was updated successfully, false otherwise
     */
    public function update($id, $cohortId, $lastName, $firstName, $email, $slackId = null)
    {
        $stmt = $this->db->prepare("UPDATE students SET cohort_id = :cohort_id, last_name = :last_name, first_name = :first_name, email = :email, slack_id = :slack_id WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'cohort_id' => $cohortId,
            'last_name' => $lastName,
            'first_name' => $firstName,
            'email' => $email,
            'slack_id' => $slackId
        ]);
    }

    /**
     * Delete the student
     *
     * @param int $id The student ID
     * @return bool True if the student was deleted successfully, false otherwise
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM students WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Find students by cohort
     *
     * @param int $cohortId The cohort ID
     * @return array An array of Student objects
     */
    public function findByCohort($cohortId)
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE cohort_id = :cohort_id ORDER BY last_name, first_name");
        $stmt->execute(['cohort_id' => $cohortId]);
        $studentsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $students = [];
        foreach ($studentsData as $studentData) {
            $students[] = (new Student($this->db))->hydrate($studentData);
        }

        return $students;
    }

    /**
     * Find a student by email
     *
     * @param string $email The student's email
     * @return Student|null The student object if found, null otherwise
     */
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $studentData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($studentData) {
            return $this->hydrate($studentData);
        }

        return null;
    }

    /**
     * Find a student by Slack ID
     *
     * @param string $slackId The student's Slack ID
     * @return Student|null The student object if found, null otherwise
     */
    public function findBySlackId($slackId)
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE slack_id = :slack_id");
        $stmt->execute(['slack_id' => $slackId]);
        $studentData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($studentData) {
            return $this->hydrate($studentData);
        }

        return null;
    }

    /**
     * Search students by name or email
     *
     * @param string $searchTerm The search term
     * @return array An array of Student objects
     */
    public function search($searchTerm)
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE last_name LIKE :search OR first_name LIKE :search OR email LIKE :search ORDER BY last_name, first_name");
        $stmt->execute(['search' => "%$searchTerm%"]);
        $studentsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $students = [];
        foreach ($studentsData as $studentData) {
            $students[] = (new Student($this->db))->hydrate($studentData);
        }

        return $students;
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
        $this->userId = $data['user_id'] ?? null;
        $this->cohortId = $data['cohort_id'];
        $this->firstName = $data['first_name'];
        $this->lastName = $data['last_name'];
        $this->email = $data['email'];
        $this->slackId = $data['slack_id'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;

        return $this;
    }

	public function getTotalCount()
	{
		$stmt = $this->db->query("SELECT COUNT(*) FROM students");
		return $stmt->fetchColumn();
	}

    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->userId; }
    public function getCohortId() { return $this->cohortId; }
    public function getFirstName() { return $this->firstName; }
    public function getLastName() { return $this->lastName; }
    public function getEmail() { return $this->email; }
    public function getSlackId() { return $this->slackId; }
    public function getCreatedAt() { return $this->createdAt; }

    // Additional methods
    public function getFullName() { return $this->firstName . ' ' . $this->lastName; }
}
