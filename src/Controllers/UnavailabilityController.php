<?php

/**
 * UnavailabilityController
 *
 * This controller handles all unavailability-related operations in the SOD (Speaker of the Day) application.
 */

namespace App\Controllers;

use App\Models\Unavailability;
use App\Services\UnavailabilityService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class UnavailabilityController
{
    /**
     * @var UnavailabilityService The unavailability service
     */
    private $unavailabilityService;

    /**
     * @var LoggerInterface The logger
     */
    private $logger;

    /**
     * UnavailabilityController constructor.
     *
     * @param UnavailabilityService $unavailabilityService The unavailability service
     * @param LoggerInterface $logger The logger
     */
    public function __construct(UnavailabilityService $unavailabilityService, LoggerInterface $logger)
    {
        $this->unavailabilityService = $unavailabilityService;
        $this->logger = $logger;
    }

    /**
     * Get all unavailabilities.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function getAllUnavailabilities(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for getting all unavailabilities');
        try {
            $unavailabilities = $this->unavailabilityService->getAllUnavailabilities();
            $this->logger->info('Successfully retrieved all unavailabilities', ['count' => count($unavailabilities)]);
            $response->getBody()->write(json_encode($unavailabilities));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving all unavailabilities', ['error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while retrieving unavailabilities']);
        }
    }

    /**
     * Get an unavailability by ID.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function getUnavailability(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for getting unavailability', ['id' => $id]);
        try {
            $unavailability = $this->unavailabilityService->getUnavailabilityById($id);
            if (!$unavailability) {
                $this->logger->warning('Unavailability not found', ['id' => $id]);
                return $response->withStatus(404)->withJson(['error' => 'Unavailability not found']);
            }
            $this->logger->info('Successfully retrieved unavailability', ['id' => $id]);
            $response->getBody()->write(json_encode($unavailability));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving unavailability', ['id' => $id, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while retrieving the unavailability']);
        }
    }

    /**
     * Create a new unavailability.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function createUnavailability(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for creating a new unavailability');
        try {
            $data = $request->getParsedBody();
            $unavailability = new Unavailability(
                (int)$data['student_id'],
                new \DateTime($data['start_date_time']),
                new \DateTime($data['end_date_time']),
                $data['reason'] ?? null
            );
            $id = $this->unavailabilityService->createUnavailability($unavailability);
            $this->logger->info('Successfully created new unavailability', ['id' => $id]);
            $response->getBody()->write(json_encode(['id' => $id]));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error creating new unavailability', ['error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while creating the unavailability']);
        }
    }

    /**
     * Update an existing unavailability.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function updateUnavailability(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for updating unavailability', ['id' => $id]);
        try {
            $data = $request->getParsedBody();
            $unavailability = $this->unavailabilityService->getUnavailabilityById($id);
            if (!$unavailability) {
                $this->logger->warning('Unavailability not found for update', ['id' => $id]);
                return $response->withStatus(404)->withJson(['error' => 'Unavailability not found']);
            }
            $unavailability->setStudentId((int)$data['student_id']);
            $unavailability->setStartDateTime(new \DateTime($data['start_date_time']));
            $unavailability->setEndDateTime(new \DateTime($data['end_date_time']));
            $unavailability->setReason($data['reason'] ?? null);
            $success = $this->unavailabilityService->updateUnavailability($unavailability);
            $this->logger->info('Successfully updated unavailability', ['id' => $id, 'success' => $success]);
            return $response->withJson(['success' => $success]);
        } catch (\Exception $e) {
            $this->logger->error('Error updating unavailability', ['id' => $id, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while updating the unavailability']);
        }
    }

    /**
     * Delete an unavailability.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function deleteUnavailability(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for deleting unavailability', ['id' => $id]);
        try {
            $success = $this->unavailabilityService->deleteUnavailability($id);
            $this->logger->info('Unavailability deletion attempt completed', ['id' => $id, 'success' => $success]);
            return $response->withJson(['success' => $success]);
        } catch (\Exception $e) {
            $this->logger->error('Error deleting unavailability', ['id' => $id, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while deleting the unavailability']);
        }
    }

    /**
     * Get all unavailabilities for a specific student.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function getUnavailabilitiesByStudent(Request $request, Response $response, array $args): Response
    {
        $studentId = (int)$args['student_id'];
        $this->logger->info('Request received for getting unavailabilities by student', ['student_id' => $studentId]);
        try {
            $unavailabilities = $this->unavailabilityService->getUnavailabilitiesByStudent($studentId);
            $this->logger->info('Successfully retrieved unavailabilities by student', ['student_id' => $studentId, 'count' => count($unavailabilities)]);
            $response->getBody()->write(json_encode($unavailabilities));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving unavailabilities by student', ['student_id' => $studentId, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while retrieving unavailabilities for the student']);
        }
    }
}
