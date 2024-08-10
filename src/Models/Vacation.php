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
     * Get all vacations
     *
     * @return array An array of Vacation objects
     */
	public function findAll()
	{
		$stmt = $this->db->query("
			SELECT v.*, c.name as cohort_name 
			FROM vacations v
			LEFT JOIN cohorts c ON v.cohort_id = c.id
			ORDER BY v.start_date
		");
		$vacationsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
		$vacations = [];
		foreach ($vacationsData as $vacationData) {
			$vacations[] = $this->hydrate($vacationData);
		}
	
		return $vacations;
	}
	
	

    /**
     * Get all vacations for a specific cohort
     *
     * @param int $cohortId The cohort ID
     * @return array An array of Vacation objects
     */
    public function findByCohort($cohortId)
    {
        $stmt = $this->db->prepare("SELECT * FROM vacations WHERE cohort_id = :cohort_id ORDER BY start_date");
        $stmt->execute(['cohort_id' => $cohortId]);
        $vacationsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $vacations = [];
        foreach ($vacationsData as $vacationData) {
            $vacations[] = $this->hydrate($vacationData);
        }

        return $vacations;
    }

    /**
     * Find vacations by date range
     *
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return array An array of Vacation objects
     */
    public function findByDateRange(DateTime $startDate, DateTime $endDate)
    {
        $stmt = $this->db->prepare("SELECT * FROM vacations WHERE 
            (start_date BETWEEN :start_date AND :end_date) OR
            (end_date BETWEEN :start_date AND :end_date) OR
            (start_date <= :start_date AND end_date >= :end_date)
            ORDER BY start_date");
        $stmt->execute([
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);
        $vacationsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $vacations = [];
        foreach ($vacationsData as $vacationData) {
            $vacations[] = $this->hydrate($vacationData);
        }

        return $vacations;
    }

    /**
     * Create a new vacation
     *
     * @param int $cohortId
     * @param string $name
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return bool True if the vacation was created successfully, false otherwise
     */
    public function create($cohortId, $name, DateTime $startDate, DateTime $endDate)
    {
        $stmt = $this->db->prepare("INSERT INTO vacations (cohort_id, name, start_date, end_date) VALUES (:cohort_id, :name, :start_date, :end_date)");
        return $stmt->execute([
            'cohort_id' => $cohortId,
            'name' => $name,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);
    }

    /**
     * Update an existing vacation
     *
     * @param int $id
     * @param int $cohortId
     * @param string $name
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return bool True if the vacation was updated successfully, false otherwise
     */
    public function update($id, $cohortId, $name, DateTime $startDate, DateTime $endDate)
    {
        $stmt = $this->db->prepare("UPDATE vacations SET cohort_id = :cohort_id, name = :name, start_date = :start_date, end_date = :end_date WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'cohort_id' => $cohortId,
            'name' => $name,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);
    }

    /**
     * Delete the vacation
     *
     * @param int $id
     * @return bool True if the vacation was deleted successfully, false otherwise
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM vacations WHERE id = :id");
        return $stmt->execute(['id' => $id]);
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
     * Find the next vacation period for a cohort after a given date
     *
     * @param int $cohortId
     * @param DateTime $fromDate
     * @return Vacation|null The next vacation period if found, null otherwise
     */
    public function findNextVacation($cohortId, DateTime $fromDate)
    {
        $stmt = $this->db->prepare("SELECT * FROM vacations WHERE cohort_id = :cohort_id AND start_date > :from_date ORDER BY start_date LIMIT 1");
        $stmt->execute([
            'cohort_id' => $cohortId,
            'from_date' => $fromDate->format('Y-m-d')
        ]);
        $vacationData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vacationData) {
            return $this->hydrate($vacationData);
        }

        return null;
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
		$this->cohortName = $data['cohort_name'];
		$this->startDate = new DateTime($data['start_date']);
		$this->endDate = new DateTime($data['end_date']);
		$this->createdAt = isset($data['created_at']) ? new DateTime($data['created_at']) : null;
	
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
