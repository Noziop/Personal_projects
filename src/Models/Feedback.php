<?php

namespace App\Models;

use PDO;
use DateTime;

class Feedback
{
    private $db;
    private $id;
    private $studentId;
    private $evaluatorId;
    private $sodDate;
    private $content;
    private $createdAt;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM sod_feedback WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $feedbackData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $feedbackData ? $this->hydrate($feedbackData) : null;
    }

    public function findAll()
    {
        $stmt = $this->db->query("SELECT * FROM sod_feedback ORDER BY sod_date DESC");
        $feedbacksData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $feedbacks = [];
        foreach ($feedbacksData as $feedbackData) {
            $feedbacks[] = $this->hydrate($feedbackData);
        }
        return $feedbacks;
    }

    public function create($studentId, $evaluatorId, $sodDate, $content)
    {
        $stmt = $this->db->prepare("INSERT INTO sod_feedback (student_id, evaluator_id, sod_date, content) VALUES (:student_id, :evaluator_id, :sod_date, :content)");
        $result = $stmt->execute([
            'student_id' => $studentId,
            'evaluator_id' => $evaluatorId,
            'sod_date' => $sodDate,
            'content' => $content
        ]);
        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function update($id, $studentId, $evaluatorId, $sodDate, $content)
    {
        $stmt = $this->db->prepare("UPDATE sod_feedback SET student_id = :student_id, evaluator_id = :evaluator_id, sod_date = :sod_date, content = :content WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'student_id' => $studentId,
            'evaluator_id' => $evaluatorId,
            'sod_date' => $sodDate,
            'content' => $content
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM sod_feedback WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    private function hydrate($data)
    {
        $this->id = $data['id'];
        $this->studentId = $data['student_id'];
        $this->evaluatorId = $data['evaluator_id'];
        $this->sodDate = new DateTime($data['sod_date']);
        $this->content = $data['content'];
        $this->createdAt = new DateTime($data['created_at']);
        return $this;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getStudentId() { return $this->studentId; }
    public function getEvaluatorId() { return $this->evaluatorId; }
    public function getSodDate() { return $this->sodDate; }
    public function getContent() { return $this->content; }
    public function getCreatedAt() { return $this->createdAt; }
}