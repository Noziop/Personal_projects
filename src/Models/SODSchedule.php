<?php

namespace App\Models;

use PDO;
use DateTime;

class SODSchedule
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM sod_schedule WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($studentId, DateTime $date)
    {
        $stmt = $this->db->prepare("INSERT INTO sod_schedule (student_id, date) VALUES (:student_id, :date)");
        return $stmt->execute([
            'student_id' => $studentId,
            'date' => $date->format('Y-m-d')
        ]);
    }

    public function update($id, $studentId, DateTime $date)
    {
        $stmt = $this->db->prepare("UPDATE sod_schedule SET student_id = :student_id, date = :date WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'student_id' => $studentId,
            'date' => $date->format('Y-m-d')
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM sod_schedule WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function findAll()
    {
        $stmt = $this->db->query("SELECT * FROM sod_schedule ORDER BY date");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByStudentId($studentId)
    {
        $stmt = $this->db->prepare("SELECT * FROM sod_schedule WHERE student_id = :student_id ORDER BY date");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByDate(DateTime $date)
    {
        $stmt = $this->db->prepare("SELECT * FROM sod_schedule WHERE date = :date");
        $stmt->execute(['date' => $date->format('Y-m-d')]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByDateRange(DateTime $startDate, DateTime $endDate)
    {
        $stmt = $this->db->prepare("SELECT * FROM sod_schedule WHERE date BETWEEN :start_date AND :end_date ORDER BY date");
        $stmt->execute([
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNextScheduledStudent(DateTime $fromDate)
    {
        $stmt = $this->db->prepare("
            SELECT s.*, u.first_name, u.last_name 
            FROM sod_schedule s
            JOIN students st ON s.student_id = st.id
            JOIN users u ON st.user_id = u.id
            WHERE s.date >= :from_date 
            ORDER BY s.date 
            LIMIT 1
        ");
        $stmt->execute(['from_date' => $fromDate->format('Y-m-d')]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isStudentScheduledOnDate($studentId, DateTime $date)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sod_schedule WHERE student_id = :student_id AND date = :date");
        $stmt->execute([
            'student_id' => $studentId,
            'date' => $date->format('Y-m-d')
        ]);
        return $stmt->fetchColumn() > 0;
    }

	public function findNextForStudent($studentId, DateTime $fromDate)
	{
		$sql = "SELECT * FROM sod_schedule 
				WHERE student_id = :student_id 
				AND date >= :from_date 
				ORDER BY date ASC 
				LIMIT 1";
		
		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			'student_id' => $studentId,
			'from_date' => $fromDate->format('Y-m-d')
		]);

		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
}
