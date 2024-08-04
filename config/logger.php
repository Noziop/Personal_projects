<?php
/**
 * Logger Configuration File
 *
 * This file configures the Monolog logger for the application.
 * It sets up a StreamHandler to write logs to a file and adds a UidProcessor
 * to assign a unique identifier to each log entry.
 */

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;

return function (ContainerInterface $container) {
    /**
     * Retrieve logger settings from the container
     */
    $loggerSettings = $container->get('settings')['logger'];

    /**
     * Create a new Logger instance
     * 
     * @var Logger $logger
     */
    $logger = new Logger($loggerSettings['name']);

    /**
     * Add a unique identifier processor
     * 
     * This processor adds a unique identifier to each log entry,
     * which can be useful for tracking specific request lifecycles.
     */
    $uidProcessor = new UidProcessor();
    $logger->pushProcessor($uidProcessor);

    /**
     * Add a stream handler
     * 
     * This handler will write log entries to a file specified in the settings.
     * The log level is also specified in the settings.
     */
    $streamHandler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
    $logger->pushHandler($streamHandler);

    /**
     * Add more handlers or processors here if needed
     * For example, you might want to add a handler to send critical errors via email:
     *
     * use Monolog\Handler\NativeMailerHandler;
     * $mailHandler = new NativeMailerHandler(
     *     'admin@example.com',
     *     'Error in application',
     *     'noreply@example.com',
     *     Logger::CRITICAL
     * );
     * $logger->pushHandler($mailHandler);
     */

    /**
     * You can also add more processors, such as IntrospectionProcessor for more detailed logs:
     *
     * use Monolog\Processor\IntrospectionProcessor;
     * $logger->pushProcessor(new IntrospectionProcessor());
     */

    /**
     * Return the configured logger
     */
    return $logger;
};