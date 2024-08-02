<?php

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response, $args) use ($app) {
        $view = $app->getContainer()->get('view');
        return $view->render($response, 'home/index.twig');
    });

    // Groupes de routes existants
    $app->group('/cohorts', function (Group $group) {
        // ... (routes existantes pour les cohortes)
    });

    $app->group('/students', function (Group $group) {
        // ... (routes existantes pour les étudiants)
    });

    $app->group('/drawings', function (Group $group) {
        // ... (routes existantes pour les tirages)
    });

    $app->group('/unavailabilities', function (Group $group) {
        // ... (routes existantes pour les indisponibilités)
    });

    $app->group('/vacations', function (Group $group) {
        // ... (routes existantes pour les vacances)
    });
};