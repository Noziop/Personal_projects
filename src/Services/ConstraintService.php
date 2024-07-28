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

class ConstraintService
{
    /**
     * @var PDO The database connection
     */
    private $db;

    /**
     * ConstraintService constructor.
     *
     * @param PDO $db The database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all constraints.
     *
     * @return array An array of Constraint objects
     */
    public function getAllConstraints(): array
    {
        $stmt = $this->db->query("SELECT * FROM constraints");
        return $stmt->fetchAll(PDO::FETCH_CLASS, Constraint::class);
    }

    /**
     * Get a constraint by its ID.
     *
     * @param int $id The ID of the constraint
     * @return Constraint|null The Constraint object if found, null otherwise
     */
    public function getConstraintById(int $id): ?Constraint
    {
        $stmt = $this->db->prepare("SELECT * FROM constraints WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $constraint = $stmt->fetchObject(Constraint::class);
        return $constraint ?: null;
    }

    /**
     * Create a new constraint.
     *
     * @param Constraint $constraint The Constraint object to create
     * @return int The ID of the newly created constraint
     */
    public function createConstraint(Constraint $constraint): int
    {
        $stmt = $this->db->prepare("INSERT INTO constraints (cohort_id, type, start_date, end_date, description) VALUES (:cohort_id, :type, :start_date, :end_date, :description)");
        $stmt->execute([
            ':cohort_id' => $constraint->getCohortId(),
            ':type' => $constraint->getType(),
            ':start_date' => $constraint->getStartDate()->format('Y-m-d'),
            ':end_date' => $constraint->getEndDate()->format('Y-m-d'),
            ':description' => $constraint->getDescription()
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing constraint.
     *
     * @param Constraint $constraint The Constraint object to update
     * @return bool True if the update was successful, false otherwise
     */
    public function updateConstraint(Constraint $constraint): bool
    {
        $stmt = $this->db->prepare("UPDATE constraints SET cohort_id = :cohort_id, type = :type, start_date = :start_date, end_date = :end_date, description = :description WHERE id = :id");
        return $stmt->execute([
            ':id' => $constraint->getId(),
            ':cohort_id' => $constraint->getCohortId(),
            ':type' => $constraint->getType(),
            ':start_date' => $constraint->getStartDate()->format('Y-m-d'),
            ':end_date' => $constraint->getEndDate()->format('Y-m-d'),
            ':description' => $constraint->getDescription()
        ]);
    }

    /**
     * Delete a constraint by its ID.
     *
     * @param int $id The ID of the constraint to delete
     * @return bool True if the deletion was successful, false otherwise
     */
    public function deleteConstraint(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM constraints WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get all constraints for a specific cohort.
     *
     * @param int $cohortId The ID of the cohort
     * @return array An array of Constraint objects
     */
    public function getConstraintsByCohort(int $cohortId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM constraints WHERE cohort_id = :cohort_id");
        $stmt->execute([':cohort_id' => $cohortId]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, Constraint::class);
    }

    /**
     * Get all constraints for a specific date range.
     *
     * @param \DateTime $startDate The start date of the range
     * @param \DateTime $endDate The end date of the range
     * @return array An array of Constraint objects
     */
    public function getConstraintsByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        $stmt = $this->db->prepare("SELECT * FROM constraints WHERE start_date <= :end_date AND end_date >= :start_date");
        $stmt->execute([
            ':start_date' => $startDate->format('Y-m-d'),
            ':end_date' => $endDate->format('Y-m-d')
        ]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, Constraint::class);
    }
}
