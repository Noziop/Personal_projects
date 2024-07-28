<?php

namespace App\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;

class ErrorHandler
{
    private $view;
    private $logger;

    public function __construct(Twig $view, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->logger = $logger;
    }

    public function __invoke(
        ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        // Log error
        if ($logErrors) {
            $this->logger->error($exception->getMessage(), [
                'exception' => $exception,
                'url' => (string) $request->getUri()
            ]);
        }

        $statusCode = 500;
        if ($exception instanceof \App\Exceptions\HttpException) {
            $statusCode = $exception->getCode();
        }

        // Render error template
        return $this->view->render(
            $this->createResponse($statusCode),
            'error.twig',
            [
                'statusCode' => $statusCode,
                'errorMessage' => $exception->getMessage(),
                'displayErrorDetails' => $displayErrorDetails,
                'exception' => $exception
            ]
        );
    }

    private function createResponse(int $statusCode): ResponseInterface
    {
        $response = new \Slim\Psr7\Response();
        return $response->withStatus($statusCode);
    }
}
