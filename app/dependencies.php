<?php

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        'logger' => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $logger = new Logger('app');
            $logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::DEBUG));
            return $logger;
        },
        PDO::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $dbSettings = $settings['db'];
            $dsn = "{$dbSettings['driver']}:host={$dbSettings['host']};dbname={$dbSettings['database']};charset={$dbSettings['charset']}";
            return new PDO($dsn, $dbSettings['username'], $dbSettings['password']);
        },
    ]);
};
