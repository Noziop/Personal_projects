<?php

/**
 * Application Dependencies
 *
 * This file defines the dependencies for the Slim application.
 * It includes configurations for logging and database connection.
 */

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        'logger' => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $loggerSettings = $settings['logger'];

            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new RotatingFileHandler($loggerSettings['path'], 0, $loggerSettings['level'], true, 0664);
            $logger->pushHandler($handler);

            $logger->pushProcessor(new IntrospectionProcessor());
            $logger->pushProcessor(new WebProcessor());

            return $logger;
        },

        PDO::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $dbSettings = $settings['db'];
            $dsn = "{$dbSettings['driver']}:host={$dbSettings['host']};dbname={$dbSettings['database']};charset={$dbSettings['charset']}";
            $pdo = new PDO($dsn, $dbSettings['username'], $dbSettings['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        },
    ]);
};
