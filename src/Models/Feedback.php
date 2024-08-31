<?php

namespace App\Models;

use PDO;

class Feedback
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

	public function findAllWithStudentInfo($eventType = null, $date = null, $studentId = null)
	{
		$sql = "
		SELECT 
			'SOD' as type,
			sf.id,
			sf.student_id,
			sf.evaluator_id,
			sf.sod_date as date,
			sf.content,
			sf.created_at,
			s.user_id,
			u.first_name,
			u.last_name,
			e.user_id as evaluator_user_id,
			eu.first_name as evaluator_first_name,
			eu.last_name as evaluator_last_name
		FROM sod_feedback sf
		JOIN students s ON sf.student_id = s.id
		JOIN users u ON s.user_id = u.id
		LEFT JOIN students e ON sf.evaluator_id = e.id
		LEFT JOIN users eu ON e.user_id = eu.id
		
		UNION ALL
		
		SELECT 
			'Stand-up' as type,
			stf.id,
			stf.cohort_id as student_id,
			NULL as evaluator_id,
			stf.date,
			stf.content,
			stf.created_at,
			NULL as user_id,
			c.name as first_name,
			'' as last_name,
			NULL as evaluator_user_id,
			NULL as evaluator_first_name,
			NULL as evaluator_last_name
		FROM standup_feedback stf
		JOIN cohorts c ON stf.cohort_id = c.id
		";
	
		$conditions = [];
		$params = [];
	
		if ($eventType) {
			$conditions[] = "type = :type";
			$params[':type'] = $eventType;
		}
		if ($date) {
			$conditions[] = "DATE(date) = :date";
			$params[':date'] = $date;
		}
		if ($studentId) {
			$conditions[] = "(student_id = :student_id OR cohort_id = :student_id)";
			$params[':student_id'] = $studentId;
		}
	
		if (!empty($conditions)) {
			$sql .= " WHERE " . implode(" AND ", $conditions);
		}
	
		$sql .= " ORDER BY date DESC, type";
	
		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);
	
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

    public function findById($id, $type = null)
    {
        $table = $this->getTableName($type);
        $sql = "SELECT * FROM $table WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getTableName($type)
    {
        switch ($type) {
            case 'SOD':
                return 'sod_feedback';
            case 'Stand-up':
                return 'standup_feedback';
            default:
                throw new \InvalidArgumentException("Invalid feedback type: $type");
        }
    }

    // Ajoutez d'autres méthodes si nécessaire
}