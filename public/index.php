<?php

/**
 * index.php
 * 
 * This is the entry point of the application. It sets up the Slim application,
 * initializes the logger, and includes the necessary route definitions.
 */

 use Slim\Factory\AppFactory;
 use Slim\Exception\HttpNotFoundException;
 use Psr\Http\Message\ServerRequestInterface as Request;
 use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
 use Slim\Psr7\Response;
 use App\Logging\Logger;
 
 require __DIR__ . '/../vendor/autoload.php';
 
 // Initialize logger
 Logger::init();
 Logger::info('Application starting');
 
 // Create Slim app
 $app = AppFactory::create();
 
 // Add routing middleware
 $app->addRoutingMiddleware();
 
 // Add error middleware
 $errorMiddleware = $app->addErrorMiddleware(true, true, true);
 
 // Define app routes
 $routes = require __DIR__ . '/../app/routes.php';
 $routes($app);
 
 // Add middleware to log all requests
 $app->add(function (Request $request, RequestHandler $handler) {
	 Logger::info('Incoming request', [
		 'method' => $request->getMethod(),
		 'uri' => (string) $request->getUri()
	 ]);
	 return $handler->handle($request);
 });
 
 // Catch-all route to handle 404
 $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
	 throw new HttpNotFoundException($request);
 });
 
 Logger::info('Routes defined, starting application');
 
 // Run app
 $app->run();
 
 Logger::info('Application finished');