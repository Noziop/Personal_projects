<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Services\VacationService;
use App\Services\CohortService;
use Psr\Log\LoggerInterface;

class VacationController
{
    private $view;
    private $vacationService;
    private $cohortService;
    private $logger;

    public function __construct(Twig $view, VacationService $vacationService, CohortService $cohortService, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->vacationService = $vacationService;
        $this->cohortService = $cohortService;
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response): Response
    {
        $vacations = $this->vacationService->getAllVacations();
        return $this->view->render($response, 'vacations/index.twig', ['vacations' => $vacations]);
    }

    public function create(Request $request, Response $response): Response
    {
        $cohorts = $this->cohortService->getAllCohorts();

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $vacation = $this->vacationService->createVacation($data['cohort_id'], $data['start_date'], $data['end_date']);
            if ($vacation) {
                $this->logger->info('Vacation created', ['vacation_id' => $vacation['id']]);
                return $response->withHeader('Location', '/vacations')->withStatus(302);
            }
        }
        return $this->view->render($response, 'vacations/create.twig', ['cohorts' => $cohorts]);
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        $vacation = $this->vacationService->getVacationById($args['id']);
        $cohorts = $this->cohortService->getAllCohorts();

        if (!$vacation) {
            $this->logger->warning('Vacation not found', ['vacation_id' => $args['id']]);
            return $response->withStatus(404);
        }

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $updated = $this->vacationService->updateVacation($args['id'], $data['cohort_id'], $data['start_date'], $data['end_date']);
            if ($updated) {
                $this->logger->info('Vacation updated', ['vacation_id' => $args['id']]);
                return $response->withHeader('Location', '/vacations')->withStatus(302);
            }
        }

        return $this->view->render($response, 'vacations/edit.twig', ['vacation' => $vacation, 'cohorts' => $cohorts]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $deleted = $this->vacationService->deleteVacation($args['id']);
        if ($deleted) {
            $this->logger->info('Vacation deleted', ['vacation_id' => $args['id']]);
            return $response->withHeader('Location', '/vacations')->withStatus(302);
        }
        return $response->withStatus(404);
    }
}
