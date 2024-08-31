<?php

namespace App\Services;

use App\Models\Feedback;
use App\Models\Student;
use App\Models\User;
use App\Models\StandupFeedback;
use App\Models\Cohort;
use Psr\Log\LoggerInterface;

class FeedbackService
{
    private $feedbackModel;
    private $studentModel;
    private $userModel;
    private $standupFeedbackModel;
    private $cohortModel;
    private $logger;

    public function __construct(
        Feedback $feedbackModel,
        Student $studentModel,
        User $userModel,
        StandupFeedback $standupFeedbackModel,
        Cohort $cohortModel,
        LoggerInterface $logger
    ) {
        $this->feedbackModel = $feedbackModel;
        $this->studentModel = $studentModel;
        $this->userModel = $userModel;
        $this->standupFeedbackModel = $standupFeedbackModel;
        $this->cohortModel = $cohortModel;
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
		
		if ($type === 'SOD') {
			$feedback = $this->feedbackModel->findById($id, $type);
			if ($feedback) {
				$feedback['type'] = 'SOD';
				$feedback['content'] = json_decode($feedback['content'], true);
				$student = $this->studentModel->findById($feedback['student_id']);
				$evaluator = $this->userModel->findById($feedback['evaluator_id']);
				$feedback['student_name'] = $student ? $student['first_name'] . ' ' . $student['last_name'] : 'Unknown';
				$feedback['evaluator_name'] = $evaluator ? $evaluator['first_name'] . ' ' . $evaluator['last_name'] : 'Unknown';
			}
		} elseif ($type === 'Stand-up') {
			$feedback = $this->standupFeedbackModel->findById($id);
			if ($feedback) {
				$feedback['type'] = 'Stand-up';
				$feedback['content'] = json_decode($feedback['content'], true);
				$cohort = $this->cohortModel->findById($feedback['cohort_id']);
				$feedback['cohort_name'] = $cohort ? $cohort->getName() : 'Unknown';
	
				// Extraire les informations générales du stand-up
				$generalInfo = [
					'scrum_master_names' => $feedback['scrum_master_names'] ?? '',
					'bugs_report' => $feedback['bugs_report'] ?? '',
					'cohort_difficulties' => $feedback['cohort_difficulties'] ?? '',
					'shared_tips' => $feedback['shared_tips'] ?? '',
					'conclusion' => $feedback['conclusion'] ?? '',
					'other_reports' => $feedback['other_reports'] ?? ''
				];
				$feedback['general_info'] = $generalInfo;
	
				// Traiter les données des étudiants
				$studentDetails = [];
				foreach ($feedback['content'] as $studentId => $studentData) {
					if (is_numeric($studentId)) {
						$student = $this->studentModel->findById($studentId);
						if ($student) {
							$user = $this->userModel->findById($student['user_id']);
							$studentData['student_name'] = $user ? $user['first_name'] . ' ' . $user['last_name'] : 'Unknown';
							$studentDetails[$studentId] = $studentData;
						}
					}
				}
				$feedback['student_details'] = $studentDetails;
			}
		} else {
			$this->logger->warning('Invalid feedback type', ['type' => $type]);
			return null;
		}
	
		if (!$feedback) {
			$this->logger->warning('Feedback not found', ['id' => $id, 'type' => $type]);
			return null;
		}
	
		return $feedback;
	}

    public function getAllStudents()
    {
        return $this->studentModel->findAll();
    }

    // Ajoutez d'autres méthodes si nécessaire
}