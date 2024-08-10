<?php

/**
 * DrawingDay Model
 *
 * This class represents a drawing day in the SOD (Speaker of the Day) application.
 * It defines the days when drawings can occur for a specific cohort.
 */

namespace App\Models;

use PDO;

class DrawingDay
{
    /**
     * @var int The unique identifier for the drawing day
     */
    private $id;

    /**
     * @var int The ID of the cohort this drawing day is associated with
     */
    private $cohortId;

    /**
     * @var string The day of the week (e.g., 'monday', 'tuesday', etc.)
     */
    private $day;

    /**
     * @var PDO The database connection
     */
    private $db;

    /**
     * DrawingDay constructor.
     *
     * @param PDO $db The database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find all drawing days for a specific cohort.
     *
     * @param int $cohortId The ID of the cohort
     * @return array An array of DrawingDay objects
     */
	public function findByCohort($cohortId)
	{
		$stmt = $this->db->prepare("SELECT * FROM drawing_days WHERE cohort_id = :cohort_id");
		$stmt->execute(['cohort_id' => $cohortId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

    /**
     * Create a new drawing day.
     *
     * @param int $cohortId The ID of the cohort
     * @param string $day The day of the week
     * @return bool True if the creation was successful, false otherwise
     */
    public function create(int $cohortId, string $day): bool
    {
        $stmt = $this->db->prepare("INSERT INTO drawing_days (cohort_id, day) VALUES (:cohort_id, :day)");
        return $stmt->execute([
            'cohort_id' => $cohortId,
            'day' => $day
        ]);
    }

    /**
     * Update an existing drawing day.
     *
     * @param int $id The ID of the drawing day to update
     * @param string $day The new day of the week
     * @return bool True if the update was successful, false otherwise
     */
    public function update(int $id, string $day): bool
    {
        $stmt = $this->db->prepare("UPDATE drawing_days SET day = :day WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'day' => $day
        ]);
    }

    /**
     * Delete a drawing day.
     *
     * @param int $id The ID of the drawing day to delete
     * @return bool True if the deletion was successful, false otherwise
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM drawing_days WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

	public function deleteByCohort(int $cohortId): bool
{
    $stmt = $this->db->prepare("DELETE FROM drawing_days WHERE cohort_id = :cohort_id");
    return $stmt->execute(['cohort_id' => $cohortId]);
}

    /**
     * Hydrate a DrawingDay object from an array of data.
     *
     * @param array $data The data to hydrate the object with
     * @return DrawingDay
     */
    private function hydrate(array $data): DrawingDay
    {
        $this->id = $data['id'];
        $this->cohortId = $data['cohort_id'];
        $this->day = $data['day'];
        return $this;
    }

    // Getters and setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCohortId(): int
    {
        return $this->cohortId;
    }

    public function getDay(): string
    {
        return $this->day;
    }

    public function setDay(string $day): void
    {
        $this->day = $day;
    }
}