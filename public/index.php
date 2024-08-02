<?php

/**
 * index.php
 * 
 * This is the entry point of the application. It sets up the Slim application,
 * initializes the logger, and includes the necessary route definitions.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Logging\Logger;

require __DIR__ . '/../vendor/autoload.php';

// Initialize logger
Logger::init();
Logger::info('Application starting');

// Create Slim app
$app = AppFactory::create();

// Create Twig
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

// Add routing middleware
$app->addRoutingMiddleware();

// Add error middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Define app routes
require __DIR__ . '/../app/routes.php';

Logger::info('Routes defined, starting application');

// Run app
$app->run();

Logger::info('Application finished');