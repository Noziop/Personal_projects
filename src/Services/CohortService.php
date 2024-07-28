<?php

/**
 * CohortService
 *
 * This service is responsible for managing cohorts in the SOD (Speaker of the Day) application.
 */

namespace App\Services;

use App\Models\Cohort;
use PDO;

class CohortService
{
    /**
     * @var PDO The database connection
     */
    private $db;

    /**
     * CohortService constructor.
     *
     * @param PDO $db The database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all cohorts.
     *
     * @return array An array of Cohort objects
     */
    public function getAllCohorts(): array
    {
        $stmt = $this->db->query("SELECT * FROM cohorts");
        return $stmt->fetchAll(PDO::FETCH_CLASS, Cohort::class);
    }

    /**
     * Get a cohort by its ID.
     *
     * @param int $id The ID of the cohort
     * @return Cohort|null The Cohort object if found, null otherwise
     */
    public function getCohortById(int $id): ?Cohort
    {
        $stmt = $this->db->prepare("SELECT * FROM cohorts WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $cohort = $stmt->fetchObject(Cohort::class);
        return $cohort ?: null;
    }

    /**
     * Create a new cohort.
     *
     * @param Cohort $cohort The Cohort object to create
     * @return int The ID of the newly created cohort
     */
    public function createCohort(Cohort $cohort): int
    {
        $stmt = $this->db->prepare("INSERT INTO cohorts (name, start_date, end_date) VALUES (:name, :start_date, :end_date)");
        $stmt->execute([
            ':name' => $cohort->getName(),
            ':start_date' => $cohort->getStartDate()->format('Y-m-d'),
            ':end_date' => $cohort->getEndDate()->format('Y-m-d')
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing cohort.
     *
     * @param Cohort $cohort The Cohort object to update
     * @return bool True if the update was successful, false otherwise
     */
    public function updateCohort(Cohort $cohort): bool
    {
        $stmt = $this->db->prepare("UPDATE cohorts SET name = :name, start_date = :start_date, end_date = :end_date WHERE id = :id");
        return $stmt->execute([
            ':id' => $cohort->getId(),
            ':name' => $cohort->getName(),
            ':start_date' => $cohort->getStartDate()->format('Y-m-d'),
            ':end_date' => $cohort->getEndDate()->format('Y-m-d')
        ]);
    }

    /**
     * Delete a cohort by its ID.
     *
     * @param int $id The ID of the cohort to delete
     * @return bool True if the deletion was successful, false otherwise
     */
    public function deleteCohort(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM cohorts WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
