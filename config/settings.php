<?php

use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => true,
            'logErrors' => true,
            'logErrorDetails' => true,
            'logger' => [
                'name' => 'slim-app',
                'path' => __DIR__ . '/../logs/app.log',
                'level' => \Monolog\Logger::DEBUG,
            ],
            'view' => [
                'template_path' => __DIR__ . '/../templates',
                'twig' => [
                    'cache' => __DIR__ . '/../var/cache/twig',
                    'auto_reload' => true,
                    'debug' => true,
                ],
            ],
            'db' => [
                'host' => $_ENV['DB_HOST'],
                'database' => $_ENV['DB_NAME'],
                'username' => $_ENV['DB_USER'],
                'password' => $_ENV['DB_PASS'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],
        ],
    ]);
};
