<?php

use Psr\Container\ContainerInterface;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use DI\ContainerBuilder;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use App\Services\AuthService;
use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        ResponseFactoryInterface::class => function (ContainerInterface $c) {
            return new ResponseFactory();
        },

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

        'view' => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            return Twig::create($settings['view']['template_path'], [
                'cache' => $settings['view']['cache_path'],
                'auto_reload' => true,
                'debug' => $settings['displayErrorDetails'],
            ]);
        },

        AuthService::class => function (ContainerInterface $c) {
            return new AuthService($c->get(PDO::class));
        },

        AuthController::class => function (ContainerInterface $c) {
            return new AuthController($c->get(AuthService::class), $c->get('view'));
        },

        AuthMiddleware::class => function (ContainerInterface $c) {
            return new AuthMiddleware($c->get(AuthService::class));
        },

        App::class => function (ContainerInterface $c) {
            AppFactory::setContainer($c);
            return AppFactory::create();
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
        App\Controllers\UserController::class => function (ContainerInterface $c) {
            return new App\Controllers\UserController($c->get(AuthService::class), $c->get(LoggerInterface::class), $c->get(Twig::class));
        },
    ]);
};