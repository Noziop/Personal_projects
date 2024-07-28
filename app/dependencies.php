<?php

/**
 * Dependencies Configuration
 *
 * This file defines the dependencies for the Slim application.
 * It includes configurations for database, logging, and view rendering.
 */

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use Twig\Loader\FilesystemLoader;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Database connection
        PDO::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $dbSettings = $settings['db'];
            $dsn = "{$dbSettings['driver']}:host={$dbSettings['host']};dbname={$dbSettings['database']};charset={$dbSettings['charset']}";
            return new PDO($dsn, $dbSettings['username'], $dbSettings['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        },

        // Monolog logger
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);
            $processor = new UidProcessor();
            $logger->pushProcessor($processor);
            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);
            return $logger;
        },

        // Twig templating engine
        Twig::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $loader = new FilesystemLoader($settings['view']['template_path']);
            $twig = new Twig($loader, [
                'cache' => $settings['view']['cache_path'],
                'auto_reload' => true,
                'debug' => $settings['displayErrorDetails'],
            ]);
            if ($settings['displayErrorDetails']) {
                $twig->addExtension(new \Twig\Extension\DebugExtension());
            }
            return $twig;
        },

        // Services
        App\Services\CohortService::class => function (ContainerInterface $c) {
            return new App\Services\CohortService($c->get(PDO::class), $c->get(LoggerInterface::class));
        },
        App\Services\StudentService::class => function (ContainerInterface $c) {
            return new App\Services\StudentService($c->get(PDO::class), $c->get(LoggerInterface::class));
        },
        App\Services\DrawingService::class => function (ContainerInterface $c) {
            return new App\Services\DrawingService(
                $c->get(PDO::class),
                $c->get(LoggerInterface::class),
                $c->get(App\Services\StudentService::class),
                $c->get(App\Services\ConstraintService::class)
            );
        },
        App\Services\UnavailabilityService::class => function (ContainerInterface $c) {
            return new App\Services\UnavailabilityService($c->get(PDO::class), $c->get(LoggerInterface::class));
        },
        App\Services\VacationService::class => function (ContainerInterface $c) {
            return new App\Services\VacationService($c->get(PDO::class), $c->get(LoggerInterface::class));
        },
        App\Services\ConstraintService::class => function (ContainerInterface $c) {
            return new App\Services\ConstraintService($c->get(PDO::class), $c->get(LoggerInterface::class));
        },
        App\Services\FetchHolidaysService::class => function (ContainerInterface $c) {
            return new App\Services\FetchHolidaysService($c->get(PDO::class), $c->get(LoggerInterface::class));
        },

        // Controllers
        App\Controllers\CohortController::class => function (ContainerInterface $c) {
            return new App\Controllers\CohortController($c->get(App\Services\CohortService::class), $c->get(LoggerInterface::class));
        },
        App\Controllers\StudentController::class => function (ContainerInterface $c) {
            return new App\Controllers\StudentController($c->get(App\Services\StudentService::class), $c->get(LoggerInterface::class));
        },
        App\Controllers\DrawingController::class => function (ContainerInterface $c) {
            return new App\Controllers\DrawingController($c->get(App\Services\DrawingService::class), $c->get(LoggerInterface::class));
        },
        App\Controllers\UnavailabilityController::class => function (ContainerInterface $c) {
            return new App\Controllers\UnavailabilityController($c->get(App\Services\UnavailabilityService::class), $c->get(LoggerInterface::class));
        },
        App\Controllers\VacationController::class => function (ContainerInterface $c) {
            return new App\Controllers\VacationController($c->get(App\Services\VacationService::class), $c->get(LoggerInterface::class));
        },
    ]);
};
