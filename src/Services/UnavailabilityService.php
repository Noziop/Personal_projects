<?php

namespace App\Services;

use App\Models\Unavailability;
use Psr\Log\LoggerInterface;

class UnavailabilityService
{
    private $unavailabilityModel;
    private $logger;

    public function __construct(Unavailability $unavailabilityModel, LoggerInterface $logger)
    {
        $this->unavailabilityModel = $unavailabilityModel;
        $this->logger = $logger;
    }

    public function createUnavailability($studentId, $startDate, $endDate)
    {
        $this->logger->info('Creating new unavailability', [
            'student_id' => $studentId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return $this->unavailabilityModel->create($studentId, $startDate, $endDate);
    }

    public function getUnavailabilityById($id)
    {
        $this->logger->info('Fetching unavailability by ID', ['id' => $id]);
        return $this->unavailabilityModel->findById($id);
    }

    public function updateUnavailability($id, $studentId, $startDate, $endDate)
    {
        $this->logger->info('Updating unavailability', [
            'id' => $id,
            'student_id' => $studentId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return $this->unavailabilityModel->update($id, $studentId, $startDate, $endDate);
    }

    public function deleteUnavailability($id)
    {
        $this->logger->info('Deleting unavailability', ['id' => $id]);
        return $this->unavailabilityModel->delete($id);
    }

    public function getAllUnavailabilities()
    {
        $this->logger->info('Fetching all unavailabilities');
        return $this->unavailabilityModel->findAll();
    }

    public function getUnavailabilitiesByStudentId($studentId)
    {
        $this->logger->info('Fetching unavailabilities by student ID', ['student_id' => $studentId]);
        return $this->unavailabilityModel->findByStudentId($studentId);
    }

    public function getUnavailabilitiesByDateRange($startDate, $endDate)
    {
        $this->logger->info('Fetching unavailabilities by date range', [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        return $this->unavailabilityModel->findByDateRange($startDate, $endDate);
    }

    public function isStudentAvailable($studentId, $date)
    {
        $this->logger->info('Checking student availability', [
            'student_id' => $studentId,
            'date' => $date
        ]);
        return $this->unavailabilityModel->isStudentAvailable($studentId, $date);
    }

    public function getAvailableStudents($date)
    {
        $this->logger->info('Fetching available students for date', ['date' => $date]);
        return $this->unavailabilityModel->getAvailableStudents($date);
    }
}
