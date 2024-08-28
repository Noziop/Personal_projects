<?php

namespace App\Services;

use App\Models\StandupFeedback;
use Psr\Log\LoggerInterface;

class StandupFeedbackService
{
    private $standupFeedbackModel;
    private $logger;

    public function __construct(StandupFeedback $standupFeedbackModel, LoggerInterface $logger)
    {
        $this->standupFeedbackModel = $standupFeedbackModel;
        $this->logger = $logger;
    }

    public function createFeedback(array $data)
    {
        $this->logger->info('Creating new standup feedback', ['student_id' => $data['student_id']]);
        return $this->standupFeedbackModel->create($data);
    }

    public function getFeedbackById($id)
    {
        return $this->standupFeedbackModel->findById($id);
    }

    public function getAllFeedbacks()
    {
        return $this->standupFeedbackModel->findAll();
    }

    // Ajoutez d'autres méthodes selon vos besoins
}