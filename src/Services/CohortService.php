<?php

namespace App\Services;

use App\Models\Cohort;
use Psr\Log\LoggerInterface;

class CohortService
{
    private $cohortModel;
    private $logger;

    public function __construct(Cohort $cohortModel, LoggerInterface $logger)
    {
        $this->cohortModel = $cohortModel;
        $this->logger = $logger;
    }

    public function createCohort($name, $startDate, $endDate)
    {
        $this->logger->info('Creating new cohort', [
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return $this->cohortModel->create($name, $startDate, $endDate);
    }

    public function getCohortById($id)
    {
        $this->logger->info('Fetching cohort by ID', ['id' => $id]);
        return $this->cohortModel->findById($id);
    }

    public function updateCohort($id, $name, $startDate, $endDate)
    {
        $this->logger->info('Updating cohort', [
            'id' => $id,
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return $this->cohortModel->update($id, $name, $startDate, $endDate);
    }

    public function deleteCohort($id)
    {
        $this->logger->info('Deleting cohort', ['id' => $id]);
        return $this->cohortModel->delete($id);
    }

    public function getAllCohorts()
    {
        $this->logger->info('Fetching all cohorts');
        return $this->cohortModel->findAll();
    }

    public function getCohortByName($name)
    {
        $this->logger->info('Fetching cohort by name', ['name' => $name]);
        return $this->cohortModel->findByName($name);
    }

    public function getCurrentCohorts()
    {
        $this->logger->info('Fetching current cohorts');
        return $this->cohortModel->findCurrent();
    }
}
