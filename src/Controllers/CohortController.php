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
use Psr\Log\LoggerInterface;

class CohortController
{
    /**
     * @var CohortService The cohort service
     */
    private $cohortService;

    /**
     * @var LoggerInterface The logger
     */
    private $logger;

    /**
     * CohortController constructor.
     *
     * @param CohortService $cohortService The cohort service
     * @param LoggerInterface $logger The logger
     */
    public function __construct(CohortService $cohortService, LoggerInterface $logger)
    {
        $this->cohortService = $cohortService;
        $this->logger = $logger;
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
        $this->logger->info('Request received for getting all cohorts');
        try {
            $cohorts = $this->cohortService->getAllCohorts();
            $this->logger->info('Successfully retrieved all cohorts', ['count' => count($cohorts)]);
            $response->getBody()->write(json_encode($cohorts));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving all cohorts', ['error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while retrieving cohorts']);
        }
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
        $id = (int)$args['id'];
        $this->logger->info('Request received for getting cohort', ['id' => $id]);
        try {
            $cohort = $this->cohortService->getCohortById($id);
            if (!$cohort) {
                $this->logger->warning('Cohort not found', ['id' => $id]);
                return $response->withStatus(404)->withJson(['error' => 'Cohort not found']);
            }
            $this->logger->info('Successfully retrieved cohort', ['id' => $id]);
            $response->getBody()->write(json_encode($cohort));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving cohort', ['id' => $id, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while retrieving the cohort']);
        }
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
        $this->logger->info('Request received for creating a new cohort');
        try {
            $data = $request->getParsedBody();
            $cohort = new Cohort(
                $data['name'],
                new \DateTime($data['start_date']),
                new \DateTime($data['end_date'])
            );
            $id = $this->cohortService->createCohort($cohort);
            $this->logger->info('Successfully created new cohort', ['id' => $id]);
            $response->getBody()->write(json_encode(['id' => $id]));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error creating new cohort', ['error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while creating the cohort']);
        }
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
        $id = (int)$args['id'];
        $this->logger->info('Request received for updating cohort', ['id' => $id]);
        try {
            $data = $request->getParsedBody();
            $cohort = $this->cohortService->getCohortById($id);
            if (!$cohort) {
                $this->logger->warning('Cohort not found for update', ['id' => $id]);
                return $response->withStatus(404)->withJson(['error' => 'Cohort not found']);
            }
            $cohort->setName($data['name']);
            $cohort->setStartDate(new \DateTime($data['start_date']));
            $cohort->setEndDate(new \DateTime($data['end_date']));
            $success = $this->cohortService->updateCohort($cohort);
            $this->logger->info('Successfully updated cohort', ['id' => $id, 'success' => $success]);
            return $response->withJson(['success' => $success]);
        } catch (\Exception $e) {
            $this->logger->error('Error updating cohort', ['id' => $id, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while updating the cohort']);
        }
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
        $id = (int)$args['id'];
        $this->logger->info('Request received for deleting cohort', ['id' => $id]);
        try {
            $success = $this->cohortService->deleteCohort($id);
            $this->logger->info('Cohort deletion attempt completed', ['id' => $id, 'success' => $success]);
            return $response->withJson(['success' => $success]);
        } catch (\Exception $e) {
            $this->logger->error('Error deleting cohort', ['id' => $id, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while deleting the cohort']);
        }
    }
}
