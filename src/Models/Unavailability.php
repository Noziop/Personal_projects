<?php

namespace App\Models;

use PDO;
use DateTime;

/**
 * Unavailability Model
 *
 * This class represents a student's unavailability period in the application.
 */
class Unavailability
{
    private $id;
    private $studentId;
    private $startDate;
    private $endDate;
    private $reason;
    private $createdAt;

    private $db;

    /**
     * Unavailability constructor.
     *
     * @param PDO $db The database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find an unavailability by its ID
     *
     * @param int $id The unavailability ID
     * @return Unavailability|null The unavailability object if found, null otherwise
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM unavailabilities WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $unavailabilityData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($unavailabilityData) {
            return $this->hydrate($unavailabilityData);
        }

        return null;
    }

    /**
     * Get all unavailabilities
     *
     * @return array An array of Unavailability objects
     */
    public function findAll()
    {
        $stmt = $this->db->query("SELECT * FROM unavailabilities ORDER BY start_date");
        $unavailabilitiesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $unavailabilities = [];
        foreach ($unavailabilitiesData as $unavailabilityData) {
            $unavailabilities[] = (new Unavailability($this->db))->hydrate($unavailabilityData);
        }

        return $unavailabilities;
    }

    /**
     * Get all unavailabilities for a specific student
     *
     * @param int $studentId The student ID
     * @return array An array of Unavailability objects
     */
    public function findByStudentId($studentId)
    {
        $stmt = $this->db->prepare("SELECT * FROM unavailabilities WHERE student_id = :student_id ORDER BY start_date");
        $stmt->execute(['student_id' => $studentId]);
        $unavailabilitiesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $unavailabilities = [];
        foreach ($unavailabilitiesData as $unavailabilityData) {
            $unavailabilities[] = (new Unavailability($this->db))->hydrate($unavailabilityData);
        }

        return $unavailabilities;
    }

    /**
     * Find unavailabilities by date range
     *
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return array An array of Unavailability objects
     */
    public function findByDateRange(DateTime $startDate, DateTime $endDate)
    {
        $stmt = $this->db->prepare("SELECT * FROM unavailabilities WHERE 
            (start_date BETWEEN :start_date AND :end_date) OR
            (end_date BETWEEN :start_date AND :end_date) OR
            (start_date <= :start_date AND end_date >= :end_date)
            ORDER BY start_date");
        $stmt->execute([
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);
        $unavailabilitiesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $unavailabilities = [];
        foreach ($unavailabilitiesData as $unavailabilityData) {
            $unavailabilities[] = (new Unavailability($this->db))->hydrate($unavailabilityData);
        }

        return $unavailabilities;
    }

    /**
     * Create a new unavailability
     *
     * @param int $studentId
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param string $reason
     * @return bool True if the unavailability was created successfully, false otherwise
     */
    public function create($studentId, DateTime $startDate, DateTime $endDate, $reason)
    {
        $stmt = $this->db->prepare("INSERT INTO unavailabilities (student_id, start_date, end_date, reason) VALUES (:student_id, :start_date, :end_date, :reason)");
        return $stmt->execute([
            'student_id' => $studentId,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'reason' => $reason
        ]);
    }

    /**
     * Update an existing unavailability
     *
     * @param int $id
     * @param int $studentId
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param string $reason
     * @return bool True if the unavailability was updated successfully, false otherwise
     */
    public function update($id, $studentId, DateTime $startDate, DateTime $endDate, $reason)
    {
        $stmt = $this->db->prepare("UPDATE unavailabilities SET student_id = :student_id, start_date = :start_date, end_date = :end_date, reason = :reason WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'student_id' => $studentId,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'reason' => $reason
        ]);
    }

    /**
     * Delete the unavailability
     *
     * @param int $id
     * @return bool True if the unavailability was deleted successfully, false otherwise
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM unavailabilities WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Check if a student is available on a specific date
     *
     * @param int $studentId The student ID
     * @param DateTime $date The date to check
     * @return bool True if the student is available, false otherwise
     */
    public function isStudentAvailable($studentId, DateTime $date)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM unavailabilities WHERE student_id = :student_id AND :date BETWEEN start_date AND end_date");
        $stmt->execute([
            'student_id' => $studentId,
            'date' => $date->format('Y-m-d')
        ]);
        
        return $stmt->fetchColumn() == 0;
    }

    /**
     * Get available students for a specific date
     *
     * @param DateTime $date
     * @return array An array of available student IDs
     */
    public function getAvailableStudents(DateTime $date)
    {
        $stmt = $this->db->prepare("
            SELECT s.id
            FROM students s
            WHERE s.id NOT IN (
                SELECT u.student_id
                FROM unavailabilities u
                WHERE :date BETWEEN u.start_date AND u.end_date
            )
        ");
        $stmt->execute(['date' => $date->format('Y-m-d')]);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get the student associated with this unavailability
     *
     * @return Student|null The Student object if found, null otherwise
     */
    public function getStudent()
    {
        return (new Student($this->db))->findById($this->studentId);
    }

    /**
     * Hydrate the unavailability object with data
     *
     * @param array $data The data to hydrate the object with
     * @return Unavailability The hydrated unavailability object
     */
    private function hydrate($data)
    {
        $this->id = $data['id'];
        $this->studentId = $data['student_id'];
        $this->startDate = new DateTime($data['start_date']);
        $this->endDate = new DateTime($data['end_date']);
        $this->reason = $data['reason'];
        $this->createdAt = new DateTime($data['created_at']);

        return $this;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getStudentId() { return $this->studentId; }
    public function getStartDate() { return $this->startDate; }
    public function getEndDate() { return $this->endDate; }
    public function getReason() { return $this->reason; }
    public function getCreatedAt() { return $this->createdAt; }
}
