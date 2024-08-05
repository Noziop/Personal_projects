<?php

namespace App\Services;

use App\Models\Drawing;
use Psr\Log\LoggerInterface;

class DrawingService
{
    private $drawingModel;
    private $logger;

    public function __construct(Drawing $drawingModel, LoggerInterface $logger)
    {
        $this->drawingModel = $drawingModel;
        $this->logger = $logger;
    }

    public function createDrawing($studentId, $drawingDate, $presentationDate, $type)
    {
        $this->logger->info('Creating new drawing', [
            'student_id' => $studentId,
            'drawing_date' => $drawingDate,
            'presentation_date' => $presentationDate,
            'type' => $type
        ]);

        return $this->drawingModel->create($studentId, $drawingDate, $presentationDate, $type);
    }

    public function getDrawingById($id)
    {
        $this->logger->info('Fetching drawing by ID', ['id' => $id]);
        return $this->drawingModel->findById($id);
    }

    public function updateDrawing($id, $studentId, $drawingDate, $presentationDate, $type)
    {
        $this->logger->info('Updating drawing', [
            'id' => $id,
            'student_id' => $studentId,
            'drawing_date' => $drawingDate,
            'presentation_date' => $presentationDate,
            'type' => $type
        ]);

        return $this->drawingModel->update($id, $studentId, $drawingDate, $presentationDate, $type);
    }

    public function deleteDrawing($id)
    {
        $this->logger->info('Deleting drawing', ['id' => $id]);
        return $this->drawingModel->delete($id);
    }

    public function getAllDrawings()
    {
        $this->logger->info('Fetching all drawings');
        return $this->drawingModel->findAll();
    }

    public function getDrawingsByStudentId($studentId)
    {
        $this->logger->info('Fetching drawings by student ID', ['student_id' => $studentId]);
        return $this->drawingModel->findByStudentId($studentId);
    }

    public function getDrawingsByType($type)
    {
        $this->logger->info('Fetching drawings by type', ['type' => $type]);
        return $this->drawingModel->findByType($type);
    }
}
