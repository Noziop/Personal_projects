<?php
/**
 * Routes Configuration File
 *
 * This file defines all the routes for the Slim application.
 */

use Slim\App;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

//controllers

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\CohortController;
use App\Controllers\StudentController;
use App\Controllers\VacationController;
use App\Controllers\HolidayController;
use App\Controllers\SODFeedbackController;
use App\Controllers\FeedbackController;


return function (App $app) {
    $app->get('/', [AuthController::class, 'loginPage'])->setName('home');
    $app->get('/login', [AuthController::class, 'loginPage'])->setName('auth.loginPage');
    $app->post('/login', [AuthController::class, 'login'])->setName('auth.login');
    $app->get('/logout', [AuthController::class, 'logout'])->setName('auth.logout');
	$app->get('/dashboard', [DashboardController::class, 'index'])->setName('dashboard');
	$app->get('/test-error', function (Request $request, Response $response) {
		throw new \Exception("This is a test error");
	});

	//cohorts
	$app->get('/cohorts', [CohortController::class, 'index'])->setName('cohorts.index');
	$app->get('/cohorts/create', [CohortController::class, 'create'])->setName('cohorts.create');
	$app->post('/cohorts/create', [CohortController::class, 'create']);
	$app->get('/cohorts/{id}/edit', [CohortController::class, 'edit'])->setName('cohorts.edit');
	$app->post('/cohorts/{id}/edit', [CohortController::class, 'edit']);
	$app->post('/cohorts/{id}/delete', [CohortController::class, 'delete'])->setName('cohorts.delete');

	//students
	$app->get('/students', [StudentController::class, 'index'])->setName('students.index');
	$app->get('/students/create', [StudentController::class, 'create'])->setName('students.create');
	$app->post('/students/create', [StudentController::class, 'create']);
	$app->get('/students/{id}/edit', [StudentController::class, 'edit'])->setName('students.edit');
	$app->post('/students/{id}/edit', [StudentController::class, 'edit']);
	$app->post('/students/{id}/delete', [StudentController::class, 'delete'])->setName('students.delete');
	$app->get('/students/{id}/unavailability', [StudentController::class, 'manageUnavailability'])->setName('students.unavailability');
	$app->post('/students/{id}/unavailability', [StudentController::class, 'manageUnavailability']);

	// vacations
	$app->get('/vacations', [VacationController::class, 'index'])->setName('vacations.index');
	$app->get('/vacations/create', [VacationController::class, 'create'])->setName('vacations.create');
	$app->post('/vacations/create', [VacationController::class, 'create']);
	$app->get('/vacations/{id}/edit', [VacationController::class, 'edit'])->setName('vacations.edit');
	$app->post('/vacations/{id}/edit', [VacationController::class, 'edit']);
	$app->post('/vacations/{id}/delete', [VacationController::class, 'delete'])->setName('vacations.delete');

	//holidays
	$app->get('/holidays', [HolidayController::class, 'index'])->setName('holidays.index');
	$app->get('/holidays/sync', [HolidayController::class, 'sync'])->setName('holidays.sync');
	$app->post('/holidays/{id}/delete', [HolidayController::class, 'delete'])->setName('holidays.delete');

	//SODFeedback Form
	$app->get('/sod-feedback/{student_id}', [SODFeedbackController::class, 'showForm'])->setName('sod_feedback.form');
	$app->post('/sod-feedback', [SODFeedbackController::class, 'submitFeedback'])->setName('sod_feedback.submit');

	//Feedback Manager
	$app->get('/feedback/manage', [FeedbackController::class, 'manage'])->setName('feedback.manage');
};
