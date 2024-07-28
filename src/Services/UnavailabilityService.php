<?php

/**
 * UnavailabilityService
 *
 * This service is responsible for managing student unavailabilities in the SOD (Speaker of the Day) application.
 */

namespace App\Services;

use App\Models\Unavailability;
use PDO;

class UnavailabilityService
{
    /**
     * @var PDO The database connection
     */
    private $db;

    /**
     * UnavailabilityService constructor.
     *
     * @param PDO $db The database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all unavailabilities.
     *
     * @return array An array of Unavailability objects
     */
    public function getAllUnavailabilities(): array
    {
        $stmt = $this->db->query("SELECT * FROM unavailabilities ORDER BY start_date_time");
        return $stmt->fetchAll(PDO::FETCH_CLASS, Unavailability::class);
    }

    /**
     * Get an unavailability by its ID.
     *
     * @param int $id The ID of the unavailability
     * @return Unavailability|null The Unavailability object if found, null otherwise
     */
    public function getUnavailabilityById(int $id): ?Unavailability
    {
        $stmt = $this->db->prepare("SELECT * FROM unavailabilities WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $unavailability = $stmt->fetchObject(Unavailability::class);
        return $unavailability ?: null;
    }

    /**
     * Create a new unavailability.
     *
     * @param Unavailability $unavailability The Unavailability object to create
     * @return int The ID of the newly created unavailability
     */
    public function createUnavailability(Unavailability $unavailability): int
    {
        $stmt = $this->db->prepare("INSERT INTO unavailabilities (student_id, start_date_time, end_date_time, reason) VALUES (:student_id, :start_date_time, :end_date_time, :reason)");
        $stmt->execute([
            ':student_id' => $unavailability->getStudentId(),
            ':start_date_time' => $unavailability->getStartDateTime()->format('Y-m-d H:i:s'),
            ':end_date_time' => $unavailability->getEndDateTime()->format('Y-m-d H:i:s'),
            ':reason' => $unavailability->getReason()
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing unavailability.
     *
     * @param Unavailability $unavailability The Unavailability object to update
     * @return bool True if the update was successful, false otherwise
     */
    public function updateUnavailability(Unavailability $unavailability): bool
    {
        $stmt = $this->db->prepare("UPDATE unavailabilities SET student_id = :student_id, start_date_time = :start_date_time, end_date_time = :end_date_time, reason = :reason WHERE id = :id");
        return $stmt->execute([
            ':id' => $unavailability->getId(),
            ':student_id' => $unavailability->getStudentId(),
            ':start_date_time' => $unavailability->getStartDateTime()->format('Y-m-d H:i:s'),
            ':end_date_time' => $unavailability->getEndDateTime()->format('Y-m-d H:i:s'),
            ':reason' => $unavailability->getReason()
        ]);
    }

    /**
     * Delete an unavailability by its ID.
     *
     * @param int $id The ID of the unavailability to delete
     * @return bool True if the deletion was successful, false otherwise
     */
    public function deleteUnavailability(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM unavailabilities WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get all unavailabilities for a specific student.
     *
     * @param int $studentId The ID of the student
     * @return array An array of Unavailability objects
     */
    public function getUnavailabilitiesByStudent(int $studentId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM unavailabilities WHERE student_id = :student_id ORDER BY start_date_time");
        $stmt->execute([':student_id' => $studentId]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, Unavailability::class);
    }

    /**
     * Get all unavailabilities for a specific date range.
     *
     * @param \DateTime $startDate The start date of the range
     * @param \DateTime $endDate The end date of the range
     * @return array An array of Unavailability objects
     */
    public function getUnavailabilitiesByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        $stmt = $this->db->prepare("SELECT * FROM unavailabilities WHERE start_date_time <= :end_date AND end_date_time >= :start_date ORDER BY start_date_time");
        $stmt->execute([
            ':start_date' => $startDate->format('Y-m-d H:i:s'),
            ':end_date' => $endDate->format('Y-m-d H:i:s')
        ]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, Unavailability::class);
    }

    /**
     * Check if a student is available on a specific date.
     *
     * @param int $studentId The ID of the student
     * @param \DateTime $date The date to check
     * @return bool True if the student is available, false otherwise
     */
    public function isStudentAvailable(int $studentId, \DateTime $date): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM unavailabilities WHERE student_id = :student_id AND :date BETWEEN start_date_time AND end_date_time");
        $stmt->execute([
            ':student_id' => $studentId,
            ':date' => $date->format('Y-m-d H:i:s')
        ]);
        return $stmt->fetchColumn() == 0;
    }
}
