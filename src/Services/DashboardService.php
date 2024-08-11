<?php
/**
 * Dashboard Service
 *
 * This service handles the aggregation of data for the dashboard,
 * pulling information from various other services based on user role.
 */

namespace App\Services;

use Psr\Log\LoggerInterface;
use DateTime;

class DashboardService
{
    private $cohortService;
    private $studentService;
    private $sodScheduleService;
    private $reportService;
    private $userService;
	private $vacationService;
    private $logger;

    /**
     * DashboardService constructor.
     *
     * @param CohortService $cohortService
     * @param StudentService $studentService
     * @param SODScheduleService $sodScheduleService
     * @param ReportService $reportService
     * @param UserService $userService
     * @param LoggerInterface $logger
     */
    public function __construct(
        CohortService $cohortService,
        StudentService $studentService,
        SODScheduleService $sodScheduleService,
        ReportService $reportService,
        UserService $userService,
		VacationService $vacationService,
        LoggerInterface $logger
    ) {
        $this->cohortService = $cohortService;
        $this->studentService = $studentService;
        $this->sodScheduleService = $sodScheduleService;
        $this->reportService = $reportService;
        $this->userService = $userService;
		$this->vacationService = $vacationService;
        $this->logger = $logger;
    }

    /**
     * Get dashboard data for a specific user
     *
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public function getDashboardData($userId)
	{
		$user = $this->userService->getUserById($userId);
		if (!$user) {
			$this->logger->error('User not found', ['user_id' => $userId]);
			return null;
		}

		switch ($user['role']) {
			case 'directrice':
			case 'swe':
			case 'ssm':
				return $this->getAdminDashboardData();
			case 'student':
				return $this->getStudentDashboardData($userId);
			default:
				$this->logger->error('Invalid user role', ['user_id' => $userId, 'role' => $user['role']]);
				return null;
		}
	}



    /**
     * Get dashboard data for student role
     *
     * @param int $userId
     * @return array
     */
	private function getStudentDashboardData($userId)
	{
		$student = $this->studentService->getStudentByUserId($userId);
		if (!is_array($student) || empty($student)) {
			$this->logger->error('Invalid or empty student data', ['user_id' => $userId]);
			return null;
		}
		if (!$student) {
			$this->logger->error('Student not found for user', ['user_id' => $userId]);
			return null;
		}
	
		$cohort = $this->cohortService->getCohortById($student['cohort_id']);
		$nextSOD = $this->sodScheduleService->getNextSODForStudent($student['id']);
		$lastReport = $this->reportService->getLastReportForStudent($student['id']);
		$unavailabilities = $this->studentService->getUnavailabilityForStudent($student['id']);
		$upcomingVacations = $this->vacationService->getUpcomingVacationsForCohort($student['cohort_id']);
	
		return [
			'student' => [
				'id' => $student['id'],
				'name' => ($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''),
				'email' => $student['email'] ?? null,
				'slack_id' => $student['slack_id'] ?? null,
				'cohort' => is_array($cohort) ? $cohort['name'] : (is_object($cohort) ? $cohort->getName() : 'N/A'),
			],
			'nextSOD' => $nextSOD ? [
				'date' => is_array($nextSOD) ? $nextSOD['date'] : $nextSOD->getDate(),
				'isPresenter' => is_array($nextSOD) ? ($nextSOD['student_id'] === $student['id']) : ($nextSOD->getStudentId() === $student['id']),
			] : null,
			'lastReport' => $lastReport ? [
				'type' => is_array($lastReport) ? $lastReport['type'] : $lastReport->getType(),
				'date' => is_array($lastReport) ? $lastReport['created_at'] : $lastReport->getCreatedAt(),
				'content' => is_array($lastReport) ? substr($lastReport['content'], 0, 100) : substr($lastReport->getContent(), 0, 100) . '...',
			] : null,
			'unavailabilities' => $unavailabilities,
			'upcomingVacations' => $upcomingVacations,
		];
	}



    /**
     * Get dashboard data for admin roles
     *
     * @return array
     */
    private function getAdminDashboardData()
    {
        $activeCohorts = $this->cohortService->getCurrentCohorts();
        $totalStudents = $this->studentService->getTotalStudentsCount();
        $upcomingSODs = $this->sodScheduleService->getScheduleEntriesByDateRange(new DateTime(), (new DateTime())->modify('+1 week'));
        $recentReports = $this->reportService->getRecentReports(10);

        return [
            'activeCohorts' => $activeCohorts,
            'totalStudents' => $totalStudents,
            'upcomingSODs' => $upcomingSODs,
            'recentReports' => $recentReports,
        ];
    }
}
