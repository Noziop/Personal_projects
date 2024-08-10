<?php

namespace App\Models;

use PDO;
use DateTime;

/**
 * Cohort Model
 *
 * This class represents a cohort in the application.
 */
class Cohort
{
    private $id;
    private $name;
    private $startDate;
    private $endDate;
    private $createdAt;
    private $db;
	private $drawingDays;

    /**
     * Cohort constructor.
     *
     * @param PDO $db The database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a cohort by its ID
     *
     * @param int $id The cohort ID
     * @return Cohort|null The cohort object if found, null otherwise
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM cohorts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $cohortData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $cohortData ? $this->hydrate($cohortData) : null;
    }

    /**
     * Find a cohort by its name
     *
     * @param string $name The cohort name
     * @return Cohort|null The cohort object if found, null otherwise
     */
    public function findByName($name)
    {
        $stmt = $this->db->prepare("SELECT * FROM cohorts WHERE name = :name");
        $stmt->execute(['name' => $name]);
        $cohortData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $cohortData ? $this->hydrate($cohortData) : null;
    }

    /**
     * Get all cohorts
     *
     * @return array An array of Cohort objects
     */
    public function findAll()
    {
        $stmt = $this->db->query("SELECT * FROM cohorts ORDER BY start_date DESC");
        $cohortsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cohorts = [];
        foreach ($cohortsData as $cohortData) {
            $cohorts[] = $this->hydrate($cohortData);
        }

        return $cohorts;
    }

    /**
     * Get current cohorts
     *
     * @return array An array of current Cohort objects
     */
    public function findCurrent()
    {
        $currentDate = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT * FROM cohorts WHERE :current_date BETWEEN start_date AND end_date ORDER BY start_date");
        $stmt->execute(['current_date' => $currentDate]);
        $cohortsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cohorts = [];
        foreach ($cohortsData as $cohortData) {
            $cohorts[] = $this->hydrate($cohortData);
        }

        return $cohorts;
    }

    /**
     * Get future cohorts
     *
     * @return array An array of future Cohort objects
     */
    public function findFuture()
    {
        $currentDate = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT * FROM cohorts WHERE start_date > :current_date ORDER BY start_date");
        $stmt->execute(['current_date' => $currentDate]);
        $cohortsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cohorts = [];
        foreach ($cohortsData as $cohortData) {
            $cohorts[] = $this->hydrate($cohortData);
        }

        return $cohorts;
    }

    /**
     * Get past cohorts
     *
     * @return array An array of past Cohort objects
     */
    public function findPast()
    {
        $currentDate = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT * FROM cohorts WHERE end_date < :current_date ORDER BY start_date DESC");
        $stmt->execute(['current_date' => $currentDate]);
        $cohortsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cohorts = [];
        foreach ($cohortsData as $cohortData) {
            $cohorts[] = $this->hydrate($cohortData);
        }

        return $cohorts;
    }

    /**
     * Create a new cohort
     *
     * @param string $name The cohort name
     * @param DateTime $startDate The start date
     * @param DateTime $endDate The end date
     * @return bool True if the cohort was created successfully, false otherwise
     */
	public function create($name, $startDate, $endDate)
	{
		$stmt = $this->db->prepare("INSERT INTO cohorts (name, start_date, end_date) VALUES (:name, :start_date, :end_date)");
		$result = $stmt->execute([
			'name' => $name,
			'start_date' => $startDate,
			'end_date' => $endDate
		]);
	
		if ($result) {
			return $this->db->lastInsertId();
		}
	
		return false;
	}

    /**
     * Update an existing cohort
     *
     * @param int $id The cohort ID
     * @param string $name The cohort name
     * @param DateTime $startDate The start date
     * @param DateTime $endDate The end date
     * @return bool True if the cohort was updated successfully, false otherwise
     */
    public function update($id, $name, DateTime $startDate, DateTime $endDate)
    {
        $stmt = $this->db->prepare("UPDATE cohorts SET name = :name, start_date = :start_date, end_date = :end_date WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'name' => $name,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);
    }

    /**
     * Delete the cohort
     *
     * @param int $id The cohort ID
     * @return bool True if the cohort was deleted successfully, false otherwise
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM cohorts WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Get students in this cohort
     *
     * @param int $cohortId The cohort ID
     * @return array An array of Student objects
     */
    public function getStudents($cohortId)
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE cohort_id = :cohort_id");
        $stmt->execute(['cohort_id' => $cohortId]);
        $studentsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $students = [];
        foreach ($studentsData as $studentData) {
            $students[] = (new Student($this->db))->hydrate($studentData);
        }

        return $students;
    }

    /**
     * Add a student to this cohort
     *
     * @param int $cohortId The cohort ID
     * @param int $studentId The student ID
     * @return bool True if the student was added successfully, false otherwise
     */
    public function addStudent($cohortId, $studentId)
    {
        $stmt = $this->db->prepare("UPDATE students SET cohort_id = :cohort_id WHERE id = :student_id");
        return $stmt->execute([
            'cohort_id' => $cohortId,
            'student_id' => $studentId
        ]);
    }

    /**
     * Remove a student from this cohort
     *
     * @param int $cohortId The cohort ID
     * @param int $studentId The student ID
     * @return bool True if the student was removed successfully, false otherwise
     */
    public function removeStudent($cohortId, $studentId)
    {
        $stmt = $this->db->prepare("UPDATE students SET cohort_id = NULL WHERE id = :student_id AND cohort_id = :cohort_id");
        return $stmt->execute([
            'cohort_id' => $cohortId,
            'student_id' => $studentId
        ]);
    }

    /**
     * Get the drawing days for this cohort.
     *
     * @return array
     */
    public function getDrawingDays(): array
    {
        return $this->drawingDays ?? [];
    }

	/**
     * Set the drawing days for this cohort.
     *
     * @param array $drawingDays
     * @return void
     */
    public function setDrawingDays(array $drawingDays): void
    {
        $this->drawingDays = $drawingDays;
    }

	public function isDrawingDay($day)
	{
		return in_array($day, array_column($this->drawingDays, 'day'));
	}

    /**
     * Hydrate the cohort object with data
     *
     * @param array $data The data to hydrate the object with
     * @return Cohort The hydrated cohort object
     */
	private function hydrate($data)
	{
		$cohort = new Cohort($this->db);
		$cohort->id = $data['id'];
		$cohort->name = $data['name'];
		$cohort->startDate = new DateTime($data['start_date']);
		$cohort->endDate = new DateTime($data['end_date']);
		$cohort->createdAt = $data['created_at'] ?? null;
		return $cohort;
	}
	

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getStartDate() { return $this->startDate; }
    public function getEndDate() { return $this->endDate; }
    public function getCreatedAt() { return $this->createdAt; }
}
