<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Coming soon : Drawing app for Speaker of the Day ritual @Holberton_School Thonon-les-bains!');
        return $response;
    });

    // Ajoutez ici vos autres routes
};
