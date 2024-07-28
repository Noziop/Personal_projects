<?php

use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => $_ENV['DISPLAY_ERROR_DETAILS'] ?? true,  // Set to false in production
            'addContentLengthHeader' => $_ENV['ADD_CONTENT_LENGTH_HEADER'] ?? true,  // Allow the web server to send the content-length header
            'logError'            => $_ENV['LOG_ERRORS'] ?? true,  // log errors to a file
            'logErrorDetails'     => $_ENV['LOG_ERROR_DETAILS'] ?? true, // log error details
            'db' => [
                'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'database' => $_ENV['DB_NAME'] ?? 'sod_database',
                'username' => $_ENV['DB_USER'] ?? 'root',
                'password' => $_ENV['DB_PASS'] ?? '',
                'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
                'collation' => $_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci',
                'prefix' => $_ENV['DB_PREFIX'] ?? '',
            ],
        ],
    ]);
};
