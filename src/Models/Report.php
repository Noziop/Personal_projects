<?php

namespace App\Models;

use PDO;

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
        $stmt = $this->db->prepare("INSERT INTO reports (student_id, type, content) VALUES (:student_id, :type, :content)");
        return $stmt->execute([
            'student_id' => $studentId,
            'type' => $type,
            'content' => $content
        ]);
    }

    public function update($id, $studentId, $type, $content)
    {
        $stmt = $this->db->prepare("UPDATE reports SET student_id = :student_id, type = :type, content = :content WHERE id = :id");
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
        $stmt = $this->db->query("SELECT * FROM reports");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByStudentId($studentId)
    {
        $stmt = $this->db->prepare("SELECT * FROM reports WHERE student_id = :student_id");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByType($type)
    {
        $stmt = $this->db->prepare("SELECT * FROM reports WHERE type = :type");
        $stmt->execute(['type' => $type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
