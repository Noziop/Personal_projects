<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Services\CohortService;
use Psr\Log\LoggerInterface;

class CohortController
{
    private $view;
    private $cohortService;
    private $logger;

    public function __construct(Twig $view, CohortService $cohortService, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->cohortService = $cohortService;
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response): Response
    {
        $cohorts = $this->cohortService->getAllCohorts();
        return $this->view->render($response, 'cohorts/index.twig', ['cohorts' => $cohorts]);
    }

    public function create(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $cohort = $this->cohortService->createCohort($data['name'], $data['start_date'], $data['end_date']);
            if ($cohort) {
                $this->logger->info('Cohort created', ['cohort_id' => $cohort['id']]);
                return $response->withHeader('Location', '/cohorts')->withStatus(302);
            }
        }
        return $this->view->render($response, 'cohorts/create.twig');
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        $cohort = $this->cohortService->getCohortById($args['id']);
        if (!$cohort) {
            $this->logger->warning('Cohort not found', ['cohort_id' => $args['id']]);
            return $response->withStatus(404);
        }

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $updated = $this->cohortService->updateCohort($args['id'], $data['name'], $data['start_date'], $data['end_date']);
            if ($updated) {
                $this->logger->info('Cohort updated', ['cohort_id' => $args['id']]);
                return $response->withHeader('Location', '/cohorts')->withStatus(302);
            }
        }

        return $this->view->render($response, 'cohorts/edit.twig', ['cohort' => $cohort]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $deleted = $this->cohortService->deleteCohort($args['id']);
        if ($deleted) {
            $this->logger->info('Cohort deleted', ['cohort_id' => $args['id']]);
            return $response->withHeader('Location', '/cohorts')->withStatus(302);
        }
        return $response->withStatus(404);
    }
}
