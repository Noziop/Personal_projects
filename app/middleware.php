<?php

use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Handlers\ErrorHandler;

return function (App $app) {
    $container = $app->getContainer();
    $settings = $container->get('settings');

    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();

    // Add Error Middleware
    $errorMiddleware = $app->addErrorMiddleware(
        $settings['displayErrorDetails'],
        $settings['logErrors'],
        $settings['logErrorDetails']
    );

    $errorHandler = new ErrorHandler($container->get(Twig::class), $container->get('logger'));
    $errorMiddleware->setDefaultErrorHandler($errorHandler);

    // Add Twig-View Middleware
    $app->add(TwigMiddleware::createFromContainer($app));
};
