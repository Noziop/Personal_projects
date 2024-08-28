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
		$feedbacks = $this->feedbackModel->findAllWithStudentInfo($eventType, $date, $studentId);
		
		foreach ($feedbacks as &$feedback) {
			$feedback['student_name'] = $feedback['first_name'] . ' ' . $feedback['last_name'];
			$feedback['evaluator_name'] = $feedback['evaluator_first_name'] && $feedback['evaluator_last_name'] 
				? $feedback['evaluator_first_name'] . ' ' . $feedback['evaluator_last_name']
				: null;
		}
	
		return $feedbacks;
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