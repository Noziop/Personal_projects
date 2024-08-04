<?php

use Slim\App;

return function (App $app) {
    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();

    // Add the Slim built-in routing middleware
    $app->addRoutingMiddleware();

    // Add Error Middleware
    $errorMiddleware = $app->addErrorMiddleware(true, true, true);
    
    // Customize error handler
    $errorHandler = $errorMiddleware->getDefaultErrorHandler();
    $errorHandler->registerErrorRenderer('text/html', function ($exception, $displayErrorDetails) use ($app) {
        $view = $app->getContainer()->get('view');
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
