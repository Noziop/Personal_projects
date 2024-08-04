<?php

namespace App\Models;

use PDO;
use DateTime;

/**
 * Vacation Model
 *
 * This class represents a vacation period for a cohort in the application.
 */
class Vacation
{
    private $id;
    private $cohortId;
    private $name;
    private $startDate;
    private $endDate;
    private $createdAt;

    private $db;

    /**
     * Vacation constructor.
     *
     * @param PDO $db The database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a vacation by its ID
     *
     * @param int $id The vacation ID
     * @return Vacation|null The vacation object if found, null otherwise
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM vacations WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $vacationData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vacationData) {
            return $this->hydrate($vacationData);
        }

        return null;
    }

    /**
     * Get all vacations for a specific cohort
     *
     * @param int $cohortId The cohort ID
     * @return array An array of Vacation objects
     */
    public function getAllForCohort($cohortId)
    {
        $stmt = $this->db->prepare("SELECT * FROM vacations WHERE cohort_id = :cohort_id ORDER BY start_date");
        $stmt->execute(['cohort_id' => $cohortId]);
        $vacationsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $vacations = [];
        foreach ($vacationsData as $vacationData) {
            $vacations[] = (new Vacation($this->db))->hydrate($vacationData);
        }

        return $vacations;
    }

    /**
     * Create a new vacation
     *
     * @param array $data The vacation data
     * @return bool True if the vacation was created successfully, false otherwise
     */
    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO vacations (cohort_id, name, start_date, end_date) VALUES (:cohort_id, :name, :start_date, :end_date)");
        return $stmt->execute([
            'cohort_id' => $data['cohort_id'],
            'name' => $data['name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date']
        ]);
    }

    /**
     * Update an existing vacation
     *
     * @param array $data The vacation data to update
     * @return bool True if the vacation was updated successfully, false otherwise
     */
    public function update($data)
    {
        $stmt = $this->db->prepare("UPDATE vacations SET name = :name, start_date = :start_date, end_date = :end_date WHERE id = :id");
        return $stmt->execute([
            'id' => $this->id,
            'name' => $data['name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date']
        ]);
    }

    /**
     * Delete the vacation
     *
     * @return bool True if the vacation was deleted successfully, false otherwise
     */
    public function delete()
    {
        $stmt = $this->db->prepare("DELETE FROM vacations WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }

    /**
     * Check if a date is within a vacation period for a specific cohort
     *
     * @param int $cohortId The cohort ID
     * @param DateTime $date The date to check
     * @return bool True if the date is within a vacation period, false otherwise
     */
    public function isDateInVacation($cohortId, DateTime $date)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM vacations WHERE cohort_id = :cohort_id AND :date BETWEEN start_date AND end_date");
        $stmt->execute([
            'cohort_id' => $cohortId,
            'date' => $date->format('Y-m-d')
        ]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get the cohort associated with this vacation
     *
     * @return Cohort|null The Cohort object if found, null otherwise
     */
    public function getCohort()
    {
        return (new Cohort($this->db))->findById($this->cohortId);
    }

    /**
     * Hydrate the vacation object with data
     *
     * @param array $data The data to hydrate the object with
     * @return Vacation The hydrated vacation object
     */
    private function hydrate($data)
    {
        $this->id = $data['id'];
        $this->cohortId = $data['cohort_id'];
        $this->name = $data['name'];
        $this->startDate = new DateTime($data['start_date']);
        $this->endDate = new DateTime($data['end_date']);
        $this->createdAt = new DateTime($data['created_at']);

        return $this;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getCohortId() { return $this->cohortId; }
    public function getName() { return $this->name; }
    public function getStartDate() { return $this->startDate; }
    public function getEndDate() { return $this->endDate; }
    public function getCreatedAt() { return $this->createdAt; }
}