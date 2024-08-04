<?php

namespace App\Models;

use PDO;

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

        if ($cohortData) {
            return $this->hydrate($cohortData);
        }

        return null;
    }

    /**
     * Get all cohorts
     *
     * @return array An array of Cohort objects
     */
    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM cohorts ORDER BY start_date DESC");
        $cohortsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cohorts = [];
        foreach ($cohortsData as $cohortData) {
            $cohorts[] = (new Cohort($this->db))->hydrate($cohortData);
        }

        return $cohorts;
    }

    /**
     * Create a new cohort
     *
     * @param array $data The cohort data
     * @return bool True if the cohort was created successfully, false otherwise
     */
    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO cohorts (name, start_date, end_date) VALUES (:name, :start_date, :end_date)");
        return $stmt->execute([
            'name' => $data['name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date']
        ]);
    }

    /**
     * Update an existing cohort
     *
     * @param array $data The cohort data to update
     * @return bool True if the cohort was updated successfully, false otherwise
     */
    public function update($data)
    {
        $stmt = $this->db->prepare("UPDATE cohorts SET name = :name, start_date = :start_date, end_date = :end_date WHERE id = :id");
        return $stmt->execute([
            'id' => $this->id,
            'name' => $data['name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date']
        ]);
    }

    /**
     * Delete the cohort
     *
     * @return bool True if the cohort was deleted successfully, false otherwise
     */
    public function delete()
    {
        $stmt = $this->db->prepare("DELETE FROM cohorts WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }

    /**
     * Get students in this cohort
     *
     * @return array An array of Student objects
     */
    public function getStudents()
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE cohort_id = :cohort_id");
        $stmt->execute(['cohort_id' => $this->id]);
        $studentsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $students = [];
        foreach ($studentsData as $studentData) {
            $students[] = (new Student($this->db))->hydrate($studentData);
        }

        return $students;
    }

    /**
     * Hydrate the cohort object with data
     *
     * @param array $data The data to hydrate the object with
     * @return Cohort The hydrated cohort object
     */
    private function hydrate($data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->startDate = $data['start_date'];
        $this->endDate = $data['end_date'];
        $this->createdAt = $data['created_at'];

        return $this;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getStartDate() { return $this->startDate; }
    public function getEndDate() { return $this->endDate; }
    public function getCreatedAt() { return $this->createdAt; }
}