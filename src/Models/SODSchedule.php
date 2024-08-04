<?php

namespace App\Models;

use PDO;

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

    public function create($studentId, $date)
    {
        $stmt = $this->db->prepare("INSERT INTO sod_schedule (student_id, date) VALUES (:student_id, :date)");
        return $stmt->execute([
            'student_id' => $studentId,
            'date' => $date
        ]);
    }

    public function update($id, $studentId, $date)
    {
        $stmt = $this->db->prepare("UPDATE sod_schedule SET student_id = :student_id, date = :date WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'student_id' => $studentId,
            'date' => $date
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM sod_schedule WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function findAll()
    {
        $stmt = $this->db->query("SELECT * FROM sod_schedule");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByStudentId($studentId)
    {
        $stmt = $this->db->prepare("SELECT * FROM sod_schedule WHERE student_id = :student_id");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByDate($date)
    {
        $stmt = $this->db->prepare("SELECT * FROM sod_schedule WHERE date = :date");
        $stmt->execute(['date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
