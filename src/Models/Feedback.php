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
		$this->id = $data['id'];
		$this->studentId = $data['student_id'];
		$this->type = $data['type'];
		$this->date = new DateTime($data['date']);
		$this->content = $data['content'];
		$this->createdAt = new DateTime($data['created_at']);
		
		// Hydrate specific fields based on type
		switch ($this->type) {
			case 'SOD':
				$this->evaluatorId = $data['evaluator_id'];
				break;
			case 'Standup':
				$this->cohortId = $data['cohort_id'];
				$this->absent = $data['absent'];
				$this->onSite = $data['on_site'];
				$this->achievements = $data['achievements'];
				$this->todayGoals = $data['today_goals'];
				$this->needHelp = $data['need_help'];
				$this->problemNature = $data['problem_nature'];
				$this->otherRemarks = $data['other_remarks'];
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