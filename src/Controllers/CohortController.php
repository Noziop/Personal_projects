<?php

/**
 * CohortController
 *
 * This controller handles all cohort-related operations in the SOD (Speaker of the Day) application.
 */

namespace App\Controllers;

use App\Models\Cohort;
use App\Services\CohortService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CohortController
{
    /**
     * @var CohortService The cohort service
     */
    private $cohortService;

    /**
     * CohortController constructor.
     *
     * @param CohortService $cohortService The cohort service
     */
    public function __construct(CohortService $cohortService)
    {
        $this->cohortService = $cohortService;
    }

    /**
     * Get all cohorts.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function getAllCohorts(Request $request, Response $response): Response
    {
        $cohorts = $this->cohortService->getAllCohorts();
        $response->getBody()->write(json_encode($cohorts));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Get a cohort by ID.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function getCohort(Request $request, Response $response, array $args): Response
    {
        $cohort = $this->cohortService->getCohortById((int)$args['id']);
        if (!$cohort) {
            return $response->withStatus(404)->withJson(['error' => 'Cohort not found']);
        }
        $response->getBody()->write(json_encode($cohort));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Create a new cohort.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function createCohort(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $cohort = new Cohort(
            $data['name'],
            new \DateTime($data['start_date']),
            new \DateTime($data['end_date'])
        );
        $id = $this->cohortService->createCohort($cohort);
        $response->getBody()->write(json_encode(['id' => $id]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    /**
     * Update an existing cohort.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function updateCohort(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $cohort = $this->cohortService->getCohortById((int)$args['id']);
        if (!$cohort) {
            return $response->withStatus(404)->withJson(['error' => 'Cohort not found']);
        }
        $cohort->setName($data['name']);
        $cohort->setStartDate(new \DateTime($data['start_date']));
        $cohort->setEndDate(new \DateTime($data['end_date']));
        $success = $this->cohortService->updateCohort($cohort);
        return $response->withJson(['success' => $success]);
    }

    /**
     * Delete a cohort.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function deleteCohort(Request $request, Response $response, array $args): Response
    {
        $success = $this->cohortService->deleteCohort((int)$args['id']);
        return $response->withJson(['success' => $success]);
    }
}
