<?php

namespace App\Models;

use PDO;
use DateTime;

class Holiday
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll()
    {
        $stmt = $this->db->query("SELECT * FROM public_holidays ORDER BY date");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM public_holidays WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($date, $description, $cohortId = null)
    {
        $stmt = $this->db->prepare("INSERT INTO public_holidays (date, description, cohort_id) VALUES (:date, :description, :cohort_id)");
        return $stmt->execute(['date' => $date, 'description' => $description, 'cohort_id' => $cohortId]);
    }

    public function update($id, $date, $description, $cohortId = null)
    {
        $stmt = $this->db->prepare("UPDATE public_holidays SET date = :date, description = :description, cohort_id = :cohort_id WHERE id = :id");
        return $stmt->execute(['id' => $id, 'date' => $date, 'description' => $description, 'cohort_id' => $cohortId]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM public_holidays WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function deleteOlderThan(DateTime $date)
    {
        $stmt = $this->db->prepare("DELETE FROM public_holidays WHERE date < :date");
        return $stmt->execute(['date' => $date->format('Y-m-d')]);
    }

    public function findByDate($date)
    {
        $stmt = $this->db->prepare("SELECT * FROM public_holidays WHERE date = :date");
        $stmt->execute(['date' => $date]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAllWithCohort()
    {
        $stmt = $this->db->query("
            SELECT ph.*, c.name as cohort_name 
            FROM public_holidays ph
            LEFT JOIN cohorts c ON ph.cohort_id = c.id
            ORDER BY ph.date
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

	public function isHoliday($date)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM public_holidays WHERE date = :date");
        $stmt->execute(['date' => $date]);
        return $stmt->fetchColumn() > 0;
    }
}
