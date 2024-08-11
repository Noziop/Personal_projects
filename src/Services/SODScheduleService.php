<?php

namespace App\Services;

use App\Models\SODSchedule;
use Psr\Log\LoggerInterface;
use DateTime;

class SODScheduleService
{
    private $scheduleModel;
    private $logger;

    public function __construct(SODSchedule $scheduleModel, LoggerInterface $logger)
    {
        $this->scheduleModel = $scheduleModel;
        $this->logger = $logger;
    }

    public function createScheduleEntry($studentId, DateTime $date)
    {
        $this->logger->info('Creating new SOD schedule entry', [
            'student_id' => $studentId,
            'date' => $date->format('Y-m-d')
        ]);

        return $this->scheduleModel->create($studentId, $date);
    }

    public function getScheduleEntryById($id)
    {
        $this->logger->info('Fetching SOD schedule entry by ID', ['id' => $id]);
        return $this->scheduleModel->findById($id);
    }

    public function updateScheduleEntry($id, $studentId, DateTime $date)
    {
        $this->logger->info('Updating SOD schedule entry', [
            'id' => $id,
            'student_id' => $studentId,
            'date' => $date->format('Y-m-d')
        ]);

        return $this->scheduleModel->update($id, $studentId, $date);
    }

    public function deleteScheduleEntry($id)
    {
        $this->logger->info('Deleting SOD schedule entry', ['id' => $id]);
        return $this->scheduleModel->delete($id);
    }

    public function getAllScheduleEntries()
    {
        $this->logger->info('Fetching all SOD schedule entries');
        return $this->scheduleModel->findAll();
    }

    public function getScheduleEntriesByStudentId($studentId)
    {
        $this->logger->info('Fetching SOD schedule entries by student ID', ['student_id' => $studentId]);
        return $this->scheduleModel->findByStudentId($studentId);
    }

    public function getScheduleEntriesByDate(DateTime $date)
    {
        $this->logger->info('Fetching SOD schedule entries by date', ['date' => $date->format('Y-m-d')]);
        return $this->scheduleModel->findByDate($date);
    }

    public function getScheduleEntriesByDateRange(DateTime $startDate, DateTime $endDate)
    {
        $this->logger->info('Fetching SOD schedule entries by date range', [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);
        return $this->scheduleModel->findByDateRange($startDate, $endDate);
    }

    public function getNextScheduledStudent(DateTime $fromDate)
    {
        $this->logger->info('Fetching next scheduled student', ['from_date' => $fromDate->format('Y-m-d')]);
        return $this->scheduleModel->findNextScheduledStudent($fromDate);
    }

    public function isStudentScheduledOnDate($studentId, DateTime $date)
    {
        $this->logger->info('Checking if student is scheduled on date', [
            'student_id' => $studentId,
            'date' => $date->format('Y-m-d')
        ]);
        return $this->scheduleModel->isStudentScheduledOnDate($studentId, $date);
    }

	public function getNextSODForStudent($studentId)
	{
		$this->logger->info('Fetching next SOD for student', ['student_id' => $studentId]);
		$currentDate = new DateTime();
		return $this->scheduleModel->findNextForStudent($studentId, $currentDate);
	}
}
