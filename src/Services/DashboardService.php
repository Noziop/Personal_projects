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
        LoggerInterface $logger
    ) {
        $this->cohortService = $cohortService;
        $this->studentService = $studentService;
        $this->sodScheduleService = $sodScheduleService;
        $this->reportService = $reportService;
        $this->userService = $userService;
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
    try {
        $this->logger->info('Fetching dashboard data', ['user_id' => $userId]);

        $user = $this->userService->getUserById($userId);
        if (!$user) {
            throw new \Exception("User not found");
        }

        $userArray = $user->toArray();

        $dashboardData = [
            'user' => $userArray,
        ];

        if ($userArray['role'] === 'student') {
            $dashboardData += $this->getStudentDashboardData($userArray['id']);
        } elseif (in_array($userArray['role'], ['directrice', 'swe', 'ssm'])) {
            $dashboardData += $this->getAdminDashboardData();
        } else {
            $this->logger->warning('Unknown user role accessing dashboard', ['role' => $userArray['role']]);
        }

        return $dashboardData;

    } catch (\Exception $e) {
        $this->logger->error('Error in DashboardService', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
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
        $cohort = $this->cohortService->getCohortById($student['cohort_id']);
        $upcomingSOD = $this->sodScheduleService->getNextScheduledStudent(new DateTime());
        $latestReports = $this->reportService->getReportsByStudentId($student['id'], 5);

        return [
            'student' => $student,
            'cohort' => $cohort,
            'upcomingSOD' => $upcomingSOD,
            'latestReports' => $latestReports,
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
