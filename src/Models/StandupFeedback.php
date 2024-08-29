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
		$sql = "INSERT INTO standup_feedback (student_id, cohort_id, date, content, summary) 
				VALUES (:student_id, :cohort_id, :date, :content, :summary)";
		$stmt = $this->db->prepare($sql);
		$result = $stmt->execute([
			'student_id' => $data['student_id'],
			'cohort_id' => $data['cohort_id'],
			'date' => $data['date'],
			'content' => $data['content'],
			'summary' => $data['summary']
		]);
		
		if ($result) {
			return $this->db->lastInsertId();
		}
		return false;
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