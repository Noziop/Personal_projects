<?php

namespace App\Services;

use App\Models\SODFeedback;
use Psr\Log\LoggerInterface;
class SODFeedbackService
{
    private $sodFeedbackModel;
    private $logger;

    public function __construct(SODFeedback $sodFeedbackModel, LoggerInterface $logger)
    {
        $this->sodFeedbackModel = $sodFeedbackModel;
        $this->logger = $logger;
    }



    public function createFeedback(array $feedbackData)
    {
        $this->logger->info('Creating new SOD feedback', ['student_id' => $feedbackData['student_id']]);
        return $this->sodFeedbackModel->create($feedbackData);
    }

    // Ajoutez d'autres méthodes si nécessaire
}
