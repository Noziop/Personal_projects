<?php

namespace App\Models;

use PDO;

class SODFeedback
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO sod_feedback (student_id, evaluator_id, sod_date, content) 
                VALUES (:student_id, :evaluator_id, :sod_date, :content)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'student_id' => $data['student_id'],
            'evaluator_id' => $data['evaluator_id'],
            'sod_date' => $data['sod_date'],
            'content' => $data['content']
        ]);
    }

    // Ajoutez d'autres méthodes si nécessaire
}
