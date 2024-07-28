<?php

/**
 * DrawingService
 *
 * This service is responsible for managing drawings in the SOD (Speaker of the Day) application.
 * It handles the creation, retrieval, and management of drawing events.
 */

namespace App\Services;

use App\Models\Drawing;
use PDO;

class DrawingService
{
    /**
     * @var PDO The database connection
     */
    private $db;

    /**
     * @var StudentService The student service for retrieving student information
     */
    private $studentService;

    /**
     * @var ConstraintService The constraint service for checking drawing constraints
     */
    private $constraintService;

    /**
     * DrawingService constructor.
     *
     * @param PDO $db The database connection
     * @param StudentService $studentService The student service
     * @param ConstraintService $constraintService The constraint service
     */
    public function __construct(PDO $db, StudentService $studentService, ConstraintService $constraintService)
    {
        $this->db = $db;
        $this->studentService = $studentService;
        $this->constraintService = $constraintService;
    }

    /**
     * Get all drawings.
     *
     * @return array An array of Drawing objects
     */
    public function getAllDrawings(): array
    {
        $stmt = $this->db->query("SELECT * FROM drawings ORDER BY drawing_date DESC");
        return $stmt->fetchAll(PDO::FETCH_CLASS, Drawing::class);
    }

    /**
     * Get a drawing by its ID.
     *
     * @param int $id The ID of the drawing
     * @return Drawing|null The Drawing object if found, null otherwise
     */
    public function getDrawingById(int $id): ?Drawing
    {
        $stmt = $this->db->prepare("SELECT * FROM drawings WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $drawing = $stmt->fetchObject(Drawing::class);
        return $drawing ?: null;
    }

    /**
     * Create a new drawing.
     *
     * @param Drawing $drawing The Drawing object to create
     * @return int The ID of the newly created drawing
     * @throws \Exception If the drawing cannot be created due to constraints
     */
    public function createDrawing(Drawing $drawing): int
    {
        // Check for constraints
        if (!$this->canPerformDrawing($drawing->getCohortId(), $drawing->getSpeakingDate())) {
            throw new \Exception("Cannot perform drawing due to existing constraints.");
        }

        $stmt = $this->db->prepare("INSERT INTO drawings (student_id, cohort_id, drawing_date, speaking_date) VALUES (:student_id, :cohort_id, :drawing_date, :speaking_date)");
        $stmt->execute([
            ':student_id' => $drawing->getStudentId(),
            ':cohort_id' => $drawing->getCohortId(),
            ':drawing_date' => $drawing->getDrawingDate()->format('Y-m-d H:i:s'),
            ':speaking_date' => $drawing->getSpeakingDate()->format('Y-m-d')
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing drawing.
     *
     * @param Drawing $drawing The Drawing object to update
     * @return bool True if the update was successful, false otherwise
     * @throws \Exception If the drawing cannot be updated due to constraints
     */
    public function updateDrawing(Drawing $drawing): bool
    {
        // Check for constraints
        if (!$this->canPerformDrawing($drawing->getCohortId(), $drawing->getSpeakingDate(), $drawing->getId())) {
            throw new \Exception("Cannot update drawing due to existing constraints.");
        }

        $stmt = $this->db->prepare("UPDATE drawings SET student_id = :student_id, cohort_id = :cohort_id, drawing_date = :drawing_date, speaking_date = :speaking_date WHERE id = :id");
        return $stmt->execute([
            ':id' => $drawing->getId(),
            ':student_id' => $drawing->getStudentId(),
            ':cohort_id' => $drawing->getCohortId(),
            ':drawing_date' => $drawing->getDrawingDate()->format('Y-m-d H:i:s'),
            ':speaking_date' => $drawing->getSpeakingDate()->format('Y-m-d')
        ]);
    }

    /**
     * Delete a drawing by its ID.
     *
     * @param int $id The ID of the drawing to delete
     * @return bool True if the deletion was successful, false otherwise
     */
    public function deleteDrawing(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM drawings WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get all drawings for a specific cohort.
     *
     * @param int $cohortId The ID of the cohort
     * @return array An array of Drawing objects
     */
    public function getDrawingsByCohort(int $cohortId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM drawings WHERE cohort_id = :cohort_id ORDER BY drawing_date DESC");
        $stmt->execute([':cohort_id' => $cohortId]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, Drawing::class);
    }

    /**
     * Perform a random drawing for a specific cohort and date.
     *
     * @param int $cohortId The ID of the cohort
     * @param \DateTime $speakingDate The date for which the drawing is being performed
     * @return Drawing|null The resulting Drawing object if successful, null otherwise
     * @throws \Exception If the drawing cannot be performed due to constraints
     */
    public function performRandomDrawing(int $cohortId, \DateTime $speakingDate): ?Drawing
    {
        if (!$this->canPerformDrawing($cohortId, $speakingDate)) {
            throw new \Exception("Cannot perform drawing due to existing constraints.");
        }

        $eligibleStudents = $this->getEligibleStudents($cohortId, $speakingDate);
        if (empty($eligibleStudents)) {
            throw new \Exception("No eligible students found for the drawing.");
        }

        $randomStudent = $eligibleStudents[array_rand($eligibleStudents)];
        $drawing = new Drawing(
            $randomStudent->getId(),
            $cohortId,
            new \DateTime(),
            $speakingDate
        );

        $drawingId = $this->createDrawing($drawing);
        return $this->getDrawingById($drawingId);
    }

    /**
     * Check if a drawing can be performed for a specific cohort and date.
     *
     * @param int $cohortId The ID of the cohort
     * @param \DateTime $speakingDate The date for which the drawing is being checked
     * @param int|null $excludeDrawingId An optional drawing ID to exclude from the check (for updates)
     * @return bool True if the drawing can be performed, false otherwise
     */
    private function canPerformDrawing(int $cohortId, \DateTime $speakingDate, ?int $excludeDrawingId = null): bool
    {
        // Check for existing drawings on the same date
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM drawings WHERE cohort_id = :cohort_id AND speaking_date = :speaking_date" . ($excludeDrawingId ? " AND id != :exclude_id" : ""));
        $params = [
            ':cohort_id' => $cohortId,
            ':speaking_date' => $speakingDate->format('Y-m-d')
        ];
        if ($excludeDrawingId) {
            $params[':exclude_id'] = $excludeDrawingId;
        }
        $stmt->execute($params);
        if ($stmt->fetchColumn() > 0) {
            return false;
        }

        // Check for constraints
        $constraints = $this->constraintService->getConstraintsByDateRange($speakingDate, $speakingDate);
        foreach ($constraints as $constraint) {
            if ($constraint->getCohortId() === null || $constraint->getCohortId() === $cohortId) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get eligible students for a drawing.
     *
     * @param int $cohortId The ID of the cohort
     * @param \DateTime $speakingDate The date for which the drawing is being performed
     * @return array An array of eligible Student objects
     */
    private function getEligibleStudents(int $cohortId, \DateTime $speakingDate): array
    {
        $allStudents = $this->studentService->getStudentsByCohort($cohortId);
        $eligibleStudents = [];

        foreach ($allStudents as $student) {
            // Check if the student has any unavailability on the speaking date
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM unavailabilities WHERE student_id = :student_id AND :speaking_date BETWEEN start_date AND end_date");
            $stmt->execute([
                ':student_id' => $student->getId(),
                ':speaking_date' => $speakingDate->format('Y-m-d')
            ]);
            if ($stmt->fetchColumn() == 0) {
                $eligibleStudents[] = $student;
            }
        }

        return $eligibleStudents;
    }
}
