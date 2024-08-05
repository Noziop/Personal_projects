<?php

namespace App\Services;

use App\Models\Cohort;
use Psr\Log\LoggerInterface;
use DateTime;

class CohortService
{
    private $cohortModel;
    private $logger;

    public function __construct(Cohort $cohortModel, LoggerInterface $logger)
    {
        $this->cohortModel = $cohortModel;
        $this->logger = $logger;
    }

    public function getAllCohorts()
    {
        return $this->cohortModel->findAll();
    }

    public function getCohortById($id)
    {
        return $this->cohortModel->findById($id);
    }

    public function createCohort($name, $startDate, $endDate)
    {
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
        return $this->cohortModel->create($name, $startDateTime, $endDateTime);
    }

    public function updateCohort($id, $name, $startDate, $endDate)
    {
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
        return $this->cohortModel->update($id, $name, $startDateTime, $endDateTime);
    }

    public function deleteCohort($id)
    {
        return $this->cohortModel->delete($id);
    }

    public function getCurrentCohorts()
    {
        return $this->cohortModel->findCurrent();
    }

    public function getFutureCohorts()
    {
        return $this->cohortModel->findFuture();
    }

    public function getPastCohorts()
    {
        return $this->cohortModel->findPast();
    }
}
