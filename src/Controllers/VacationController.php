<?php

/**
 * VacationController
 *
 * This controller handles all vacation-related operations in the SOD (Speaker of the Day) application.
 */

namespace App\Controllers;

use App\Models\Vacation;
use App\Services\VacationService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class VacationController
{
    /**
     * @var VacationService The vacation service
     */
    private $vacationService;

    /**
     * @var LoggerInterface The logger
     */
    private $logger;

    /**
     * VacationController constructor.
     *
     * @param VacationService $vacationService The vacation service
     * @param LoggerInterface $logger The logger
     */
    public function __construct(VacationService $vacationService, LoggerInterface $logger)
    {
        $this->vacationService = $vacationService;
        $this->logger = $logger;
    }

    /**
     * Get all vacations.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function getAllVacations(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for getting all vacations');
        try {
            $vacations = $this->vacationService->getAllVacations();
            $this->logger->info('Successfully retrieved all vacations', ['count' => count($vacations)]);
            $response->getBody()->write(json_encode($vacations));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving all vacations', ['error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while retrieving vacations']);
        }
    }

    /**
     * Get a vacation by ID.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function getVacation(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for getting vacation', ['id' => $id]);
        try {
            $vacation = $this->vacationService->getVacationById($id);
            if (!$vacation) {
                $this->logger->warning('Vacation not found', ['id' => $id]);
                return $response->withStatus(404)->withJson(['error' => 'Vacation not found']);
            }
            $this->logger->info('Successfully retrieved vacation', ['id' => $id]);
            $response->getBody()->write(json_encode($vacation));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving vacation', ['id' => $id, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while retrieving the vacation']);
        }
    }

    /**
     * Create a new vacation.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function createVacation(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for creating a new vacation');
        try {
            $data = $request->getParsedBody();
            $vacation = new Vacation(
                (int)$data['cohort_id'],
                $data['name'],
                new \DateTime($data['start_date']),
                new \DateTime($data['end_date'])
            );
            $id = $this->vacationService->createVacation($vacation);
            $this->logger->info('Successfully created new vacation', ['id' => $id]);
            $response->getBody()->write(json_encode(['id' => $id]));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error creating new vacation', ['error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while creating the vacation']);
        }
    }

    /**
     * Update an existing vacation.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function updateVacation(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for updating vacation', ['id' => $id]);
        try {
            $data = $request->getParsedBody();
            $vacation = $this->vacationService->getVacationById($id);
            if (!$vacation) {
                $this->logger->warning('Vacation not found for update', ['id' => $id]);
                return $response->withStatus(404)->withJson(['error' => 'Vacation not found']);
            }
            $vacation->setCohortId((int)$data['cohort_id']);
            $vacation->setName($data['name']);
            $vacation->setStartDate(new \DateTime($data['start_date']));
            $vacation->setEndDate(new \DateTime($data['end_date']));
            $success = $this->vacationService->updateVacation($vacation);
            $this->logger->info('Successfully updated vacation', ['id' => $id, 'success' => $success]);
            return $response->withJson(['success' => $success]);
        } catch (\Exception $e) {
            $this->logger->error('Error updating vacation', ['id' => $id, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while updating the vacation']);
        }
    }

    /**
     * Delete a vacation.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function deleteVacation(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for deleting vacation', ['id' => $id]);
        try {
            $success = $this->vacationService->deleteVacation($id);
            $this->logger->info('Vacation deletion attempt completed', ['id' => $id, 'success' => $success]);
            return $response->withJson(['success' => $success]);
        } catch (\Exception $e) {
            $this->logger->error('Error deleting vacation', ['id' => $id, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while deleting the vacation']);
        }
    }

    /**
     * Get all vacations for a specific cohort.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function getVacationsByCohort(Request $request, Response $response, array $args): Response
    {
        $cohortId = (int)$args['cohort_id'];
        $this->logger->info('Request received for getting vacations by cohort', ['cohort_id' => $cohortId]);
        try {
            $vacations = $this->vacationService->getVacationsByCohort($cohortId);
            $this->logger->info('Successfully retrieved vacations by cohort', ['cohort_id' => $cohortId, 'count' => count($vacations)]);
            $response->getBody()->write(json_encode($vacations));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving vacations by cohort', ['cohort_id' => $cohortId, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while retrieving vacations for the cohort']);
        }
    }
}
