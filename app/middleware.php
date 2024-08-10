<?php

/**
 * Middleware Configuration
 *
 * This file sets up the middleware for the Slim application.
 */

use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Psr\Log\LoggerInterface;
use App\Handlers\ErrorHandler;
use Slim\Flash\MessagesMiddleware;


return function (App $app) {
    $container = $app->getContainer();
    $settings = $container->get('settings');

    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();

	// Add Flash Messages Middleware
	$app->add(MessagesMiddleware::class);

    // Add Error Middleware
    $errorMiddleware = $app->addErrorMiddleware(
        $settings['displayErrorDetails'] ?? false,
        $settings['logErrors'] ?? false,
        $settings['logErrorDetails'] ?? false
    );

    $twig = $container->get(Twig::class);
    $errorHandler = new ErrorHandler($container->get(Twig::class), $container->get(LoggerInterface::class));
    $errorMiddleware->setDefaultErrorHandler($errorHandler);

    // Add Twig-View Middleware
    $app->add(TwigMiddleware::createFromContainer($app, Twig::class));
};
