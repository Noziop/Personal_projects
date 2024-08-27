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

    public function getFeedback($eventType = null, $date = null, $studentId = null)
    {
        return $this->feedbackModel->findAll($eventType, $date, $studentId);
    }

    public function getAllStudents()
    {
        return $this->studentModel->findAll();
    }
}