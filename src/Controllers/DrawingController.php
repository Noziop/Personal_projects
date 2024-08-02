<?php

namespace App\Controllers;

use App\Services\DrawingService;
use App\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

class DrawingController
{
    private $drawingService;
    private $logger;
    private $view;

    public function __construct(DrawingService $drawingService, LoggerInterface $logger, Twig $view)
    {
        $this->drawingService = $drawingService;
        $this->logger = $logger;
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        $this->logger->info('Accessing drawing page');
        return $this->view->render($response, 'tirage/index.twig');
    }

    /**
     * Perform a drawing for Speaker of the Day
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     * @throws HttpException
     */
    public function performDrawing(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for performing a drawing');
        try {
            $data = $request->getParsedBody();
            $this->logger->debug('Received data for drawing', ['data' => $data]);

            if (!isset($data['cohort_ids']) || !isset($data['start_date']) || !isset($data['end_date'])) {
                $this->logger->warning('Invalid data for drawing', ['data' => $data]);
                throw new HttpException('Données invalides pour le tirage au sort', 400);
            }

            $cohortIds = $data['cohort_ids'];
            $startDate = new \DateTime($data['start_date']);
            $endDate = new \DateTime($data['end_date']);

            $drawingResult = $this->drawingService->performDrawing($cohortIds, $startDate, $endDate);
            $this->logger->info('Drawing performed successfully', ['result_count' => count($drawingResult)]);

            $response->getBody()->write(json_encode($drawingResult));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error performing drawing', ['error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors du tirage au sort', 500);
        }
    }

    /**
     * Get all drawings
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     * @throws HttpException
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
            throw new HttpException('Une erreur est survenue lors de la récupération des tirages au sort', 500);
        }
    }

    /**
     * Get a specific drawing by ID
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args Route arguments
     * @return Response
     * @throws HttpException
     */
    public function getDrawing(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for getting drawing', ['id' => $id]);
        try {
            $drawing = $this->drawingService->getDrawingById($id);
            if (!$drawing) {
                $this->logger->warning('Drawing not found', ['id' => $id]);
                throw new HttpException('Tirage au sort non trouvé', 404);
            }
            $this->logger->info('Successfully retrieved drawing', ['id' => $id]);
            $response->getBody()->write(json_encode($drawing));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving drawing', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la récupération du tirage au sort', 500);
        }
    }

    /**
     * Delete a drawing
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @param array $args Route arguments
     * @return Response
     * @throws HttpException
     */
    public function deleteDrawing(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for deleting drawing', ['id' => $id]);
        try {
            $success = $this->drawingService->deleteDrawing($id);
            $this->logger->info('Drawing deletion attempt completed', ['id' => $id, 'success' => $success]);
            if (!$success) {
                throw new HttpException('Le tirage au sort n\'a pas pu être supprimé', 500);
            }
            return $response->withJson(['success' => $success]);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error deleting drawing', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la suppression du tirage au sort', 500);
        }
    }
}