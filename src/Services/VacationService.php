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

    public function getAllVacations()
    {
        $this->logger->info('Fetching all vacation periods');
        return $this->vacationModel->findAll();
    }

    public function getVacationById($id)
    {
        $this->logger->info('Fetching vacation by ID', ['id' => $id]);
        return $this->vacationModel->findById($id);
    }

    public function createVacation($cohortId, $startDate, $endDate)
    {
        $this->logger->info('Creating new vacation', [
            'cohort_id' => $cohortId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        return $this->vacationModel->create($cohortId, $startDate, $endDate);
    }

    public function updateVacation($id, $cohortId, $startDate, $endDate)
    {
        $this->logger->info('Updating vacation', [
            'id' => $id,
            'cohort_id' => $cohortId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        return $this->vacationModel->update($id, $cohortId, $startDate, $endDate);
    }

    public function deleteVacation($id)
    {
        $this->logger->info('Deleting vacation', ['id' => $id]);
        return $this->vacationModel->delete($id);
    }
}