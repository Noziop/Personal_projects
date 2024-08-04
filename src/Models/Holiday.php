<?php

namespace App\Models;

use PDO;
use DateTime;

/**
 * Holiday Model
 *
 * This class represents a public holiday in the application.
 */
class Holiday
{
    private $id;
    private $name;
    private $date;
    private $isRecurring;
    private $createdAt;

    private $db;

    /**
     * Holiday constructor.
     *
     * @param PDO $db The database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a holiday by its ID
     *
     * @param int $id The holiday ID
     * @return Holiday|null The holiday object if found, null otherwise
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM holidays WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $holidayData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($holidayData) {
            return $this->hydrate($holidayData);
        }

        return null;
    }

    /**
     * Get all holidays for a specific year
     *
     * @param int $year The year to get holidays for
     * @return array An array of Holiday objects
     */
    public function getAllForYear($year)
    {
        $stmt = $this->db->prepare("SELECT * FROM holidays WHERE YEAR(date) = :year OR is_recurring = 1 ORDER BY date");
        $stmt->execute(['year' => $year]);
        $holidaysData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $holidays = [];
        foreach ($holidaysData as $holidayData) {
            $holidays[] = (new Holiday($this->db))->hydrate($holidayData);
        }

        return $holidays;
    }

    /**
     * Create a new holiday
     *
     * @param array $data The holiday data
     * @return bool True if the holiday was created successfully, false otherwise
     */
    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO holidays (name, date, is_recurring) VALUES (:name, :date, :is_recurring)");
        return $stmt->execute([
            'name' => $data['name'],
            'date' => $data['date'],
            'is_recurring' => $data['is_recurring'] ? 1 : 0
        ]);
    }

    /**
     * Update an existing holiday
     *
     * @param array $data The holiday data to update
     * @return bool True if the holiday was updated successfully, false otherwise
     */
    public function update($data)
    {
        $stmt = $this->db->prepare("UPDATE holidays SET name = :name, date = :date, is_recurring = :is_recurring WHERE id = :id");
        return $stmt->execute([
            'id' => $this->id,
            'name' => $data['name'],
            'date' => $data['date'],
            'is_recurring' => $data['is_recurring'] ? 1 : 0
        ]);
    }

    /**
     * Delete the holiday
     *
     * @return bool True if the holiday was deleted successfully, false otherwise
     */
    public function delete()
    {
        $stmt = $this->db->prepare("DELETE FROM holidays WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }

    /**
     * Check if a date is a holiday
     *
     * @param DateTime $date The date to check
     * @return bool True if the date is a holiday, false otherwise
     */
    public function isHoliday(DateTime $date)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM holidays WHERE date = :date OR (MONTH(date) = :month AND DAY(date) = :day AND is_recurring = 1)");
        $stmt->execute([
            'date' => $date->format('Y-m-d'),
            'month' => $date->format('m'),
            'day' => $date->format('d')
        ]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Hydrate the holiday object with data
     *
     * @param array $data The data to hydrate the object with
     * @return Holiday The hydrated holiday object
     */
    private function hydrate($data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->date = new DateTime($data['date']);
        $this->isRecurring = (bool)$data['is_recurring'];
        $this->createdAt = new DateTime($data['created_at']);

        return $this;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getDate() { return $this->date; }
    public function isRecurring() { return $this->isRecurring; }
    public function getCreatedAt() { return $this->createdAt; }
}