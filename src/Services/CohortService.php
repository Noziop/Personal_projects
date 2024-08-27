<?php

namespace App\Services;

use App\Models\Cohort;
use App\Models\DrawingDay;
use Psr\Log\LoggerInterface;
use DateTime;

class CohortService
{
    private $cohortModel;
    private $drawingDayModel;
    private $logger;

    public function __construct(Cohort $cohortModel, DrawingDay $drawingDayModel, LoggerInterface $logger)
    {
        $this->cohortModel = $cohortModel;
        $this->drawingDayModel = $drawingDayModel;
        $this->logger = $logger;
    }

	public function getAllCohorts()
	{
		$cohorts = $this->cohortModel->findAll();
		foreach ($cohorts as $cohort) {
			$cohort->setDrawingDays($this->drawingDayModel->findByCohort($cohort->getId()));
		}
		return $cohorts;
	}

	public function getCohortById($cohortId)
	{
		$this->logger->info('Fetching cohort by ID', ['cohort_id' => $cohortId]);
		$cohort = $this->cohortModel->findById($cohortId);
		if ($cohort instanceof Cohort) {
			$cohort->setDrawingDays($this->drawingDayModel->findByCohort($cohort->getId()));
			return $cohort;
		}
		return null;
	}

	public function createCohort($name, $startDate, $endDate, $drawingDays)
	{
		$cohortId = $this->cohortModel->create($name, $startDate, $endDate);
		if ($cohortId) {
			$this->updateDrawingDays($cohortId, $drawingDays);
			return $this->getCohortById($cohortId);
		}
		return null;
	}

    public function updateCohort($id, $name, $startDate, $endDate, $drawingDays)
    {
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
        $updated = $this->cohortModel->update($id, $name, $startDateTime, $endDateTime);
        
        if ($updated) {
            $this->updateDrawingDays($id, $drawingDays);
        }
        
        return $updated;
    }

    public function deleteCohort($id)
    {
        // La suppression des jours de tirage associés devrait être gérée par la contrainte ON DELETE CASCADE dans la base de données
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

    private function getDrawingDaysForCohort($cohortId)
    {
        return $this->drawingDayModel->findByCohort($cohortId);
    }

    private function updateDrawingDays($cohortId, $drawingDays)
    {
        // Supprimer les jours de tirage existants
        $this->drawingDayModel->deleteByCohort($cohortId);

        // Ajouter les nouveaux jours de tirage
        foreach ($drawingDays as $day) {
            $this->drawingDayModel->create($cohortId, $day);
        }
    }
}