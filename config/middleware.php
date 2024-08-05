<?php

use Slim\App;
use Slim\Views\TwigMiddleware;

return function (App $app) {
    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();

    // Add the Slim built-in routing middleware
    $app->addRoutingMiddleware();

    // Add Twig Middleware
    $app->add(TwigMiddleware::createFromContainer($app));

    // Add logging middleware
    $app->add(function ($request, $handler) {
        try {
            $response = $handler->handle($request);
            error_log("Response status: " . $response->getStatusCode());
            error_log("Response headers: " . json_encode($response->getHeaders()));
            return $response;
        } catch (\Throwable $e) {
            error_log("Error in middleware: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    });

    // Add Error Middleware
    $errorMiddleware = $app->addErrorMiddleware(true, true, true);

	$app->add(function ($request, $handler) {
		try {
			return $handler->handle($request);
		} catch (\Throwable $e) {
			error_log($e->getMessage());
			error_log($e->getTraceAsString());
			throw $e;
		}
	});
	

    // Customize error handler
    $errorHandler = $errorMiddleware->getDefaultErrorHandler();
    $errorHandler->registerErrorRenderer('text/html', function ($exception, $displayErrorDetails) use ($app) {
        $view = $app->getContainer()->get('view');
        error_log("Rendering error template. Exception: " . $exception->getMessage());
        return $view->render(
            $app->getResponseFactory()->createResponse(),
            'error.twig',
            [
                'message' => $exception->getMessage(),
                'displayErrorDetails' => $displayErrorDetails
            ]
        );
    });
};
