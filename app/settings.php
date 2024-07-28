<?php

/**
 * Application Settings
 *
 * This file defines the settings for the Slim application.
 * It includes configurations for error display, logging, and database connection.
 */

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        'settings' => [
            // Display error details
            'displayErrorDetails' => filter_var($_ENV['DISPLAY_ERROR_DETAILS'] ?? true, FILTER_VALIDATE_BOOLEAN),

            // Add content length header
            'addContentLengthHeader' => filter_var($_ENV['ADD_CONTENT_LENGTH_HEADER'] ?? true, FILTER_VALIDATE_BOOLEAN),

            // Error Logging
            'logErrors'  => filter_var($_ENV['LOG_ERRORS'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'logErrorDetails' => filter_var($_ENV['LOG_ERROR_DETAILS'] ?? true, FILTER_VALIDATE_BOOLEAN),

            // Monolog settings
            'logger' => [
                'name' => $_ENV['APP_NAME'] ?? 'app',
                'path' => __DIR__ . '/../logs/app.log',
                'level' => Logger::DEBUG,
            ],

            // Database settings
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

            // Application Secret
            'app_secret' => $_ENV['APP_SECRET'] ?? 'your-secret-key',
        ],
    ]);
};
