<?php

namespace App\Models;

use PDO;
use Exception;

class Student
{
    private $id;
    private $userId;
    private $cohortId;
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT s.*, u.username, u.first_name, u.last_name, u.email, u.slack_id
            FROM students s
            JOIN users u ON s.user_id = u.id
            WHERE s.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll()
    {
        $query = "
            SELECT 
                s.*, 
                u.username, u.first_name, u.last_name, u.email, u.slack_id,
                c.name as cohort_name,
                GROUP_CONCAT(CONCAT(uv.start_date, ' to ', uv.end_date) SEPARATOR ', ') as unavailability,
                (SELECT COUNT(*) FROM sod_schedule ss WHERE ss.student_id = s.id) as sod_count
            FROM students s 
            JOIN users u ON s.user_id = u.id
            LEFT JOIN cohorts c ON s.cohort_id = c.id
            LEFT JOIN unavailabilities uv ON s.id = uv.student_id
            GROUP BY s.id
        ";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($userId, $cohortId)
    {
        $stmt = $this->db->prepare("INSERT INTO students (user_id, cohort_id) VALUES (:user_id, :cohort_id)");
        return $stmt->execute([
            'user_id' => $userId,
            'cohort_id' => $cohortId
        ]);
    }

    public function update($id, $cohortId)
    {
        $sql = "UPDATE students SET cohort_id = :cohort_id WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'cohort_id' => $cohortId
        ]);
    }

    public function delete($id)
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT user_id FROM students WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $userId = $stmt->fetchColumn();

            $stmt = $this->db->prepare("DELETE FROM students WHERE id = :id");
            $stmt->execute(['id' => $id]);

            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(['id' => $userId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findByCohort($cohortId)
    {
        $stmt = $this->db->prepare("
            SELECT s.*, u.username, u.first_name, u.last_name, u.email, u.slack_id
            FROM students s
            JOIN users u ON s.user_id = u.id
            WHERE s.cohort_id = :cohort_id
            ORDER BY u.last_name, u.first_name
        ");
        $stmt->execute(['cohort_id' => $cohortId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("
            SELECT s.*, u.username, u.first_name, u.last_name, u.email, u.slack_id
            FROM students s
            JOIN users u ON s.user_id = u.id
            WHERE u.email = :email
        ");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findBySlackId($slackId)
    {
        $stmt = $this->db->prepare("
            SELECT s.*, u.username, u.first_name, u.last_name, u.email, u.slack_id
            FROM students s
            JOIN users u ON s.user_id = u.id
            WHERE u.slack_id = :slack_id
        ");
        $stmt->execute(['slack_id' => $slackId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function search($searchTerm)
    {
        $stmt = $this->db->prepare("
            SELECT s.*, u.username, u.first_name, u.last_name, u.email, u.slack_id
            FROM students s
            JOIN users u ON s.user_id = u.id
            WHERE u.last_name LIKE :search OR u.first_name LIKE :search OR u.email LIKE :search
            ORDER BY u.last_name, u.first_name
        ");
        $stmt->execute(['search' => "%$searchTerm%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

	public function findByUserId($userId)
	{
		$stmt = $this->db->prepare("SELECT * FROM students WHERE user_id = :user_id");
		$stmt->execute(['user_id' => $userId]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

    public function getTotalCount()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM students");
        return $stmt->fetchColumn();
    }

	public function getStudentsByCohorts($cohortIds)
	{
		$placeholders = implode(',', array_fill(0, count($cohortIds), '?'));
		
		$sql = "SELECT s.*, u.first_name, u.last_name, u.email 
				FROM students s
				JOIN users u ON s.user_id = u.id
				WHERE s.cohort_id IN ($placeholders)";
		
		$stmt = $this->db->prepare($sql);
		$stmt->execute($cohortIds);
		
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}