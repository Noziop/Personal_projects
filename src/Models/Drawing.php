<?php

namespace App\Models;

use PDO;
use DateTime;

/**
 * Drawing Model
 *
 * This class represents a drawing (tirage au sort) in the application.
 */
class Drawing
{
    private $id;
    private $cohortId;
    private $studentId;
    private $drawDate;
    private $type;
    private $status;
    private $createdAt;

    private $db;

    /**
     * Drawing constructor.
     *
     * @param PDO $db The database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a drawing by its ID
     *
     * @param int $id The drawing ID
     * @return Drawing|null The drawing object if found, null otherwise
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM drawings WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $drawingData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($drawingData) {
            return $this->hydrate($drawingData);
        }

        return null;
    }

    /**
     * Get all drawings for a specific cohort
     *
     * @param int $cohortId The cohort ID
     * @return array An array of Drawing objects
     */
    public function getAllForCohort($cohortId)
    {
        $stmt = $this->db->prepare("SELECT * FROM drawings WHERE cohort_id = :cohort_id ORDER BY draw_date DESC");
        $stmt->execute(['cohort_id' => $cohortId]);
        $drawingsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $drawings = [];
        foreach ($drawingsData as $drawingData) {
            $drawings[] = (new Drawing($this->db))->hydrate($drawingData);
        }

        return $drawings;
    }

    /**
     * Create a new drawing
     *
     * @param array $data The drawing data
     * @return bool True if the drawing was created successfully, false otherwise
     */
    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO drawings (cohort_id, student_id, draw_date, type, status) VALUES (:cohort_id, :student_id, :draw_date, :type, :status)");
        return $stmt->execute([
            'cohort_id' => $data['cohort_id'],
            'student_id' => $data['student_id'],
            'draw_date' => $data['draw_date'],
            'type' => $data['type'],
            'status' => $data['status'] ?? 'pending'
        ]);
    }

    /**
     * Update an existing drawing
     *
     * @param array $data The drawing data to update
     * @return bool True if the drawing was updated successfully, false otherwise
     */
    public function update($data)
    {
        $stmt = $this->db->prepare("UPDATE drawings SET student_id = :student_id, draw_date = :draw_date, type = :type, status = :status WHERE id = :id");
        return $stmt->execute([
            'id' => $this->id,
            'student_id' => $data['student_id'],
            'draw_date' => $data['draw_date'],
            'type' => $data['type'],
            'status' => $data['status']
        ]);
    }

    /**
     * Delete the drawing
     *
     * @return bool True if the drawing was deleted successfully, false otherwise
     */
    public function delete()
    {
        $stmt = $this->db->prepare("DELETE FROM drawings WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }

    /**
     * Get the student associated with this drawing
     *
     * @return Student|null The Student object if found, null otherwise
     */
    public function getStudent()
    {
        return (new Student($this->db))->findById($this->studentId);
    }

    /**
     * Get the cohort associated with this drawing
     *
     * @return Cohort|null The Cohort object if found, null otherwise
     */
    public function getCohort()
    {
        return (new Cohort($this->db))->findById($this->cohortId);
    }

	public function getLastDrawingForStudent($studentId, $startDate)
	{
		$sql = "SELECT * FROM drawings 
				WHERE student_id = :student_id 
				AND drawing_date >= :start_date
				ORDER BY drawing_date DESC 
				LIMIT 1";
		
		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			'student_id' => $studentId,
			'start_date' => $startDate
		]);
		
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function getDrawingsCountForStudent($studentId)
	{
		$sql = "SELECT COUNT(*) FROM drawings WHERE student_id = :student_id";
		
		$stmt = $this->db->prepare($sql);
		$stmt->execute(['student_id' => $studentId]);
		
		return $stmt->fetchColumn();
	}

	public function createDrawing(array $data)
	{
		$sql = "INSERT INTO drawings (student_id, drawing_date, presentation_date) 
				VALUES (:student_id, :drawing_date, :presentation_date)";
		
		$stmt = $this->db->prepare($sql);
		$result = $stmt->execute([
			'student_id' => $data['student_id'],
			'drawing_date' => $data['drawing_date'],
			'presentation_date' => $data['presentation_date']
		]);

		if ($result) {
			return $this->db->lastInsertId();
		}

		return false;
	}

	public function getAllDrawings()
	{
		$sql = "SELECT d.*, u.first_name, u.last_name, c.name as cohort_name 
				FROM drawings d
				JOIN students s ON d.student_id = s.id
				JOIN users u ON s.user_id = u.id
				JOIN cohorts c ON s.cohort_id = c.id
				ORDER BY d.drawing_date ASC";
		
		$stmt = $this->db->query($sql);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function archiveDrawings()
	{
		try {
			$this->db->beginTransaction();
			
			// Copier les données de 'drawings' vers 'drawing_history'
			$sql = "INSERT INTO drawing_history (student_id, drawing_date, presentation_date)
					SELECT student_id, drawing_date, presentation_date
					FROM drawings";
			$this->db->exec($sql);
			
			// Obtenir le nombre de lignes copiées
			$rowsCopied = $this->db->query("SELECT ROW_COUNT()")->fetchColumn();
			
			// Vider la table 'drawings'
			$this->db->exec("TRUNCATE TABLE drawings");
			
			$this->db->commit();
			
			return $rowsCopied;
		} catch (\Exception $e) {
			$this->db->rollBack();
			throw $e;
		}
	}
	
	private function createDrawingHistoryTableIfNotExists()
	{
		$sql = "CREATE TABLE IF NOT EXISTS drawing_history (
			id INT AUTO_INCREMENT PRIMARY KEY,
			student_id INT NOT NULL,
			drawing_date DATE NOT NULL,
			presentation_date DATE NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		)";
		$this->db->exec($sql);
	}

    public function resetDrawings()
    {
        try {
            $this->db->exec("TRUNCATE TABLE drawings");
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * Hydrate the drawing object with data
     *
     * @param array $data The data to hydrate the object with
     * @return Drawing The hydrated drawing object
     */
    private function hydrate($data)
    {
        $this->id = $data['id'];
        $this->cohortId = $data['cohort_id'];
        $this->studentId = $data['student_id'];
        $this->drawDate = new DateTime($data['draw_date']);
        $this->type = $data['type'];
        $this->status = $data['status'];
        $this->createdAt = new DateTime($data['created_at']);

        return $this;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getCohortId() { return $this->cohortId; }
    public function getStudentId() { return $this->studentId; }
    public function getDrawDate() { return $this->drawDate; }
    public function getType() { return $this->type; }
    public function getStatus() { return $this->status; }
    public function getCreatedAt() { return $this->createdAt; }
}