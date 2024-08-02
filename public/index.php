<?php

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Logging\Logger;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

// Initialize logger
Logger::init();
Logger::info('Application starting');

// Create Container
$container = new Container();
AppFactory::setContainer($container);

// Create Slim app
$app = AppFactory::create();

// Create Twig
$container->set('view', function() {
    return Twig::create(__DIR__ . '/../templates', ['cache' => false]);
});

// Add Twig-View Middleware
$app->add(TwigMiddleware::createFromContainer($app));

// Add routing middleware
$app->addRoutingMiddleware();

// Add error middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Define app routes
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

Logger::info('Routes defined, starting application');

// Run app
$app->run();

Logger::info('Application finished');