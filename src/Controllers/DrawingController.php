<?php

namespace App\Controllers;

use App\Models\Drawing;
use App\Services\DrawingService;
use App\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class DrawingController
{
    private $drawingService;
    private $logger;

    public function __construct(DrawingService $drawingService, LoggerInterface $logger)
    {
        $this->drawingService = $drawingService;
        $this->logger = $logger;
    }

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
            throw new HttpException('Une erreur est survenue lors de la récupération des tirages', 500);
        }
    }

    public function getDrawing(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for getting drawing', ['id' => $id]);
        try {
            $drawing = $this->drawingService->getDrawingById($id);
            if (!$drawing) {
                $this->logger->warning('Drawing not found', ['id' => $id]);
                throw new HttpException('Tirage non trouvé', 404);
            }
            $this->logger->info('Successfully retrieved drawing', ['id' => $id]);
            $response->getBody()->write(json_encode($drawing));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving drawing', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la récupération du tirage', 500);
        }
    }

    public function createDrawing(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for creating a new drawing');
        try {
            $data = $request->getParsedBody();
            if (!isset($data['student_id']) || !isset($data['cohort_id']) || !isset($data['drawing_date']) || !isset($data['speaking_date'])) {
                throw new HttpException('Données invalides pour la création du tirage', 400);
            }
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
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error creating new drawing', ['error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la création du tirage', 500);
        }
    }

    public function updateDrawing(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for updating drawing', ['id' => $id]);
        try {
            $data = $request->getParsedBody();
            if (!isset($data['student_id']) || !isset($data['cohort_id']) || !isset($data['drawing_date']) || !isset($data['speaking_date'])) {
                throw new HttpException('Données invalides pour la mise à jour du tirage', 400);
            }
            $drawing = $this->drawingService->getDrawingById($id);
            if (!$drawing) {
                $this->logger->warning('Drawing not found for update', ['id' => $id]);
                throw new HttpException('Tirage non trouvé', 404);
            }
            $drawing->setStudentId((int)$data['student_id']);
            $drawing->setCohortId((int)$data['cohort_id']);
            $drawing->setDrawingDate(new \DateTime($data['drawing_date']));
            $drawing->setSpeakingDate(new \DateTime($data['speaking_date']));
            $success = $this->drawingService->updateDrawing($drawing);
            $this->logger->info('Successfully updated drawing', ['id' => $id, 'success' => $success]);
            return $response->withJson(['success' => $success]);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error updating drawing', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la mise à jour du tirage', 500);
        }
    }

    public function deleteDrawing(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for deleting drawing', ['id' => $id]);
        try {
            $drawing = $this->drawingService->getDrawingById($id);
            if (!$drawing) {
                $this->logger->warning('Drawing not found for deletion', ['id' => $id]);
                throw new HttpException('Tirage non trouvé', 404);
            }
            $success = $this->drawingService->deleteDrawing($id);
            $this->logger->info('Drawing deletion attempt completed', ['id' => $id, 'success' => $success]);
            if (!$success) {
                throw new HttpException('Le tirage n\'a pas pu être supprimé', 500);
            }
            return $response->withJson(['success' => $success]);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error deleting drawing', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la suppression du tirage', 500);
        }
    }

    public function performRandomDrawing(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for performing a random drawing');
        try {
            $data = $request->getParsedBody();
            if (!isset($data['cohort_id']) || !isset($data['speaking_date'])) {
                throw new HttpException('Données invalides pour le tirage aléatoire', 400);
            }
            $cohortId = (int)$data['cohort_id'];
            $speakingDate = new \DateTime($data['speaking_date']);
            $drawing = $this->drawingService->performRandomDrawing($cohortId, $speakingDate);
            $this->logger->info('Successfully performed random drawing', ['drawing_id' => $drawing->getId()]);
            $response->getBody()->write(json_encode($drawing));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error performing random drawing', ['error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors du tirage aléatoire', 500);
        }
    }
}
