<?php

// Set error display based on environment
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Main entry point for the application
 *
 * This file bootstraps the application, sets up the container,
 * and runs the application.
 */

 use DI\ContainerBuilder;
 use Slim\Factory\AppFactory;
 use Slim\Views\TwigMiddleware;
 use Dotenv\Dotenv;
 
 // Autoloader
 require __DIR__ . '/../vendor/autoload.php';
 
 // Start session
 session_start();
 
 // Load environment variables
 $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
 $dotenv->load();
 
 // Instantiate PHP-DI ContainerBuilder
 $containerBuilder = new ContainerBuilder();
 
 // Set up settings
 $settings = require __DIR__ . '/../config/settings.php';
 $settings($containerBuilder);
 
 // Set up dependencies
 $dependencies = require __DIR__ . '/../config/dependencies.php';
 $dependencies($containerBuilder);
 
 // Build PHP-DI Container instance
 $container = $containerBuilder->build();
 
 // Instantiate the app
 AppFactory::setContainer($container);
 $app = AppFactory::create();
 
 // Add Twig-View Middleware
 $app->add(TwigMiddleware::createFromContainer($app, 'view'));
 
 // Register routes
 $routes = require __DIR__ . '/../config/routes.php';
 $routes($app);
 
 // Register middleware
 $middleware = require __DIR__ . '/../config/middleware.php';
 $middleware($app);
 
 // Add Error Middleware
 $displayErrorDetails = $_ENV['APP_DEBUG'] ?? false;
 $logErrors = true;
 $logErrorDetails = true;
 $app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);
 
 // Run app
 $app->run();