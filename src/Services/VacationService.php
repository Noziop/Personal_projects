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

class VacationService
{
    /**
     * @var PDO The database connection
     */
    private $db;

    /**
     * VacationService constructor.
     *
     * @param PDO $db The database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all vacations.
     *
     * @return array An array of Vacation objects
     */
    public function getAllVacations(): array
    {
        $stmt = $this->db->query("SELECT * FROM vacations ORDER BY start_date");
        return $stmt->fetchAll(PDO::FETCH_CLASS, Vacation::class);
    }

    /**
     * Get a vacation by its ID.
     *
     * @param int $id The ID of the vacation
     * @return Vacation|null The Vacation object if found, null otherwise
     */
    public function getVacationById(int $id): ?Vacation
    {
        $stmt = $this->db->prepare("SELECT * FROM vacations WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $vacation = $stmt->fetchObject(Vacation::class);
        return $vacation ?: null;
    }

    /**
     * Create a new vacation.
     *
     * @param Vacation $vacation The Vacation object to create
     * @return int The ID of the newly created vacation
     */
    public function createVacation(Vacation $vacation): int
    {
        $stmt = $this->db->prepare("INSERT INTO vacations (cohort_id, name, start_date, end_date) VALUES (:cohort_id, :name, :start_date, :end_date)");
        $stmt->execute([
            ':cohort_id' => $vacation->getCohortId(),
            ':name' => $vacation->getName(),
            ':start_date' => $vacation->getStartDate()->format('Y-m-d'),
            ':end_date' => $vacation->getEndDate()->format('Y-m-d')
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing vacation.
     *
     * @param Vacation $vacation The Vacation object to update
     * @return bool True if the update was successful, false otherwise
     */
    public function updateVacation(Vacation $vacation): bool
    {
        $stmt = $this->db->prepare("UPDATE vacations SET cohort_id = :cohort_id, name = :name, start_date = :start_date, end_date = :end_date WHERE id = :id");
        return $stmt->execute([
            ':id' => $vacation->getId(),
            ':cohort_id' => $vacation->getCohortId(),
            ':name' => $vacation->getName(),
            ':start_date' => $vacation->getStartDate()->format('Y-m-d'),
            ':end_date' => $vacation->getEndDate()->format('Y-m-d')
        ]);
    }

    /**
     * Delete a vacation by its ID.
     *
     * @param int $id The ID of the vacation to delete
     * @return bool True if the deletion was successful, false otherwise
     */
    public function deleteVacation(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM vacations WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get all vacations for a specific cohort.
     *
     * @param int $cohortId The ID of the cohort
     * @return array An array of Vacation objects
     */
    public function getVacationsByCohort(int $cohortId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM vacations WHERE cohort_id = :cohort_id ORDER BY start_date");
        $stmt->execute([':cohort_id' => $cohortId]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, Vacation::class);
    }

    /**
     * Get all vacations for a specific date range.
     *
     * @param \DateTime $startDate The start date of the range
     * @param \DateTime $endDate The end date of the range
     * @return array An array of Vacation objects
     */
    public function getVacationsByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        $stmt = $this->db->prepare("SELECT * FROM vacations WHERE start_date <= :end_date AND end_date >= :start_date ORDER BY start_date");
        $stmt->execute([
            ':start_date' => $startDate->format('Y-m-d'),
            ':end_date' => $endDate->format('Y-m-d')
        ]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, Vacation::class);
    }

    /**
     * Check if a given date is within any vacation period for a specific cohort.
     *
     * @param int $cohortId The ID of the cohort
     * @param \DateTime $date The date to check
     * @return bool True if the date is within a vacation period, false otherwise
     */
    public function isDateInVacation(int $cohortId, \DateTime $date): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM vacations WHERE cohort_id = :cohort_id AND :date BETWEEN start_date AND end_date");
        $stmt->execute([
            ':cohort_id' => $cohortId,
            ':date' => $date->format('Y-m-d')
        ]);
        return $stmt->fetchColumn() > 0;
    }
}
