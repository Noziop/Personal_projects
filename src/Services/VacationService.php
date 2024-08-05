<?php

namespace App\Services;

use App\Models\Vacation;
use Psr\Log\LoggerInterface;

class VacationService
{
    private $vacationModel;
    private $logger;

    public function __construct(Vacation $vacationModel, LoggerInterface $logger)
    {
        $this->vacationModel = $vacationModel;
        $this->logger = $logger;
    }

    public function createVacation($cohortId, $startDate, $endDate)
    {
        $this->logger->info('Creating new vacation period', [
            'cohort_id' => $cohortId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return $this->vacationModel->create($cohortId, $startDate, $endDate);
    }

    public function getVacationById($id)
    {
        $this->logger->info('Fetching vacation period by ID', ['id' => $id]);
        return $this->vacationModel->findById($id);
    }

    public function updateVacation($id, $cohortId, $startDate, $endDate)
    {
        $this->logger->info('Updating vacation period', [
            'id' => $id,
            'cohort_id' => $cohortId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return $this->vacationModel->update($id, $cohortId, $startDate, $endDate);
    }

    public function deleteVacation($id)
    {
        $this->logger->info('Deleting vacation period', ['id' => $id]);
        return $this->vacationModel->delete($id);
    }

    public function getAllVacations()
    {
        $this->logger->info('Fetching all vacation periods');
        return $this->vacationModel->findAll();
    }

    public function getVacationsByCohort($cohortId)
    {
        $this->logger->info('Fetching vacation periods by cohort', ['cohort_id' => $cohortId]);
        return $this->vacationModel->findByCohort($cohortId);
    }

    public function getVacationsByDateRange($startDate, $endDate)
    {
        $this->logger->info('Fetching vacation periods by date range', [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        return $this->vacationModel->findByDateRange($startDate, $endDate);
    }

    public function isDateInVacation($cohortId, $date)
    {
        $this->logger->info('Checking if date is in vacation period', [
            'cohort_id' => $cohortId,
            'date' => $date
        ]);
        return $this->vacationModel->isDateInVacation($cohortId, $date);
    }

    public function getNextVacation($cohortId, $fromDate)
    {
        $this->logger->info('Fetching next vacation period', [
            'cohort_id' => $cohortId,
            'from_date' => $fromDate
        ]);
        return $this->vacationModel->findNextVacation($cohortId, $fromDate);
    }
}
