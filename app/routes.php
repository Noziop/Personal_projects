<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Middleware\AuthMiddleware;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response) {
        return $this->get('view')->render($response, 'home/index.twig');
    })->setName('home');

    $app->group('/cohorts', function (Group $group) {
        $group->get('', \App\Controllers\CohortController::class . ':getAllCohorts')->setName('cohorts.index');
        $group->get('/{id}', \App\Controllers\CohortController::class . ':getCohort')->setName('cohorts.view');
        $group->post('', \App\Controllers\CohortController::class . ':createCohort')->setName('cohorts.create');
        $group->put('/{id}', \App\Controllers\CohortController::class . ':updateCohort')->setName('cohorts.update');
        $group->delete('/{id}', \App\Controllers\CohortController::class . ':deleteCohort')->setName('cohorts.delete');
    })->add(AuthMiddleware::requireRole(['directrice', 'swe', 'ssm']));

    $app->group('/students', function (Group $group) {
        $group->get('', \App\Controllers\StudentController::class . ':getAllStudents')->setName('students.index');
        $group->get('/{id}', \App\Controllers\StudentController::class . ':getStudent')->setName('students.view');
        $group->post('', \App\Controllers\StudentController::class . ':createStudent')->setName('students.create');
        $group->put('/{id}', \App\Controllers\StudentController::class . ':updateStudent')->setName('students.update');
        $group->delete('/{id}', \App\Controllers\StudentController::class . ':deleteStudent')->setName('students.delete');
    })->add(AuthMiddleware::requireRole(['directrice', 'swe', 'ssm']));

    $app->group('/drawings', function (Group $group) {
        $group->get('', \App\Controllers\DrawingController::class . ':getAllDrawings')->setName('drawings.index');
        $group->get('/{id}', \App\Controllers\DrawingController::class . ':getDrawing')->setName('drawings.view');
        $group->post('', \App\Controllers\DrawingController::class . ':performDrawing')->setName('drawings.perform');
        $group->delete('/{id}', \App\Controllers\DrawingController::class . ':deleteDrawing')->setName('drawings.delete');
    })->add(AuthMiddleware::requireRole(['directrice', 'swe', 'ssm']));

    $app->group('/unavailabilities', function (Group $group) {
        $group->get('', \App\Controllers\UnavailabilityController::class . ':getAllUnavailabilities')->setName('unavailabilities.index');
        $group->get('/{id}', \App\Controllers\UnavailabilityController::class . ':getUnavailability')->setName('unavailabilities.view');
        $group->post('', \App\Controllers\UnavailabilityController::class . ':createUnavailability')->setName('unavailabilities.create');
        $group->put('/{id}', \App\Controllers\UnavailabilityController::class . ':updateUnavailability')->setName('unavailabilities.update');
        $group->delete('/{id}', \App\Controllers\UnavailabilityController::class . ':deleteUnavailability')->setName('unavailabilities.delete');
    })->add(AuthMiddleware::requireRole(['directrice', 'swe', 'ssm', 'student']));

    $app->group('/vacations', function (Group $group) {
        $group->get('', \App\Controllers\VacationController::class . ':getAllVacations')->setName('vacations.index');
        $group->get('/{id}', \App\Controllers\VacationController::class . ':getVacation')->setName('vacations.view');
        $group->post('', \App\Controllers\VacationController::class . ':createVacation')->setName('vacations.create');
        $group->put('/{id}', \App\Controllers\VacationController::class . ':updateVacation')->setName('vacations.update');
        $group->delete('/{id}', \App\Controllers\VacationController::class . ':deleteVacation')->setName('vacations.delete');
    })->add(AuthMiddleware::requireRole(['directrice', 'swe', 'ssm']));

    $app->group('/users', function (Group $group) {
        $group->get('', \App\Controllers\UserController::class . ':index')->setName('users.index');
        $group->get('/create', \App\Controllers\UserController::class . ':create')->setName('users.create');
        $group->post('', \App\Controllers\UserController::class . ':create');
        $group->get('/{id}', \App\Controllers\UserController::class . ':show')->setName('users.show');
        $group->get('/{id}/edit', \App\Controllers\UserController::class . ':edit')->setName('users.edit');
        $group->put('/{id}', \App\Controllers\UserController::class . ':edit');
        $group->delete('/{id}', \App\Controllers\UserController::class . ':delete')->setName('users.delete');
    })->add(AuthMiddleware::requireRole(['directrice', 'swe']));

    $app->get('/tirage', \App\Controllers\DrawingController::class . ':index')->setName('tirage.index')
        ->add(AuthMiddleware::requireRole(['directrice', 'swe', 'ssm']));

    // Routes pour l'authentification
    $app->get('/login', \App\Controllers\AuthController::class . ':loginForm')->setName('auth.loginForm');
    $app->post('/login', \App\Controllers\AuthController::class . ':login')->setName('auth.login');
    $app->get('/logout', \App\Controllers\AuthController::class . ':logout')->setName('auth.logout');
};
