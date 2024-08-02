<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response) {
        return $this->get('view')->render($response, 'home/index.twig');
    });

    $app->group('/cohorts', function (Group $group) {
        $group->get('', \App\Controllers\CohortController::class . ':getAllCohorts');
        $group->get('/{id}', \App\Controllers\CohortController::class . ':getCohort');
        $group->post('', \App\Controllers\CohortController::class . ':createCohort');
        $group->put('/{id}', \App\Controllers\CohortController::class . ':updateCohort');
        $group->delete('/{id}', \App\Controllers\CohortController::class . ':deleteCohort');
    });

    $app->group('/students', function (Group $group) {
        $group->get('', \App\Controllers\StudentController::class . ':getAllStudents');
        $group->get('/{id}', \App\Controllers\StudentController::class . ':getStudent');
        $group->post('', \App\Controllers\StudentController::class . ':createStudent');
        $group->put('/{id}', \App\Controllers\StudentController::class . ':updateStudent');
        $group->delete('/{id}', \App\Controllers\StudentController::class . ':deleteStudent');
    });

    $app->group('/drawings', function (Group $group) {
        $group->get('', \App\Controllers\DrawingController::class . ':getAllDrawings');
        $group->get('/{id}', \App\Controllers\DrawingController::class . ':getDrawing');
        $group->post('', \App\Controllers\DrawingController::class . ':performDrawing');
        $group->delete('/{id}', \App\Controllers\DrawingController::class . ':deleteDrawing');
    });

    $app->group('/unavailabilities', function (Group $group) {
        $group->get('', \App\Controllers\UnavailabilityController::class . ':getAllUnavailabilities');
        $group->get('/{id}', \App\Controllers\UnavailabilityController::class . ':getUnavailability');
        $group->post('', \App\Controllers\UnavailabilityController::class . ':createUnavailability');
        $group->put('/{id}', \App\Controllers\UnavailabilityController::class . ':updateUnavailability');
        $group->delete('/{id}', \App\Controllers\UnavailabilityController::class . ':deleteUnavailability');
    });

    $app->group('/vacations', function (Group $group) {
        $group->get('', \App\Controllers\VacationController::class . ':getAllVacations');
        $group->get('/{id}', \App\Controllers\VacationController::class . ':getVacation');
        $group->post('', \App\Controllers\VacationController::class . ':createVacation');
        $group->put('/{id}', \App\Controllers\VacationController::class . ':updateVacation');
        $group->delete('/{id}', \App\Controllers\VacationController::class . ':deleteVacation');
    });
};