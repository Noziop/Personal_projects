<?php

/**
 * ConstraintService
 *
 * This service is responsible for managing constraints in the SOD (Speaker of the Day) application.
 * Constraints can include public holidays, vacations, and other periods when drawings should not occur.
 */

namespace App\Services;

use App\Models\Constraint;
use PDO;
use Psr\Log\LoggerInterface;

class ConstraintService
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
     * ConstraintService constructor.
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
     * Get all constraints.
     *
     * @return array An array of Constraint objects
     * @throws \PDOException If there's an error executing the query
     */
    public function getAllConstraints(): array
    {
        $this->logger->info('Fetching all constraints');
        try {
            $stmt = $this->db->query("SELECT * FROM constraints");
            $constraints = $stmt->fetchAll(PDO::FETCH_CLASS, Constraint::class);
            $this->logger->info('Fetched ' . count($constraints) . ' constraints');
            return $constraints;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching all constraints: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a constraint by its ID.
     *
     * @param int $id The ID of the constraint
     * @return Constraint|null The Constraint object if found, null otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function getConstraintById(int $id): ?Constraint
    {
        $this->logger->info('Fetching constraint with id: ' . $id);
        try {
            $stmt = $this->db->prepare("SELECT * FROM constraints WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $constraint = $stmt->fetchObject(Constraint::class);
            if ($constraint) {
                $this->logger->info('Constraint found', ['id' => $id]);
            } else {
                $this->logger->warning('Constraint not found', ['id' => $id]);
            }
            return $constraint ?: null;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching constraint: ' . $e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Create a new constraint.
     *
     * @param Constraint $constraint The Constraint object to create
     * @return int The ID of the newly created constraint
     * @throws \PDOException If there's an error executing the query
     */
    public function createConstraint(Constraint $constraint): int
    {
        $this->logger->info('Creating new constraint', ['type' => $constraint->getType()]);
        try {
            $stmt = $this->db->prepare("INSERT INTO constraints (cohort_id, type, start_date, end_date, description) VALUES (:cohort_id, :type, :start_date, :end_date, :description)");
            $stmt->execute([
                ':cohort_id' => $constraint->getCohortId(),
                ':type' => $constraint->getType(),
                ':start_date' => $constraint->getStartDate()->format('Y-m-d'),
                ':end_date' => $constraint->getEndDate()->format('Y-m-d'),
                ':description' => $constraint->getDescription()
            ]);
            $id = (int) $this->db->lastInsertId();
            $this->logger->info('Constraint created', ['id' => $id]);
            return $id;
        } catch (\PDOException $e) {
            $this->logger->error('Error creating constraint: ' . $e->getMessage(), ['type' => $constraint->getType()]);
            throw $e;
        }
    }

    /**
     * Update an existing constraint.
     *
     * @param Constraint $constraint The Constraint object to update
     * @return bool True if the update was successful, false otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function updateConstraint(Constraint $constraint): bool
    {
        $this->logger->info('Updating constraint', ['id' => $constraint->getId()]);
        try {
            $stmt = $this->db->prepare("UPDATE constraints SET cohort_id = :cohort_id, type = :type, start_date = :start_date, end_date = :end_date, description = :description WHERE id = :id");
            $success = $stmt->execute([
                ':id' => $constraint->getId(),
                ':cohort_id' => $constraint->getCohortId(),
                ':type' => $constraint->getType(),
                ':start_date' => $constraint->getStartDate()->format('Y-m-d'),
                ':end_date' => $constraint->getEndDate()->format('Y-m-d'),
                ':description' => $constraint->getDescription()
            ]);
            if ($success) {
                $this->logger->info('Constraint updated successfully', ['id' => $constraint->getId()]);
            } else {
                $this->logger->warning('Failed to update constraint', ['id' => $constraint->getId()]);
            }
            return $success;
        } catch (\PDOException $e) {
            $this->logger->error('Error updating constraint: ' . $e->getMessage(), ['id' => $constraint->getId()]);
            throw $e;
        }
    }

    /**
     * Delete a constraint by its ID.
     *
     * @param int $id The ID of the constraint to delete
     * @return bool True if the deletion was successful, false otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function deleteConstraint(int $id): bool
    {
        $this->logger->info('Deleting constraint', ['id' => $id]);
        try {
            $stmt = $this->db->prepare("DELETE FROM constraints WHERE id = :id");
            $success = $stmt->execute([':id' => $id]);
            if ($success) {
                $this->logger->info('Constraint deleted successfully', ['id' => $id]);
            } else {
                $this->logger->warning('Failed to delete constraint', ['id' => $id]);
            }
            return $success;
        } catch (\PDOException $e) {
            $this->logger->error('Error deleting constraint: ' . $e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Get all constraints for a specific cohort.
     *
     * @param int $cohortId The ID of the cohort
     * @return array An array of Constraint objects
     * @throws \PDOException If there's an error executing the query
     */
    public function getConstraintsByCohort(int $cohortId): array
    {
        $this->logger->info('Fetching constraints for cohort', ['cohort_id' => $cohortId]);
        try {
            $stmt = $this->db->prepare("SELECT * FROM constraints WHERE cohort_id = :cohort_id");
            $stmt->execute([':cohort_id' => $cohortId]);
            $constraints = $stmt->fetchAll(PDO::FETCH_CLASS, Constraint::class);
            $this->logger->info('Fetched ' . count($constraints) . ' constraints for cohort', ['cohort_id' => $cohortId]);
            return $constraints;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching constraints for cohort: ' . $e->getMessage(), ['cohort_id' => $cohortId]);
            throw $e;
        }
    }

    /**
     * Get all constraints for a specific date range.
     *
     * @param \DateTime $startDate The start date of the range
     * @param \DateTime $endDate The end date of the range
     * @return array An array of Constraint objects
     * @throws \PDOException If there's an error executing the query
     */
    public function getConstraintsByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        $this->logger->info('Fetching constraints for date range', [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);
        try {
            $stmt = $this->db->prepare("SELECT * FROM constraints WHERE start_date <= :end_date AND end_date >= :start_date");
            $stmt->execute([
                ':start_date' => $startDate->format('Y-m-d'),
                ':end_date' => $endDate->format('Y-m-d')
            ]);
            $constraints = $stmt->fetchAll(PDO::FETCH_CLASS, Constraint::class);
            $this->logger->info('Fetched ' . count($constraints) . ' constraints for date range');
            return $constraints;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching constraints for date range: ' . $e->getMessage(), [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ]);
            throw $e;
        }
    }
}
