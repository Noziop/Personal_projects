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
use Psr\Log\LoggerInterface;

class DrawingService
{
    /**
     * @var PDO The database connection
     */
    private $db;

    /**
     * @var LoggerInterface The logger
     */
    private $logger;

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
     * @param LoggerInterface $logger The logger
     * @param StudentService $studentService The student service
     * @param ConstraintService $constraintService The constraint service
     */
    public function __construct(PDO $db, LoggerInterface $logger, StudentService $studentService, ConstraintService $constraintService)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->studentService = $studentService;
        $this->constraintService = $constraintService;
    }

    /**
     * Get all drawings.
     *
     * @return array An array of Drawing objects
     * @throws \PDOException If there's an error executing the query
     */
    public function getAllDrawings(): array
    {
        $this->logger->info('Fetching all drawings');
        try {
            $stmt = $this->db->query("SELECT * FROM drawings ORDER BY drawing_date DESC");
            $drawings = $stmt->fetchAll(PDO::FETCH_CLASS, Drawing::class);
            $this->logger->info('Fetched ' . count($drawings) . ' drawings');
            return $drawings;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching all drawings: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a drawing by its ID.
     *
     * @param int $id The ID of the drawing
     * @return Drawing|null The Drawing object if found, null otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function getDrawingById(int $id): ?Drawing
    {
        $this->logger->info('Fetching drawing with id: ' . $id);
        try {
            $stmt = $this->db->prepare("SELECT * FROM drawings WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $drawing = $stmt->fetchObject(Drawing::class);
            if ($drawing) {
                $this->logger->info('Drawing found', ['id' => $id]);
            } else {
                $this->logger->warning('Drawing not found', ['id' => $id]);
            }
            return $drawing ?: null;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching drawing: ' . $e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Create a new drawing.
     *
     * @param Drawing $drawing The Drawing object to create
     * @return int The ID of the newly created drawing
     * @throws \Exception If the drawing cannot be created due to constraints
     * @throws \PDOException If there's an error executing the query
     */
    public function createDrawing(Drawing $drawing): int
    {
        $this->logger->info('Creating new drawing', [
            'student_id' => $drawing->getStudentId(),
            'cohort_id' => $drawing->getCohortId(),
            'speaking_date' => $drawing->getSpeakingDate()->format('Y-m-d')
        ]);

        try {
            if (!$this->canPerformDrawing($drawing->getCohortId(), $drawing->getSpeakingDate())) {
                $this->logger->warning('Cannot perform drawing due to existing constraints', [
                    'cohort_id' => $drawing->getCohortId(),
                    'speaking_date' => $drawing->getSpeakingDate()->format('Y-m-d')
                ]);
                throw new \Exception("Cannot perform drawing due to existing constraints.");
            }

            $stmt = $this->db->prepare("INSERT INTO drawings (student_id, cohort_id, drawing_date, speaking_date) VALUES (:student_id, :cohort_id, :drawing_date, :speaking_date)");
            $stmt->execute([
                ':student_id' => $drawing->getStudentId(),
                ':cohort_id' => $drawing->getCohortId(),
                ':drawing_date' => $drawing->getDrawingDate()->format('Y-m-d H:i:s'),
                ':speaking_date' => $drawing->getSpeakingDate()->format('Y-m-d')
            ]);
            $id = (int) $this->db->lastInsertId();
            $this->logger->info('Drawing created', ['id' => $id]);
            return $id;
        } catch (\PDOException $e) {
            $this->logger->error('Error creating drawing: ' . $e->getMessage(), [
                'student_id' => $drawing->getStudentId(),
                'cohort_id' => $drawing->getCohortId()
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing drawing.
     *
     * @param Drawing $drawing The Drawing object to update
     * @return bool True if the update was successful, false otherwise
     * @throws \Exception If the drawing cannot be updated due to constraints
     * @throws \PDOException If there's an error executing the query
     */
    public function updateDrawing(Drawing $drawing): bool
    {
        $this->logger->info('Updating drawing', ['id' => $drawing->getId()]);
        try {
            if (!$this->canPerformDrawing($drawing->getCohortId(), $drawing->getSpeakingDate(), $drawing->getId())) {
                $this->logger->warning('Cannot update drawing due to existing constraints', ['id' => $drawing->getId()]);
                throw new \Exception("Cannot update drawing due to existing constraints.");
            }

            $stmt = $this->db->prepare("UPDATE drawings SET student_id = :student_id, cohort_id = :cohort_id, drawing_date = :drawing_date, speaking_date = :speaking_date WHERE id = :id");
            $success = $stmt->execute([
                ':id' => $drawing->getId(),
                ':student_id' => $drawing->getStudentId(),
                ':cohort_id' => $drawing->getCohortId(),
                ':drawing_date' => $drawing->getDrawingDate()->format('Y-m-d H:i:s'),
                ':speaking_date' => $drawing->getSpeakingDate()->format('Y-m-d')
            ]);
            if ($success) {
                $this->logger->info('Drawing updated successfully', ['id' => $drawing->getId()]);
            } else {
                $this->logger->warning('Failed to update drawing', ['id' => $drawing->getId()]);
            }
            return $success;
        } catch (\PDOException $e) {
            $this->logger->error('Error updating drawing: ' . $e->getMessage(), ['id' => $drawing->getId()]);
            throw $e;
        }
    }

    /**
     * Delete a drawing by its ID.
     *
     * @param int $id The ID of the drawing to delete
     * @return bool True if the deletion was successful, false otherwise
     * @throws \PDOException If there's an error executing the query
     */
    public function deleteDrawing(int $id): bool
    {
        $this->logger->info('Deleting drawing', ['id' => $id]);
        try {
            $stmt = $this->db->prepare("DELETE FROM drawings WHERE id = :id");
            $success = $stmt->execute([':id' => $id]);
            if ($success) {
                $this->logger->info('Drawing deleted successfully', ['id' => $id]);
            } else {
                $this->logger->warning('Failed to delete drawing', ['id' => $id]);
            }
            return $success;
        } catch (\PDOException $e) {
            $this->logger->error('Error deleting drawing: ' . $e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Get all drawings for a specific cohort.
     *
     * @param int $cohortId The ID of the cohort
     * @return array An array of Drawing objects
     * @throws \PDOException If there's an error executing the query
     */
    public function getDrawingsByCohort(int $cohortId): array
    {
        $this->logger->info('Fetching drawings for cohort', ['cohort_id' => $cohortId]);
        try {
            $stmt = $this->db->prepare("SELECT * FROM drawings WHERE cohort_id = :cohort_id ORDER BY drawing_date DESC");
            $stmt->execute([':cohort_id' => $cohortId]);
            $drawings = $stmt->fetchAll(PDO::FETCH_CLASS, Drawing::class);
            $this->logger->info('Fetched ' . count($drawings) . ' drawings for cohort', ['cohort_id' => $cohortId]);
            return $drawings;
        } catch (\PDOException $e) {
            $this->logger->error('Error fetching drawings for cohort: ' . $e->getMessage(), ['cohort_id' => $cohortId]);
            throw $e;
        }
    }

    /**
     * Perform a random drawing for a specific cohort and date.
     *
     * @param int $cohortId The ID of the cohort
     * @param \DateTime $speakingDate The date for which the drawing is being performed
     * @return Drawing|null The resulting Drawing object if successful, null otherwise
     * @throws \Exception If the drawing cannot be performed due to constraints
     * @throws \PDOException If there's an error executing the query
     */
    public function performRandomDrawing(int $cohortId, \DateTime $speakingDate): ?Drawing
    {
        $this->logger->info('Performing random drawing', [
            'cohort_id' => $cohortId,
            'speaking_date' => $speakingDate->format('Y-m-d')
        ]);

        try {
            if (!$this->canPerformDrawing($cohortId, $speakingDate)) {
                $this->logger->warning('Cannot perform drawing due to existing constraints', [
                    'cohort_id' => $cohortId,
                    'speaking_date' => $speakingDate->format('Y-m-d')
                ]);
                throw new \Exception("Cannot perform drawing due to existing constraints.");
            }

            $eligibleStudents = $this->getEligibleStudents($cohortId, $speakingDate);
            if (empty($eligibleStudents)) {
                $this->logger->warning('No eligible students found for the drawing', [
                    'cohort_id' => $cohortId,
                    'speaking_date' => $speakingDate->format('Y-m-d')
                ]);
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
            $this->logger->info('Random drawing performed successfully', ['drawing_id' => $drawingId]);
            return $this->getDrawingById($drawingId);
        } catch (\PDOException $e) {
            $this->logger->error('Error performing random drawing: ' . $e->getMessage(), [
                'cohort_id' => $cohortId,
                'speaking_date' => $speakingDate->format('Y-m-d')
            ]);
            throw $e;
        }
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
        $this->logger->info('Checking if drawing can be performed', [
            'cohort_id' => $cohortId,
            'speaking_date' => $speakingDate->format('Y-m-d'),
            'exclude_drawing_id' => $excludeDrawingId
        ]);

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
            $this->logger->info('Drawing cannot be performed: existing drawing on the same date');
            return false;
        }

        // Check for constraints
        $constraints = $this->constraintService->getConstraintsByDateRange($speakingDate, $speakingDate);
        foreach ($constraints as $constraint) {
            if ($constraint->getCohortId() === null || $constraint->getCohortId() === $cohortId) {
                $this->logger->info('Drawing cannot be performed: constraint found', ['constraint_id' => $constraint->getId()]);
                return false;
            }
        }

        $this->logger->info('Drawing can be performed');
        return true;
    }

    /**
     * Get eligible students for a drawing.
     *
     * @param int $cohortId The ID of the cohort
     * @param \DateTime $speakingDate The date for which the drawing is being performed
     * @return array An array of eligible Student objects
     * @throws \PDOException If there's an error executing the query
     */
    private function getEligibleStudents(int $cohortId, \DateTime $speakingDate): array
    {
        $this->logger->info('Getting eligible students for drawing', [
            'cohort_id' => $cohortId,
            'speaking_date' => $speakingDate->format('Y-m-d')
        ]);

        try {
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

            $this->logger->info('Found ' . count($eligibleStudents) . ' eligible students', [
                'cohort_id' => $cohortId,
                'speaking_date' => $speakingDate->format('Y-m-d')
            ]);

            return $eligibleStudents;
        } catch (\PDOException $e) {
            $this->logger->error('Error getting eligible students: ' . $e->getMessage(), [
                'cohort_id' => $cohortId,
                'speaking_date' => $speakingDate->format('Y-m-d')
            ]);
            throw $e;
        }
    }
}
