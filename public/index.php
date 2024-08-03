<?php

use Slim\Factory\AppFactory;
use Slim\Views\TwigMiddleware;
use App\Logging\Logger;
use DI\ContainerBuilder;

require __DIR__ . '/../vendor/autoload.php';

// Display Errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize logger
Logger::init();
Logger::info('Application starting');

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

// Set up settings
$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
$app = $container->get(Slim\App::class);

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