<?php
/**
 * Logger.php
 * 
 * This file contains the Logger class which provides a centralized logging system for the application.
 * It uses Monolog to handle logging to both file and console outputs.
 * 
 * @package App\Logging
 */

namespace App\Logging;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    private static $logger;

    /**
     * Initialize the logger
     * 
     * @param string $name The name of the logger
     * @return void
     */
    public static function init($name = 'app')
    {
        self::$logger = new MonologLogger($name);

        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            "Y-m-d H:i:s.u"
        );

        // Console handler
        $consoleHandler = new StreamHandler('php://stdout', MonologLogger::DEBUG);
        $consoleHandler->setFormatter($formatter);
        self::$logger->pushHandler($consoleHandler);

        // File handler
        $fileHandler = new RotatingFileHandler(__DIR__ . '/../../logs/app.log', 0, MonologLogger::DEBUG);
        $fileHandler->setFormatter($formatter);
        self::$logger->pushHandler($fileHandler);
    }

    /**
     * Log a debug message
     * 
     * @param string $message The log message
     * @param array $context Additional context
     * @return void
     */
    public static function debug($message, array $context = [])
    {
        self::ensureInit();
        self::$logger->debug($message, $context);
    }

    /**
     * Log an info message
     * 
     * @param string $message The log message
     * @param array $context Additional context
     * @return void
     */
    public static function info($message, array $context = [])
    {
        self::ensureInit();
        self::$logger->info($message, $context);
    }

    /**
     * Log a warning message
     * 
     * @param string $message The log message
     * @param array $context Additional context
     * @return void
     */
    public static function warning($message, array $context = [])
    {
        self::ensureInit();
        self::$logger->warning($message, $context);
    }

    /**
     * Log an error message
     * 
     * @param string $message The log message
     * @param array $context Additional context
     * @return void
     */
    public static function error($message, array $context = [])
    {
        self::ensureInit();
        self::$logger->error($message, $context);
    }

    /**
     * Ensure the logger is initialized
     * 
     * @return void
     */
    private static function ensureInit()
    {
        if (!self::$logger) {
            self::init();
        }
    }
}