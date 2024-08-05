<?php

namespace App\Models;

use PDO;
use DateTime;

class Report
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM reports WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($studentId, $type, $content)
    {
        $stmt = $this->db->prepare("INSERT INTO reports (student_id, type, content, created_at) VALUES (:student_id, :type, :content, NOW())");
        return $stmt->execute([
            'student_id' => $studentId,
            'type' => $type,
            'content' => $content
        ]);
    }

    public function update($id, $studentId, $type, $content)
    {
        $stmt = $this->db->prepare("UPDATE reports SET student_id = :student_id, type = :type, content = :content, updated_at = NOW() WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'student_id' => $studentId,
            'type' => $type,
            'content' => $content
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM reports WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function findAll()
    {
        $stmt = $this->db->query("SELECT * FROM reports ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByStudentId($studentId, $limit = null)
    {
        $query = "SELECT * FROM reports WHERE student_id = :student_id ORDER BY created_at DESC";
        if ($limit !== null) {
            $query .= " LIMIT :limit";
        }
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        if ($limit !== null) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByType($type)
    {
        $stmt = $this->db->prepare("SELECT * FROM reports WHERE type = :type ORDER BY created_at DESC");
        $stmt->execute(['type' => $type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByDateRange(DateTime $startDate, DateTime $endDate)
    {
        $stmt = $this->db->prepare("SELECT * FROM reports WHERE created_at BETWEEN :start_date AND :end_date ORDER BY created_at DESC");
        $stmt->execute([
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'end_date' => $endDate->format('Y-m-d H:i:s')
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentReports($limit = 10)
    {
        $stmt = $this->db->prepare("
            SELECT r.*, s.id as student_id, u.first_name, u.last_name
            FROM reports r
            JOIN students s ON r.student_id = s.id
            JOIN users u ON s.user_id = u.id
            ORDER BY r.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
