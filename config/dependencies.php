<?php
/**
 * Dependencies Configuration File
 *
 * This file defines all the dependencies for the Slim application.
 * It uses PHP-DI for dependency injection.
 */

use DI\ContainerBuilder;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\Twig;
use Slim\Psr7\Factory\ResponseFactory;
use App\Models\User;
use App\Controllers\AuthController;
use GuzzleHttp\Client;
use App\Services\HolidayService;


return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        ResponseFactoryInterface::class => fn() => new ResponseFactory(),

        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);
            $logger->pushProcessor(new UidProcessor());
            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);
            return $logger;
        },

        Twig::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $twig = Twig::create($settings['view']['template_path'], $settings['view']['twig']);
            $environment = $twig->getEnvironment();
            $environment->addGlobal('session', $_SESSION);
            return $twig;
        },

        'view' => fn(ContainerInterface $c) => $c->get(Twig::class),

        PDO::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $dbSettings = $settings['db'];
            $dsn = "mysql:host={$dbSettings['host']};dbname={$dbSettings['database']};charset={$dbSettings['charset']}";
            return new PDO($dsn, $dbSettings['username'], $dbSettings['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        },

        User::class => fn(ContainerInterface $c) => new User($c->get(PDO::class)),

        AuthController::class => function (ContainerInterface $c) {
            return new AuthController($c->get(Twig::class), $c->get(User::class), $c->get(LoggerInterface::class));
        },

        App::class => function (ContainerInterface $c) {
            AppFactory::setContainer($c);
            return AppFactory::create();
        },
		
		HolidayService::class => function (ContainerInterface $c) {
			return new HolidayService(
				$c->get(Holiday::class),
				$c->get(LoggerInterface::class),
				new Client()
			);
		},
	
        RouteParserInterface::class => fn(ContainerInterface $c) => $c->get(App::class)->getRouteCollector()->getRouteParser(),
    ]);
};
