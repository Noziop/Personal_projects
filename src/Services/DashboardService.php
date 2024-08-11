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
		if (!$student) {
			$this->logger->error('Student not found for user', ['user_id' => $userId]);
			return null;
		}
	
		$cohort = $this->cohortService->getCohortById($student->getCohortId());
		$nextSOD = $this->sodScheduleService->getNextSODForStudent($student->getId());
		$lastReport = $this->reportService->getLastReportForStudent($student->getId());
		$unavailabilities = $this->studentService->getUnavailabilityForStudent($student->getId());
		$upcomingVacations = $this->vacationService->getUpcomingVacationsForCohort($student->getCohortId());
	
		return [
			'student' => [
				'id' => $student->getId(),
				'name' => $student->getFirstName() . ' ' . $student->getLastName(),
				'email' => $student->getEmail(),
				'slack_id' => $student->getSlackId(),
				'cohort' => $cohort ? $cohort->getName() : 'N/A',
			],
			'nextSOD' => $nextSOD ? [
				'date' => $nextSOD->getDate(),
				'isPresenter' => $nextSOD->getStudentId() === $student->getId(),
			] : null,
			'lastReport' => $lastReport ? [
				'type' => $lastReport->getType(),
				'date' => $lastReport->getCreatedAt(),
				'content' => substr($lastReport->getContent(), 0, 100) . '...',
			] : null,
			'unavailabilities' => array_map(function($unavailability) {
				return [
					'start_date' => $unavailability->getStartDate(),
					'end_date' => $unavailability->getEndDate(),
				];
			}, $unavailabilities),
			'upcomingVacations' => array_map(function($vacation) {
				return [
					'start_date' => $vacation->getStartDate(),
					'end_date' => $vacation->getEndDate(),
				];
			}, $upcomingVacations),
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
