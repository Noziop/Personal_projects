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
        $this->logger->info('Creating new standup feedback', ['cohort_id' => $data['cohort_id']]);
        return $this->standupFeedbackModel->create($data);
    }

    public function getFeedbackById($id)
    {
        $this->logger->info('Fetching standup feedback', ['id' => $id]);
        return $this->standupFeedbackModel->findById($id);
    }

    public function getFeedbackByCohortAndDate($cohortId, $date)
    {
        $this->logger->info('Fetching standup feedback by cohort and date', ['cohort_id' => $cohortId, 'date' => $date]);
        return $this->standupFeedbackModel->findByCohortAndDate($cohortId, $date);
    }

    public function getFeedbackByDateRange($startDate, $endDate)
    {
        $this->logger->info('Fetching standup feedback by date range', ['start_date' => $startDate, 'end_date' => $endDate]);
        return $this->standupFeedbackModel->findByDateRange($startDate, $endDate);
    }

    public function updateFeedback($id, array $data)
    {
        $this->logger->info('Updating standup feedback', ['id' => $id]);
        return $this->standupFeedbackModel->update($id, $data);
    }

    public function deleteFeedback($id)
    {
        $this->logger->info('Deleting standup feedback', ['id' => $id]);
        return $this->standupFeedbackModel->delete($id);
    }
}