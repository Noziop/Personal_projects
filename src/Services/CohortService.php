<?php

/**
 * CohortService
 *
 * This service is responsible for managing cohorts in the SOD (Speaker of the Day) application.
 */

namespace App\Services;

use App\Models\Cohort;
use PDO;
use Psr\Log\LoggerInterface;

class CohortService
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
     * CohortService constructor.
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
     * Get all cohorts.
     *
     * @return array An array of Cohort objects
     * @throws \PDOException If there's an error executing the query
     */
    public function getAllCohorts(): array
    {
        $this->logger->info('Fetching all cohorts');
        try {
            $stmt = $this->db->query("SELECT * FROM cohorts");
            $cohorts = $stmt->fetchAll(PDO::FETCH_CLASS, Cohort::class);
            $this->logger->info('Fetched ' . count($cohorts) . ' cohorts');
            return $cohorts;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching all cohorts: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a cohort by its ID.
     *
     * @param int $id The ID of the cohort
     * @return Cohort|null The Cohort object if found, null otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function getCohortById(int $id): ?Cohort
    {
        $this->logger->info('Fetching cohort with id: ' . $id);
        try {
            $stmt = $this->db->prepare("SELECT * FROM cohorts WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $cohort = $stmt->fetchObject(Cohort::class);
            if ($cohort) {
                $this->logger->info('Cohort found', ['id' => $id]);
            } else {
                $this->logger->warning('Cohort not found', ['id' => $id]);
            }
            return $cohort ?: null;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching cohort: ' . $e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Create a new cohort.
     *
     * @param Cohort $cohort The Cohort object to create
     * @return int The ID of the newly created cohort
     * @throws \PDOException If there's an error executing the query
     */
    public function createCohort(Cohort $cohort): int
    {
        $this->logger->info('Creating new cohort', ['name' => $cohort->getName()]);
        try {
            $stmt = $this->db->prepare("INSERT INTO cohorts (name, start_date, end_date) VALUES (:name, :start_date, :end_date)");
            $stmt->execute([
                ':name' => $cohort->getName(),
                ':start_date' => $cohort->getStartDate()->format('Y-m-d'),
                ':end_date' => $cohort->getEndDate()->format('Y-m-d')
            ]);
            $id = (int) $this->db->lastInsertId();
            $this->logger->info('Cohort created', ['id' => $id]);
            return $id;
        } catch (\PDOException $e) {
            $this->logger->error('Error creating cohort: ' . $e->getMessage(), ['name' => $cohort->getName()]);
            throw $e;
        }
    }

    /**
     * Update an existing cohort.
     *
     * @param Cohort $cohort The Cohort object to update
     * @return bool True if the update was successful, false otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function updateCohort(Cohort $cohort): bool
    {
        $this->logger->info('Updating cohort', ['id' => $cohort->getId()]);
        try {
            $stmt = $this->db->prepare("UPDATE cohorts SET name = :name, start_date = :start_date, end_date = :end_date WHERE id = :id");
            $success = $stmt->execute([
                ':id' => $cohort->getId(),
                ':name' => $cohort->getName(),
                ':start_date' => $cohort->getStartDate()->format('Y-m-d'),
                ':end_date' => $cohort->getEndDate()->format('Y-m-d')
            ]);
            if ($success) {
                $this->logger->info('Cohort updated successfully', ['id' => $cohort->getId()]);
            } else {
                $this->logger->warning('Failed to update cohort', ['id' => $cohort->getId()]);
            }
            return $success;
        } catch (\PDOException $e) {
            $this->logger->error('Error updating cohort: ' . $e->getMessage(), ['id' => $cohort->getId()]);
            throw $e;
        }
    }

    /**
     * Delete a cohort by its ID.
     *
     * @param int $id The ID of the cohort to delete
     * @return bool True if the deletion was successful, false otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function deleteCohort(int $id): bool
    {
        $this->logger->info('Deleting cohort', ['id' => $id]);
        try {
            $stmt = $this->db->prepare("DELETE FROM cohorts WHERE id = :id");
            $success = $stmt->execute([':id' => $id]);
            if ($success) {
                $this->logger->info('Cohort deleted successfully', ['id' => $id]);
            } else {
                $this->logger->warning('Failed to delete cohort', ['id' => $id]);
            }
            return $success;
        } catch (\PDOException $e) {
            $this->logger->error('Error deleting cohort: ' . $e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }
}
