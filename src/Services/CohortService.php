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

    public function createCohort($name, DateTime $startDate, DateTime $endDate)
    {
        $this->logger->info('Creating new cohort', [
            'name' => $name,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);

        return $this->cohortModel->create($name, $startDate, $endDate);
    }

    public function getCohortById($id)
    {
        $this->logger->info('Fetching cohort by ID', ['id' => $id]);
        return $this->cohortModel->findById($id);
    }

    public function getCohortByName($name)
    {
        $this->logger->info('Fetching cohort by name', ['name' => $name]);
        return $this->cohortModel->findByName($name);
    }

    public function updateCohort($id, $name, DateTime $startDate, DateTime $endDate)
    {
        $this->logger->info('Updating cohort', [
            'id' => $id,
            'name' => $name,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
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

    public function getCurrentCohorts()
    {
        $this->logger->info('Fetching current cohorts');
        return $this->cohortModel->findCurrent();
    }

    public function getFutureCohorts()
    {
        $this->logger->info('Fetching future cohorts');
        return $this->cohortModel->findFuture();
    }

    public function getPastCohorts()
    {
        $this->logger->info('Fetching past cohorts');
        return $this->cohortModel->findPast();
    }

    public function getStudentsInCohort($cohortId)
    {
        $this->logger->info('Fetching students in cohort', ['cohort_id' => $cohortId]);
        return $this->cohortModel->getStudents($cohortId);
    }

    public function addStudentToCohort($cohortId, $studentId)
    {
        $this->logger->info('Adding student to cohort', [
            'cohort_id' => $cohortId,
            'student_id' => $studentId
        ]);
        return $this->cohortModel->addStudent($cohortId, $studentId);
    }

    public function removeStudentFromCohort($cohortId, $studentId)
    {
        $this->logger->info('Removing student from cohort', [
            'cohort_id' => $cohortId,
            'student_id' => $studentId
        ]);
        return $this->cohortModel->removeStudent($cohortId, $studentId);
    }
}
