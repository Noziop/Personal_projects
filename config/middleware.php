<?php

use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

return function (App $app) {
    $container = $app->getContainer();
    
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    
    $app->add(TwigMiddleware::createFromContainer($app));

    // Ajoutez ce middleware de journalisation
    $app->add(function (Request $request, RequestHandler $handler) use ($container) {
        try {
            $response = $handler->handle($request);
            $responseStatusCode = $response->getStatusCode();
    
            $logger = $container->get(LoggerInterface::class);
            $logger->info(
                sprintf(
                    '"%s %s" %d',
                    $request->getMethod(),
                    $request->getUri(),
                    $responseStatusCode
                )
            );
    
            return $response;
        } catch (\Throwable $e) {
            $logger = $container->get(LoggerInterface::class);
            $logger->error('Error: ' . $e->getMessage());
            throw $e;
        }
    });

    // Configurez le middleware d'erreur
    $errorMiddleware = $app->addErrorMiddleware(true, true, true);

    // Définissez un gestionnaire d'erreurs personnalisé
    $errorMiddleware->setDefaultErrorHandler(function (
        Request $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) use ($app) {
        $payload = ['error' => $exception->getMessage()];

        $response = $app->getResponseFactory()->createResponse();
        $twig = $app->getContainer()->get(Twig::class);
        
        return $twig->render(
            $response->withStatus(500),
            'error.twig',
            [
                'message' => $exception->getMessage(),
                'trace' => $displayErrorDetails ? $exception->getTraceAsString() : '',
                'displayErrorDetails' => $displayErrorDetails
            ]
        );
    });
};
