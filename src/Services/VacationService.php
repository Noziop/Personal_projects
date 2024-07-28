<?php

/**
 * VacationService
 *
 * This service is responsible for managing vacations in the SOD (Speaker of the Day) application.
 * Vacations represent periods when no drawings should occur for specific cohorts.
 */

namespace App\Services;

use App\Models\Vacation;
use PDO;
use Psr\Log\LoggerInterface;

class VacationService
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
     * VacationService constructor.
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
     * Get all vacations.
     *
     * @return array An array of Vacation objects
     * @throws \PDOException If there's an error executing the query
     */
    public function getAllVacations(): array
    {
        $this->logger->info('Fetching all vacations');
        try {
            $stmt = $this->db->query("SELECT * FROM vacations ORDER BY start_date");
            $vacations = $stmt->fetchAll(PDO::FETCH_CLASS, Vacation::class);
            $this->logger->info('Fetched ' . count($vacations) . ' vacations');
            return $vacations;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching all vacations: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a vacation by its ID.
     *
     * @param int $id The ID of the vacation
     * @return Vacation|null The Vacation object if found, null otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function getVacationById(int $id): ?Vacation
    {
        $this->logger->info('Fetching vacation with id: ' . $id);
        try {
            $stmt = $this->db->prepare("SELECT * FROM vacations WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $vacation = $stmt->fetchObject(Vacation::class);
            if ($vacation) {
                $this->logger->info('Vacation found', ['id' => $id]);
            } else {
                $this->logger->warning('Vacation not found', ['id' => $id]);
            }
            return $vacation ?: null;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching vacation: ' . $e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Create a new vacation.
     *
     * @param Vacation $vacation The Vacation object to create
     * @return int The ID of the newly created vacation
     * @throws \PDOException If there's an error executing the query
     */
    public function createVacation(Vacation $vacation): int
    {
        $this->logger->info('Creating new vacation', [
            'cohort_id' => $vacation->getCohortId(),
            'name' => $vacation->getName(),
            'start_date' => $vacation->getStartDate()->format('Y-m-d'),
            'end_date' => $vacation->getEndDate()->format('Y-m-d')
        ]);
        try {
            $stmt = $this->db->prepare("INSERT INTO vacations (cohort_id, name, start_date, end_date) VALUES (:cohort_id, :name, :start_date, :end_date)");
            $stmt->execute([
                ':cohort_id' => $vacation->getCohortId(),
                ':name' => $vacation->getName(),
                ':start_date' => $vacation->getStartDate()->format('Y-m-d'),
                ':end_date' => $vacation->getEndDate()->format('Y-m-d')
            ]);
            $id = (int) $this->db->lastInsertId();
            $this->logger->info('Vacation created', ['id' => $id]);
            return $id;
        } catch (\PDOException $e) {
            $this->logger->error('Error creating vacation: ' . $e->getMessage(), [
                'cohort_id' => $vacation->getCohortId(),
                'name' => $vacation->getName()
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing vacation.
     *
     * @param Vacation $vacation The Vacation object to update
     * @return bool True if the update was successful, false otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function updateVacation(Vacation $vacation): bool
    {
        $this->logger->info('Updating vacation', ['id' => $vacation->getId()]);
        try {
            $stmt = $this->db->prepare("UPDATE vacations SET cohort_id = :cohort_id, name = :name, start_date = :start_date, end_date = :end_date WHERE id = :id");
            $success = $stmt->execute([
                ':id' => $vacation->getId(),
                ':cohort_id' => $vacation->getCohortId(),
                ':name' => $vacation->getName(),
                ':start_date' => $vacation->getStartDate()->format('Y-m-d'),
                ':end_date' => $vacation->getEndDate()->format('Y-m-d')
            ]);
            if ($success) {
                $this->logger->info('Vacation updated successfully', ['id' => $vacation->getId()]);
            } else {
                $this->logger->warning('Failed to update vacation', ['id' => $vacation->getId()]);
            }
            return $success;
        } catch (\PDOException $e) {
            $this->logger->error('Error updating vacation: ' . $e->getMessage(), ['id' => $vacation->getId()]);
            throw $e;
        }
    }

    /**
     * Delete a vacation by its ID.
     *
     * @param int $id The ID of the vacation to delete
     * @return bool True if the deletion was successful, false otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function deleteVacation(int $id): bool
    {
        $this->logger->info('Deleting vacation', ['id' => $id]);
        try {
            $stmt = $this->db->prepare("DELETE FROM vacations WHERE id = :id");
            $success = $stmt->execute([':id' => $id]);
            if ($success) {
                $this->logger->info('Vacation deleted successfully', ['id' => $id]);
            } else {
                $this->logger->warning('Failed to delete vacation', ['id' => $id]);
            }
            return $success;
        } catch (\PDOException $e) {
            $this->logger->error('Error deleting vacation: ' . $e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Get all vacations for a specific cohort.
     *
     * @param int $cohortId The ID of the cohort
     * @return array An array of Vacation objects
     * @throws \PDOException If there's an error executing the query
     */
    public function getVacationsByCohort(int $cohortId): array
    {
        $this->logger->info('Fetching vacations for cohort', ['cohort_id' => $cohortId]);
        try {
            $stmt = $this->db->prepare("SELECT * FROM vacations WHERE cohort_id = :cohort_id ORDER BY start_date");
            $stmt->execute([':cohort_id' => $cohortId]);
            $vacations = $stmt->fetchAll(PDO::FETCH_CLASS, Vacation::class);
            $this->logger->info('Fetched ' . count($vacations) . ' vacations for cohort', ['cohort_id' => $cohortId]);
            return $vacations;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching vacations for cohort: ' . $e->getMessage(), ['cohort_id' => $cohortId]);
            throw $e;
        }
    }

    /**
     * Get all vacations for a specific date range.
     *
     * @param \DateTime $startDate The start date of the range
     * @param \DateTime $endDate The end date of the range
     * @return array An array of Vacation objects
     * @throws \PDOException If there's an error executing the query
     */
    public function getVacationsByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        $this->logger->info('Fetching vacations for date range', [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);
        try {
            $stmt = $this->db->prepare("SELECT * FROM vacations WHERE start_date <= :end_date AND end_date >= :start_date ORDER BY start_date");
            $stmt->execute([
                ':start_date' => $startDate->format('Y-m-d'),
                ':end_date' => $endDate->format('Y-m-d')
            ]);
            $vacations = $stmt->fetchAll(PDO::FETCH_CLASS, Vacation::class);
            $this->logger->info('Fetched ' . count($vacations) . ' vacations for date range');
            return $vacations;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching vacations for date range: ' . $e->getMessage(), [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ]);
            throw $e;
        }
    }

    /**
     * Check if a given date is within any vacation period for a specific cohort.
     *
     * @param int $cohortId The ID of the cohort
     * @param \DateTime $date The date to check
     * @return bool True if the date is within a vacation period, false otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function isDateInVacation(int $cohortId, \DateTime $date): bool
    {
        $this->logger->info('Checking if date is in vacation', [
            'cohort_id' => $cohortId,
            'date' => $date->format('Y-m-d')
        ]);
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM vacations WHERE cohort_id = :cohort_id AND :date BETWEEN start_date AND end_date");
            $stmt->execute([
                ':cohort_id' => $cohortId,
                ':date' => $date->format('Y-m-d')
            ]);
            $isInVacation = $stmt->fetchColumn() > 0;
            $this->logger->info('Date in vacation check result', [
                'cohort_id' => $cohortId,
                'date' => $date->format('Y-m-d'),
                'is_in_vacation' => $isInVacation
            ]);
            return $isInVacation;
        } catch (\PDOException $e) {
            $this->logger->error('Error checking if date is in vacation: ' . $e->getMessage(), [
                'cohort_id' => $cohortId,
                'date' => $date->format('Y-m-d')
            ]);
            throw $e;
        }
    }
}
