<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Services\UserService;
use App\Services\CohortService;
use App\Services\StudentService;
use App\Services\VacationService;
use App\Services\HolidayService;
use App\Services\UnavailabilityService;
use App\Services\FeedbackService;
use Psr\Log\LoggerInterface;

class UserManagementController
{
    private $view;
    private $userService;
    private $cohortService;
    private $studentService;
    private $vacationService;
    private $holidayService;
    private $unavailabilityService;
    private $feedbackService;
    private $logger;

    public function __construct(
        Twig $view,
        UserService $userService,
        CohortService $cohortService,
        StudentService $studentService,
        VacationService $vacationService,
        HolidayService $holidayService,
        UnavailabilityService $unavailabilityService,
        FeedbackService $feedbackService,
        LoggerInterface $logger
    ) {
        $this->view = $view;
        $this->userService = $userService;
        $this->cohortService = $cohortService;
        $this->studentService = $studentService;
        $this->vacationService = $vacationService;
        $this->holidayService = $holidayService;
        $this->unavailabilityService = $unavailabilityService;
        $this->feedbackService = $feedbackService;
        $this->logger = $logger;
    }

	public function dashboard(Request $request, Response $response): Response
	{
		$users = $this->userService->getAllUsers();
		$cohorts = $this->cohortService->getAllCohorts();
		$students = $this->studentService->getAllStudents();
		$sodFeedbacks = $this->feedbackService->getActiveSodFeedbacks();
		$standupFeedbacks = $this->feedbackService->getActiveStandupFeedbacks();
		$upcomingEvents = $this->eventService->getUpcomingEvents();
		$recentActivities = $this->activityLogService->getRecentActivities();
		
		return $this->view->render($response, 'user_management/dashboard.twig', [
			'users' => $users,
			'cohorts' => $cohorts,
			'students' => $students,
			'sod_feedbacks' => $sodFeedbacks,
			'standup_feedbacks' => $standupFeedbacks,
			'upcoming_events' => $upcomingEvents,
			'recent_activities' => $recentActivities
		]);
	}

    // Users Management
    public function usersList(Request $request, Response $response): Response
    {
        $users = $this->userService->getAllUsers();
        return $this->view->render($response, 'user_management/users_list.twig', ['users' => $users]);
    }

    public function createUser(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $result = $this->userService->createUser($data);
            if ($result) {
                return $response->withHeader('Location', '/user-management/users')->withStatus(302);
            }
        }
        return $this->view->render($response, 'user_management/create_user.twig');
    }

    public function editUser(Request $request, Response $response, array $args): Response
    {
        $userId = $args['id'];
        $user = $this->userService->getUserById($userId);
        
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $result = $this->userService->updateUser($userId, $data);
            if ($result) {
                return $response->withHeader('Location', '/user-management/users')->withStatus(302);
            }
        }
        
        return $this->view->render($response, 'user_management/edit_user.twig', ['user' => $user]);
    }

    public function deleteUser(Request $request, Response $response, array $args): Response
    {
        $userId = $args['id'];
        $result = $this->userService->deleteUser($userId);
        return $response->withHeader('Location', '/user-management/users')->withStatus(302);
    }

    public function toggleUserActive(Request $request, Response $response, array $args): Response
    {
        $userId = $args['id'];
        $result = $this->userService->toggleUserActive($userId);
        return $response->withHeader('Location', '/user-management/users')->withStatus(302);
    }

    // Cohorts Management
    public function cohortsList(Request $request, Response $response): Response
    {
        $cohorts = $this->cohortService->getAllCohorts();
        return $this->view->render($response, 'user_management/cohorts_list.twig', ['cohorts' => $cohorts]);
    }

    public function createCohort(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $result = $this->cohortService->createCohort($data);
            if ($result) {
                return $response->withHeader('Location', '/user-management/cohorts')->withStatus(302);
            }
        }
        return $this->view->render($response, 'user_management/create_cohort.twig');
    }

    public function editCohort(Request $request, Response $response, array $args): Response
    {
        $cohortId = $args['id'];
        $cohort = $this->cohortService->getCohortById($cohortId);
        
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $result = $this->cohortService->updateCohort($cohortId, $data);
            if ($result) {
                return $response->withHeader('Location', '/user-management/cohorts')->withStatus(302);
            }
        }
        
        return $this->view->render($response, 'user_management/edit_cohort.twig', ['cohort' => $cohort]);
    }

    public function deleteCohort(Request $request, Response $response, array $args): Response
    {
        $cohortId = $args['id'];
        $result = $this->cohortService->deleteCohort($cohortId);
        return $response->withHeader('Location', '/user-management/cohorts')->withStatus(302);
    }

    // Students Management
    public function studentsList(Request $request, Response $response): Response
    {
        $students = $this->studentService->getAllStudents();
        return $this->view->render($response, 'user_management/students_list.twig', ['students' => $students]);
    }

    public function createStudent(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $result = $this->studentService->createStudent($data);
            if ($result) {
                return $response->withHeader('Location', '/user-management/students')->withStatus(302);
            }
        }
        $cohorts = $this->cohortService->getAllCohorts();
        return $this->view->render($response, 'user_management/create_student.twig', ['cohorts' => $cohorts]);
    }

    public function editStudent(Request $request, Response $response, array $args): Response
    {
        $studentId = $args['id'];
        $student = $this->studentService->getStudentById($studentId);
        
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $result = $this->studentService->updateStudent($studentId, $data);
            if ($result) {
                return $response->withHeader('Location', '/user-management/students')->withStatus(302);
            }
        }
        
        $cohorts = $this->cohortService->getAllCohorts();
        return $this->view->render($response, 'user_management/edit_student.twig', ['student' => $student, 'cohorts' => $cohorts]);
    }

    public function deleteStudent(Request $request, Response $response, array $args): Response
    {
        $studentId = $args['id'];
        $result = $this->studentService->deleteStudent($studentId);
        return $response->withHeader('Location', '/user-management/students')->withStatus(302);
    }

    // Vacations Management
    public function vacationsList(Request $request, Response $response): Response
    {
        $vacations = $this->vacationService->getAllVacations();
        return $this->view->render($response, 'user_management/vacations_list.twig', ['vacations' => $vacations]);
    }

    public function createVacation(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $result = $this->vacationService->createVacation($data);
            if ($result) {
                return $response->withHeader('Location', '/user-management/vacations')->withStatus(302);
            }
        }
        $cohorts = $this->cohortService->getAllCohorts();
        return $this->view->render($response, 'user_management/create_vacation.twig', ['cohorts' => $cohorts]);
    }

    // Holidays Management
    public function holidaysList(Request $request, Response $response): Response
    {
        $holidays = $this->holidayService->getAllHolidays();
        return $this->view->render($response, 'user_management/holidays_list.twig', ['holidays' => $holidays]);
    }

    public function createHoliday(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $result = $this->holidayService->createHoliday($data);
            if ($result) {
                return $response->withHeader('Location', '/user-management/holidays')->withStatus(302);
            }
        }
        return $this->view->render($response, 'user_management/create_holiday.twig');
    }

    // Unavailabilities Management
    public function unavailabilitiesList(Request $request, Response $response): Response
    {
        $unavailabilities = $this->unavailabilityService->getAllUnavailabilities();
        return $this->view->render($response, 'user_management/unavailabilities_list.twig', ['unavailabilities' => $unavailabilities]);
    }

    public function createUnavailability(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $result = $this->unavailabilityService->createUnavailability($data);
            if ($result) {
                return $response->withHeader('Location', '/user-management/unavailabilities')->withStatus(302);
            }
        }
        $students = $this->studentService->getAllStudents();
        return $this->view->render($response, 'user_management/create_unavailability.twig', ['students' => $students]);
    }

    // Feedback Management
    public function feedbacksList(Request $request, Response $response): Response
    {
        $feedbacks = $this->feedbackService->getAllFeedbacks();
        return $this->view->render($response, 'user_management/feedbacks_list.twig', ['feedbacks' => $feedbacks]);
    }

    public function viewFeedback(Request $request, Response $response, array $args): Response
    {
        $feedbackId = $args['id'];
        $feedback = $this->feedbackService->getFeedbackById($feedbackId);
        return $this->view->render($response, 'user_management/view_feedback.twig', ['feedback' => $feedback]);
    }
}