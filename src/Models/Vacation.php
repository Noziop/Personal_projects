<?php

namespace App\Models;

use PDO;
use DateTime;

class Vacation
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll()
    {
        $stmt = $this->db->query("
            SELECT v.*, c.name as cohort_name 
            FROM vacations v
            LEFT JOIN cohorts c ON v.cohort_id = c.id
            ORDER BY v.start_date
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT v.*, c.name as cohort_name 
            FROM vacations v
            LEFT JOIN cohorts c ON v.cohort_id = c.id
            WHERE v.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($cohortId, $startDate, $endDate)
    {
        $stmt = $this->db->prepare("
            INSERT INTO vacations (cohort_id, start_date, end_date) 
            VALUES (:cohort_id, :start_date, :end_date)
        ");
        return $stmt->execute([
            'cohort_id' => $cohortId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    public function update($id, $cohortId, $startDate, $endDate)
    {
        $stmt = $this->db->prepare("
            UPDATE vacations 
            SET cohort_id = :cohort_id, start_date = :start_date, end_date = :end_date 
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'cohort_id' => $cohortId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM vacations WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

	public function findUpcomingByCohortId($cohortId, DateTime $currentDate)
	{
		$sql = "SELECT * FROM vacations 
				WHERE cohort_id = :cohort_id 
				AND end_date >= :current_date 
				ORDER BY start_date ASC";
		
		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			'cohort_id' => $cohortId,
			'current_date' => $currentDate->format('Y-m-d')
		]);
	
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}