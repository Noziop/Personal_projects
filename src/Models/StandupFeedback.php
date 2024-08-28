<?php

namespace App\Models;

use PDO;

class StandupFeedback
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO standup_feedback (student_id, cohort_id, date, absent, on_site, achievements, today_goals, need_help, problem_nature, other_remarks, content) 
                VALUES (:student_id, :cohort_id, :date, :absent, :on_site, :achievements, :today_goals, :need_help, :problem_nature, :other_remarks, :content)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM standup_feedback WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll()
    {
        $stmt = $this->db->query("SELECT * FROM standup_feedback ORDER BY date DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ajoutez d'autres méthodes selon vos besoins
}