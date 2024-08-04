<?php

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

    public function getDashboardData($userId)
    {
        $this->logger->info('Fetching dashboard data', ['user_id' => $userId]);

        $user = $this->userService->getUserById($userId);
        $dashboardData = [
            'user' => $user,
        ];

        if ($user['role'] === 'student') {
            $student = $this->studentService->getStudentByUserId($userId);
            $cohort = $this->cohortService->getCohortById($student['cohort_id']);
            $upcomingSOD = $this->sodScheduleService->getNextScheduledStudent(new DateTime());
            $latestReports = $this->reportService->getReportsByStudentId($student['id'], 5);

            $dashboardData += [
                'student' => $student,
                'cohort' => $cohort,
                'upcomingSOD' => $upcomingSOD,
                'latestReports' => $latestReports,
            ];
        } elseif (in_array($user['role'], ['directrice', 'swe', 'ssm'])) {
            $activeCohorts = $this->cohortService->getCurrentCohorts();
            $totalStudents = $this->studentService->getTotalStudentsCount();
            $upcomingSODs = $this->sodScheduleService->getScheduleEntriesByDateRange(new DateTime(), (new DateTime())->modify('+1 week'));
            $recentReports = $this->reportService->getRecentReports(10);

            $dashboardData += [
                'activeCohorts' => $activeCohorts,
                'totalStudents' => $totalStudents,
                'upcomingSODs' => $upcomingSODs,
                'recentReports' => $recentReports,
            ];
        }

        return $dashboardData;
    }
}
