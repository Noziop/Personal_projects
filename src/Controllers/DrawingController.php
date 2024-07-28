<?php

/**
 * DrawingController
 *
 * This controller handles all drawing-related operations in the SOD (Speaker of the Day) application.
 */

namespace App\Controllers;

use App\Models\Drawing;
use App\Services\DrawingService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class DrawingController
{
    /**
     * @var DrawingService The drawing service
     */
    private $drawingService;

    /**
     * @var LoggerInterface The logger
     */
    private $logger;

    /**
     * DrawingController constructor.
     *
     * @param DrawingService $drawingService The drawing service
     * @param LoggerInterface $logger The logger
     */
    public function __construct(DrawingService $drawingService, LoggerInterface $logger)
    {
        $this->drawingService = $drawingService;
        $this->logger = $logger;
    }

    /**
     * Get all drawings.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function getAllDrawings(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for getting all drawings');
        try {
            $drawings = $this->drawingService->getAllDrawings();
            $this->logger->info('Successfully retrieved all drawings', ['count' => count($drawings)]);
            $response->getBody()->write(json_encode($drawings));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving all drawings', ['error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while retrieving drawings']);
        }
    }

    /**
     * Get a drawing by ID.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function getDrawing(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for getting drawing', ['id' => $id]);
        try {
            $drawing = $this->drawingService->getDrawingById($id);
            if (!$drawing) {
                $this->logger->warning('Drawing not found', ['id' => $id]);
                return $response->withStatus(404)->withJson(['error' => 'Drawing not found']);
            }
            $this->logger->info('Successfully retrieved drawing', ['id' => $id]);
            $response->getBody()->write(json_encode($drawing));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving drawing', ['id' => $id, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while retrieving the drawing']);
        }
    }

    /**
     * Create a new drawing.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function createDrawing(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for creating a new drawing');
        try {
            $data = $request->getParsedBody();
            $drawing = new Drawing(
                (int)$data['student_id'],
                (int)$data['cohort_id'],
                new \DateTime($data['drawing_date']),
                new \DateTime($data['speaking_date'])
            );
            $id = $this->drawingService->createDrawing($drawing);
            $this->logger->info('Successfully created new drawing', ['id' => $id]);
            $response->getBody()->write(json_encode(['id' => $id]));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error creating new drawing', ['error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while creating the drawing']);
        }
    }

    /**
     * Update an existing drawing.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function updateDrawing(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for updating drawing', ['id' => $id]);
        try {
            $data = $request->getParsedBody();
            $drawing = $this->drawingService->getDrawingById($id);
            if (!$drawing) {
                $this->logger->warning('Drawing not found for update', ['id' => $id]);
                return $response->withStatus(404)->withJson(['error' => 'Drawing not found']);
            }
            $drawing->setStudentId((int)$data['student_id']);
            $drawing->setCohortId((int)$data['cohort_id']);
            $drawing->setDrawingDate(new \DateTime($data['drawing_date']));
            $drawing->setSpeakingDate(new \DateTime($data['speaking_date']));
            $success = $this->drawingService->updateDrawing($drawing);
            $this->logger->info('Successfully updated drawing', ['id' => $id, 'success' => $success]);
            return $response->withJson(['success' => $success]);
        } catch (\Exception $e) {
            $this->logger->error('Error updating drawing', ['id' => $id, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while updating the drawing']);
        }
    }

    /**
     * Delete a drawing.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args The route arguments
     * @return Response
     */
    public function deleteDrawing(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for deleting drawing', ['id' => $id]);
        try {
            $success = $this->drawingService->deleteDrawing($id);
            $this->logger->info('Drawing deletion attempt completed', ['id' => $id, 'success' => $success]);
            return $response->withJson(['success' => $success]);
        } catch (\Exception $e) {
            $this->logger->error('Error deleting drawing', ['id' => $id, 'error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while deleting the drawing']);
        }
    }

    /**
     * Perform a random drawing for a specific cohort and date.
     *
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function performRandomDrawing(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for performing a random drawing');
        try {
            $data = $request->getParsedBody();
            $cohortId = (int)$data['cohort_id'];
            $speakingDate = new \DateTime($data['speaking_date']);
            $drawing = $this->drawingService->performRandomDrawing($cohortId, $speakingDate);
            if (!$drawing) {
                $this->logger->warning('Unable to perform random drawing', ['cohort_id' => $cohortId, 'speaking_date' => $speakingDate->format('Y-m-d')]);
                return $response->withStatus(400)->withJson(['error' => 'Unable to perform random drawing']);
            }
            $this->logger->info('Successfully performed random drawing', ['drawing_id' => $drawing->getId()]);
            $response->getBody()->write(json_encode($drawing));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error performing random drawing', ['error' => $e->getMessage()]);
            return $response->withStatus(500)->withJson(['error' => 'An error occurred while performing the random drawing']);
        }
    }
}
