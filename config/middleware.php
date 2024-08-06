<?php

use Slim\App;
use Slim\Views\TwigMiddleware;
use Slim\Handlers\ErrorHandler;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Response;

return function (App $app) {
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();

    $app->add(TwigMiddleware::createFromContainer($app));

    $app->add(function ($request, $handler) {
        try {
            return $handler->handle($request);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            throw $e;
        }
    });

    $errorMiddleware = $app->addErrorMiddleware(true, true, true);

    $customErrorHandler = function (
        ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) use ($app) {
        $payload = ['error' => $exception->getMessage()];

        $response = $app->getResponseFactory()->createResponse();
        $response->getBody()->write(
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        return $response->withStatus(500)
                        ->withHeader('Content-Type', 'application/json');
    };

    $errorMiddleware->setDefaultErrorHandler($customErrorHandler);
};
