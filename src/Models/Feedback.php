<?php

namespace App\Models;

use PDO;
use DateTime;

class Feedback
{
    private $db;
    private $id;
    private $studentId;
	private $studentName;
    private $type;
    private $date;
    private $content;
    private $createdAt;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

	public function findById($id, $type)
	{
		$table = $this->getTableName($type);
		$stmt = $this->db->prepare("SELECT * FROM $table WHERE id = :id");
		$stmt->execute(['id' => $id]);
		$feedbackData = $stmt->fetch(PDO::FETCH_ASSOC);
		return $feedbackData ? $this->hydrate($feedbackData, $type) : null;
	}

	public function findAll($type = null, $date = null, $studentId = null)
{
		if ($type) {
			$table = $this->getTableName($type);
			$stmt = $this->db->query("SELECT *, '$type' as type FROM $table ORDER BY date DESC");
		} else {
			$stmt = $this->db->query("
				SELECT id, student_id, sod_date as date, content, created_at, 'SOD' as type, evaluator_id, NULL as cohort_id, NULL as absent, NULL as on_site, NULL as achievements, NULL as today_goals, NULL as need_help, NULL as problem_nature, NULL as other_remarks
				FROM sod_feedback
				UNION ALL
				SELECT id, student_id, date, content, created_at, 'Standup' as type, NULL as evaluator_id, cohort_id, absent, on_site, achievements, today_goals, need_help, problem_nature, other_remarks
				FROM standup_feedback
				UNION ALL
				SELECT id, student_id, date, content, created_at, 'PLD' as type, NULL as evaluator_id, NULL as cohort_id, NULL as absent, NULL as on_site, NULL as achievements, NULL as today_goals, NULL as need_help, NULL as problem_nature, NULL as other_remarks
				FROM pld_submissions
				ORDER BY date DESC
			");
		}
		$feedbacksData = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$feedbacks = [];
		foreach ($feedbacksData as $feedbackData) {
			$feedbacks[] = $this->hydrate($feedbackData);
		}
		return $feedbacks;
	}

    public function create($data, $type)
    {
        $table = $this->getTableName($type);
        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));
        $stmt = $this->db->prepare("INSERT INTO $table ($columns) VALUES ($values)");
        $result = $stmt->execute($data);
        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function update($id, $data, $type)
    {
        $table = $this->getTableName($type);
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }
        $set = implode(', ', $set);
        $stmt = $this->db->prepare("UPDATE $table SET $set WHERE id = :id");
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function delete($id, $type)
    {
        $table = $this->getTableName($type);
        $stmt = $this->db->prepare("DELETE FROM $table WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

	private function hydrate($data)
	{
		$this->id = $data['id'] ?? null;
		$this->studentId = $data['student_id'] ?? null;
		$this->type = $data['type'] ?? null;
		$this->date = isset($data['date']) ? new DateTime($data['date']) : null;
		$this->content = $data['content'] ?? null;
		$this->createdAt = isset($data['created_at']) ? new DateTime($data['created_at']) : null;
		
		// Hydrate specific fields based on type
		switch ($this->type) {
			case 'SOD':
				$this->evaluatorId = $data['evaluator_id'] ?? null;
				break;
			case 'Standup':
				$this->cohortId = $data['cohort_id'] ?? null;
				$this->absent = $data['absent'] ?? null;
				$this->onSite = $data['on_site'] ?? null;
				$this->achievements = $data['achievements'] ?? null;
				$this->todayGoals = $data['today_goals'] ?? null;
				$this->needHelp = $data['need_help'] ?? null;
				$this->problemNature = $data['problem_nature'] ?? null;
				$this->otherRemarks = $data['other_remarks'] ?? null;
				break;
			case 'PLD':
				// No specific fields for PLD
				break;
		}
		
		return $this;
	}

	private function getTableName($type)
	{
		switch ($type) {
			case 'SOD':
				return 'sod_feedback';
			case 'Standup':
				return 'standup_feedback';
			case 'PLD':
				return 'pld_submissions';
			default:
				throw new \InvalidArgumentException("Invalid feedback type: $type");
		}
	}

	public function findAllWithStudentInfo($type = null, $date = null, $studentId = null)
	{
		$sql = "
		SELECT 
			'SOD' as type,
			f.id,
			f.student_id,
			f.evaluator_id,
			f.sod_date as date,
			f.content,
			f.created_at,
			s.user_id,
			u.first_name,
			u.last_name,
			e.user_id as evaluator_user_id,
			eu.first_name as evaluator_first_name,
			eu.last_name as evaluator_last_name
		FROM sod_feedback f
		JOIN students s ON f.student_id = s.id
		JOIN users u ON s.user_id = u.id
		LEFT JOIN students e ON f.evaluator_id = e.id
		LEFT JOIN users eu ON e.user_id = eu.id
		
		UNION ALL
		
		SELECT 
			'Standup' as type,
			f.id,
			f.student_id,
			NULL as evaluator_id,
			f.date,
			f.content,
			f.created_at,
			s.user_id,
			u.first_name,
			u.last_name,
			NULL as evaluator_user_id,
			NULL as evaluator_first_name,
			NULL as evaluator_last_name
		FROM standup_feedback f
		JOIN students s ON f.student_id = s.id
		JOIN users u ON s.user_id = u.id
		
		UNION ALL
		
		SELECT 
			'PLD' as type,
			f.id,
			f.student_id,
			NULL as evaluator_id,
			f.date,
			f.content,
			f.created_at,
			s.user_id,
			u.first_name,
			u.last_name,
			NULL as evaluator_user_id,
			NULL as evaluator_first_name,
			NULL as evaluator_last_name
		FROM pld_submissions f
		JOIN students s ON f.student_id = s.id
		JOIN users u ON s.user_id = u.id
		";
	
		$conditions = [];
		$params = [];
	
		if ($type) {
			$conditions[] = "type = :type";
			$params[':type'] = $type;
		}
		if ($date) {
			$conditions[] = "DATE(date) = :date";
			$params[':date'] = $date;
		}
		if ($studentId) {
			$conditions[] = "student_id = :student_id";
			$params[':student_id'] = $studentId;
		}
	
		if (!empty($conditions)) {
			$sql .= " WHERE " . implode(" AND ", $conditions);
		}
	
		$sql .= " ORDER BY date DESC";
	
		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);
	
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function toArray()
	{
		return [
			'id' => $this->id,
			'type' => $this->type,
			'date' => $this->date ? $this->date->format('Y-m-d') : null,
			'studentId' => $this->studentId,
			'studentName' => $this->studentName,
			'content' => $this->content,
			// Ajoutez d'autres propriétés si nécessaire
		];
	}

	public function setStudentName($name)
    {
        $this->studentName = $name;
    }

    public function getStudentName()
    {
        return $this->studentName;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getStudentId() { return $this->studentId; }
    public function getType() { return $this->type; }
    public function getDate() { return $this->date; }
    public function getContent() { return $this->content; }
    public function getCreatedAt() { return $this->createdAt; }
}