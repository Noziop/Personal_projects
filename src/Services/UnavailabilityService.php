<?php

/**
 * UnavailabilityService
 *
 * This service is responsible for managing student unavailabilities in the SOD (Speaker of the Day) application.
 */

namespace App\Services;

use App\Models\Unavailability;
use PDO;
use Psr\Log\LoggerInterface;

class UnavailabilityService
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
     * UnavailabilityService constructor.
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
     * Get all unavailabilities.
     *
     * @return array An array of Unavailability objects
     * @throws \PDOException If there's an error executing the query
     */
    public function getAllUnavailabilities(): array
    {
        $this->logger->info('Fetching all unavailabilities');
        try {
            $stmt = $this->db->query("SELECT * FROM unavailabilities ORDER BY start_date_time");
            $unavailabilities = $stmt->fetchAll(PDO::FETCH_CLASS, Unavailability::class);
            $this->logger->info('Fetched ' . count($unavailabilities) . ' unavailabilities');
            return $unavailabilities;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching all unavailabilities: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get an unavailability by its ID.
     *
     * @param int $id The ID of the unavailability
     * @return Unavailability|null The Unavailability object if found, null otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function getUnavailabilityById(int $id): ?Unavailability
    {
        $this->logger->info('Fetching unavailability with id: ' . $id);
        try {
            $stmt = $this->db->prepare("SELECT * FROM unavailabilities WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $unavailability = $stmt->fetchObject(Unavailability::class);
            if ($unavailability) {
                $this->logger->info('Unavailability found', ['id' => $id]);
            } else {
                $this->logger->warning('Unavailability not found', ['id' => $id]);
            }
            return $unavailability ?: null;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching unavailability: ' . $e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Create a new unavailability.
     *
     * @param Unavailability $unavailability The Unavailability object to create
     * @return int The ID of the newly created unavailability
     * @throws \PDOException If there's an error executing the query
     */
    public function createUnavailability(Unavailability $unavailability): int
    {
        $this->logger->info('Creating new unavailability', [
            'student_id' => $unavailability->getStudentId(),
            'start_date_time' => $unavailability->getStartDateTime()->format('Y-m-d H:i:s'),
            'end_date_time' => $unavailability->getEndDateTime()->format('Y-m-d H:i:s')
        ]);
        try {
            $stmt = $this->db->prepare("INSERT INTO unavailabilities (student_id, start_date_time, end_date_time, reason) VALUES (:student_id, :start_date_time, :end_date_time, :reason)");
            $stmt->execute([
                ':student_id' => $unavailability->getStudentId(),
                ':start_date_time' => $unavailability->getStartDateTime()->format('Y-m-d H:i:s'),
                ':end_date_time' => $unavailability->getEndDateTime()->format('Y-m-d H:i:s'),
                ':reason' => $unavailability->getReason()
            ]);
            $id = (int) $this->db->lastInsertId();
            $this->logger->info('Unavailability created', ['id' => $id]);
            return $id;
        } catch (\PDOException $e) {
            $this->logger->error('Error creating unavailability: ' . $e->getMessage(), [
                'student_id' => $unavailability->getStudentId()
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing unavailability.
     *
     * @param Unavailability $unavailability The Unavailability object to update
     * @return bool True if the update was successful, false otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function updateUnavailability(Unavailability $unavailability): bool
    {
        $this->logger->info('Updating unavailability', ['id' => $unavailability->getId()]);
        try {
            $stmt = $this->db->prepare("UPDATE unavailabilities SET student_id = :student_id, start_date_time = :start_date_time, end_date_time = :end_date_time, reason = :reason WHERE id = :id");
            $success = $stmt->execute([
                ':id' => $unavailability->getId(),
                ':student_id' => $unavailability->getStudentId(),
                ':start_date_time' => $unavailability->getStartDateTime()->format('Y-m-d H:i:s'),
                ':end_date_time' => $unavailability->getEndDateTime()->format('Y-m-d H:i:s'),
                ':reason' => $unavailability->getReason()
            ]);
            if ($success) {
                $this->logger->info('Unavailability updated successfully', ['id' => $unavailability->getId()]);
            } else {
                $this->logger->warning('Failed to update unavailability', ['id' => $unavailability->getId()]);
            }
            return $success;
        } catch (\PDOException $e) {
            $this->logger->error('Error updating unavailability: ' . $e->getMessage(), ['id' => $unavailability->getId()]);
            throw $e;
        }
    }

    /**
     * Delete an unavailability by its ID.
     *
     * @param int $id The ID of the unavailability to delete
     * @return bool True if the deletion was successful, false otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function deleteUnavailability(int $id): bool
    {
        $this->logger->info('Deleting unavailability', ['id' => $id]);
        try {
            $stmt = $this->db->prepare("DELETE FROM unavailabilities WHERE id = :id");
            $success = $stmt->execute([':id' => $id]);
            if ($success) {
                $this->logger->info('Unavailability deleted successfully', ['id' => $id]);
            } else {
                $this->logger->warning('Failed to delete unavailability', ['id' => $id]);
            }
            return $success;
        } catch (\PDOException $e) {
            $this->logger->error('Error deleting unavailability: ' . $e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Get all unavailabilities for a specific student.
     *
     * @param int $studentId The ID of the student
     * @return array An array of Unavailability objects
     * @throws \PDOException If there's an error executing the query
     */
    public function getUnavailabilitiesByStudent(int $studentId): array
    {
        $this->logger->info('Fetching unavailabilities for student', ['student_id' => $studentId]);
        try {
            $stmt = $this->db->prepare("SELECT * FROM unavailabilities WHERE student_id = :student_id ORDER BY start_date_time");
            $stmt->execute([':student_id' => $studentId]);
            $unavailabilities = $stmt->fetchAll(PDO::FETCH_CLASS, Unavailability::class);
            $this->logger->info('Fetched ' . count($unavailabilities) . ' unavailabilities for student', ['student_id' => $studentId]);
            return $unavailabilities;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching unavailabilities for student: ' . $e->getMessage(), ['student_id' => $studentId]);
            throw $e;
        }
    }

    /**
     * Get all unavailabilities for a specific date range.
     *
     * @param \DateTime $startDate The start date of the range
     * @param \DateTime $endDate The end date of the range
     * @return array An array of Unavailability objects
     * @throws \PDOException If there's an error executing the query
     */
    public function getUnavailabilitiesByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        $this->logger->info('Fetching unavailabilities for date range', [
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'end_date' => $endDate->format('Y-m-d H:i:s')
        ]);
        try {
            $stmt = $this->db->prepare("SELECT * FROM unavailabilities WHERE start_date_time <= :end_date AND end_date_time >= :start_date ORDER BY start_date_time");
            $stmt->execute([
                ':start_date' => $startDate->format('Y-m-d H:i:s'),
                ':end_date' => $endDate->format('Y-m-d H:i:s')
            ]);
            $unavailabilities = $stmt->fetchAll(PDO::FETCH_CLASS, Unavailability::class);
            $this->logger->info('Fetched ' . count($unavailabilities) . ' unavailabilities for date range');
            return $unavailabilities;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching unavailabilities for date range: ' . $e->getMessage(), [
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s')
            ]);
            throw $e;
        }
    }

    /**
     * Check if a student is available on a specific date.
     *
     * @param int $studentId The ID of the student
     * @param \DateTime $date The date to check
     * @return bool True if the student is available, false otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function isStudentAvailable(int $studentId, \DateTime $date): bool
    {
        $this->logger->info('Checking student availability', [
            'student_id' => $studentId,
            'date' => $date->format('Y-m-d H:i:s')
        ]);
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM unavailabilities WHERE student_id = :student_id AND :date BETWEEN start_date_time AND end_date_time");
            $stmt->execute([
                ':student_id' => $studentId,
                ':date' => $date->format('Y-m-d H:i:s')
            ]);
            $isAvailable = $stmt->fetchColumn() == 0;
            $this->logger->info('Student availability check result', [
                'student_id' => $studentId,
                'date' => $date->format('Y-m-d H:i:s'),
                'is_available' => $isAvailable
            ]);
            return $isAvailable;
        } catch (\PDOException $e) {
            $this->logger->error('Error checking student availability: ' . $e->getMessage(), [
                'student_id' => $studentId,
                'date' => $date->format('Y-m-d H:i:s')
            ]);
            throw $e;
        }
    }
}
