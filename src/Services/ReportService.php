<?php

namespace App\Services;

use App\Models\Report;
use Psr\Log\LoggerInterface;

class ReportService
{
    private $reportModel;
    private $logger;

    public function __construct(Report $reportModel, LoggerInterface $logger)
    {
        $this->reportModel = $reportModel;
        $this->logger = $logger;
    }

    public function createReport($studentId, $type, $content)
    {
        $this->logger->info('Creating new report', [
            'student_id' => $studentId,
            'type' => $type,
            'content' => substr($content, 0, 50) . '...' // Log only the first 50 characters of content
        ]);

        return $this->reportModel->create($studentId, $type, $content);
    }

    public function getReportById($id)
    {
        $this->logger->info('Fetching report by ID', ['id' => $id]);
        return $this->reportModel->findById($id);
    }

    public function updateReport($id, $studentId, $type, $content)
    {
        $this->logger->info('Updating report', [
            'id' => $id,
            'student_id' => $studentId,
            'type' => $type,
            'content' => substr($content, 0, 50) . '...' // Log only the first 50 characters of content
        ]);

        return $this->reportModel->update($id, $studentId, $type, $content);
    }

    public function deleteReport($id)
    {
        $this->logger->info('Deleting report', ['id' => $id]);
        return $this->reportModel->delete($id);
    }

    public function getAllReports()
    {
        $this->logger->info('Fetching all reports');
        return $this->reportModel->findAll();
    }

    public function getReportsByStudentId($studentId)
    {
        $this->logger->info('Fetching reports by student ID', ['student_id' => $studentId]);
        return $this->reportModel->findByStudentId($studentId);
    }

    public function getReportsByType($type)
    {
        $this->logger->info('Fetching reports by type', ['type' => $type]);
        return $this->reportModel->findByType($type);
    }

    public function getReportsByDateRange($startDate, $endDate)
    {
        $this->logger->info('Fetching reports by date range', [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);
        return $this->reportModel->findByDateRange($startDate, $endDate);
    }

    public function getLatestReportByStudentAndType($studentId, $type)
    {
        $this->logger->info('Fetching latest report by student and type', [
            'student_id' => $studentId,
            'type' => $type
        ]);
        return $this->reportModel->findLatestByStudentAndType($studentId, $type);
    }

	public function getRecentReports($limit = 10)
	{
		$this->logger->info('Fetching recent reports', ['limit' => $limit]);
		return $this->reportModel->getRecentReports($limit);
	}

}
