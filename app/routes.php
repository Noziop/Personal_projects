<?php

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\CohortController;
use App\Controllers\StudentController;
use App\Controllers\DrawingController;
use App\Controllers\UnavailabilityController;
use App\Controllers\VacationController;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;

return function (App $app) {
    // Routes publiques
    $app->get('/login', [AuthController::class, 'loginForm'])->setName('login');
    $app->post('/login', [AuthController::class, 'login']);
    $app->get('/logout', [AuthController::class, 'logout'])->setName('logout');

    // Routes protégées
    $app->group('', function (RouteCollectorProxy $group) {
        $group->get('/', [DrawingController::class, 'index'])->setName('home');
        
        // Routes pour les cohortes
        $group->get('/cohorts', [CohortController::class, 'index'])->setName('cohorts.index');
        $group->post('/cohorts', [CohortController::class, 'create']);
        $group->put('/cohorts/{id}', [CohortController::class, 'update']);
        $group->delete('/cohorts/{id}', [CohortController::class, 'delete']);

        // Routes pour les étudiants
        $group->get('/students', [StudentController::class, 'index'])->setName('students.index');
        $group->post('/students', [StudentController::class, 'create']);
        $group->put('/students/{id}', [StudentController::class, 'update']);
        $group->delete('/students/{id}', [StudentController::class, 'delete']);

        // Routes pour les tirages
        $group->post('/drawings', [DrawingController::class, 'draw']);

        // Routes pour les indisponibilités
        $group->get('/unavailabilities', [UnavailabilityController::class, 'index']);
        $group->post('/unavailabilities', [UnavailabilityController::class, 'create']);
        $group->put('/unavailabilities/{id}', [UnavailabilityController::class, 'update']);
        $group->delete('/unavailabilities/{id}', [UnavailabilityController::class, 'delete']);

        // Routes pour les vacances
        $group->get('/vacations', [VacationController::class, 'index'])->setName('vacations.index');
        $group->post('/vacations', [VacationController::class, 'create']);
        $group->put('/vacations/{id}', [VacationController::class, 'update']);
        $group->delete('/vacations/{id}', [VacationController::class, 'delete']);

        // Routes pour les utilisateurs (accessibles uniquement par l'administrateur)
        $group->group('/users', function (RouteCollectorProxy $group) {
            $group->get('', [UserController::class, 'index']);
            $group->post('', [UserController::class, 'create']);
            $group->put('/{id}', [UserController::class, 'update']);
            $group->delete('/{id}', [UserController::class, 'delete']);
        })->add(function ($request, $handler) {
            $user = $request->getAttribute('user');
            if ($user->getRole() !== 'directrice') {
                $response = new \Slim\Psr7\Response();
                return $response->withStatus(403)->withHeader('Location', '/');
            }
            return $handler->handle($request);
        });

    })->add(AuthMiddleware::class);
};