<?php

namespace App\Services;

use App\Models\Feedback;
use App\Models\Student;
use Psr\Log\LoggerInterface;

class FeedbackService
{
    private $feedbackModel;
    private $studentModel;
    private $logger;

    public function __construct(Feedback $feedbackModel, Student $studentModel, LoggerInterface $logger)
    {
        $this->feedbackModel = $feedbackModel;
        $this->studentModel = $studentModel;
        $this->logger = $logger;
    }

	public function getAllFeedbacks($eventType = null, $date = null, $studentId = null)
	{
		$this->logger->info('Fetching all feedbacks', ['event_type' => $eventType, 'date' => $date, 'student_id' => $studentId]);
		$feedbacks = $this->feedbackModel->findAllWithStudentInfo($eventType, $date, $studentId);
		$this->logger->info('Retrieved feedbacks', ['count' => count($feedbacks), 'types' => array_column($feedbacks, 'type')]);
		return $feedbacks;
	}
		

    public function getFeedbackById($id, $type)
    {
        $this->logger->info('Fetching feedback by ID', ['id' => $id, 'type' => $type]);
        return $this->feedbackModel->findById($id, $type);
    }

    public function getAllStudents()
    {
        return $this->studentModel->findAll();
    }

    // Ajoutez d'autres méthodes si nécessaire
}