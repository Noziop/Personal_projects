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
		$feedbacks = $this->feedbackModel->findAll($eventType, $date, $studentId);
		
		foreach ($feedbacks as &$feedback) {
			$studentData = $this->studentModel->findById($feedback->getStudentId());
			if ($studentData) {
				$feedback->setStudentName($studentData['first_name'] . ' ' . $studentData['last_name']);
			} else {
				$feedback->setStudentName('N/A');
				$this->logger->warning('Student not found', ['student_id' => $feedback->getStudentId()]);
			}
		}
	
		return $feedbacks;
	}

    public function getFeedbackById($id, $eventType)
    {
        return $this->feedbackModel->findById($id, $eventType);
    }

    public function createFeedback($data, $eventType)
    {
        return $this->feedbackModel->create($data, $eventType);
    }

    public function updateFeedback($id, $data, $eventType)
    {
        return $this->feedbackModel->update($id, $data, $eventType);
    }

    public function deleteFeedback($id, $eventType)
    {
        return $this->feedbackModel->delete($id, $eventType);
    }

    public function getAllStudents()
    {
        return $this->studentModel->findAll();
    }

    public function getEventTypes()
    {
        return ['SOD', 'Stand up', 'PLD'];
    }
}