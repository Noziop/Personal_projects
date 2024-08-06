<?php

use Slim\App;
use Slim\Views\TwigMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Exception\HttpNotFoundException;

return function (App $app) {
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    
    $app->add(TwigMiddleware::createFromContainer($app));

    // Ajoutez ce middleware de journalisation
	$app->add(function (Request $request, RequestHandler $handler) use ($app) {
		try {
			$response = $handler->handle($request);
			$responseStatusCode = $response->getStatusCode();
	
			$logger = $app->getContainer()->get(LoggerInterface::class);
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
			$logger = $app->getContainer()->get(LoggerInterface::class);
			$logger->error('Error: ' . $e->getMessage());
			throw $e;
		}
	});

    // Configurez le middleware d'erreur
    $errorMiddleware = $app->addErrorMiddleware(true, true, true);

    // Définissez un gestionnaire d'erreurs personnalisé
    $errorHandler = $errorMiddleware->getDefaultErrorHandler();
    $errorHandler->forceContentType('text/html');
    $errorHandler->setDefaultErrorRenderer('text/html', function (\Throwable $exception, bool $displayErrorDetails) use ($app) {
        $response = new Response();
        $twig = $app->getContainer()->get('view');
        return $twig->render($response, 'error.twig', [
            'message' => $exception->getMessage(),
            'trace' => $displayErrorDetails ? $exception->getTraceAsString() : '',
            'displayErrorDetails' => $displayErrorDetails
        ]);
    });
};
