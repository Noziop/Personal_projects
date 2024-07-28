<?php

namespace App\Controllers;

use App\Models\Unavailability;
use App\Services\UnavailabilityService;
use App\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class UnavailabilityController
{
    private $unavailabilityService;
    private $logger;

    public function __construct(UnavailabilityService $unavailabilityService, LoggerInterface $logger)
    {
        $this->unavailabilityService = $unavailabilityService;
        $this->logger = $logger;
    }

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
            throw new HttpException('Une erreur est survenue lors de la récupération des indisponibilités', 500);
        }
    }

    public function getUnavailability(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for getting unavailability', ['id' => $id]);
        try {
            $unavailability = $this->unavailabilityService->getUnavailabilityById($id);
            if (!$unavailability) {
                $this->logger->warning('Unavailability not found', ['id' => $id]);
                throw new HttpException('Indisponibilité non trouvée', 404);
            }
            $this->logger->info('Successfully retrieved unavailability', ['id' => $id]);
            $response->getBody()->write(json_encode($unavailability));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving unavailability', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la récupération de l\'indisponibilité', 500);
        }
    }

    public function createUnavailability(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for creating a new unavailability');
        try {
            $data = $request->getParsedBody();
            if (!isset($data['student_id']) || !isset($data['start_date_time']) || !isset($data['end_date_time'])) {
                throw new HttpException('Données invalides pour la création de l\'indisponibilité', 400);
            }
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
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error creating new unavailability', ['error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la création de l\'indisponibilité', 500);
        }
    }

    public function updateUnavailability(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for updating unavailability', ['id' => $id]);
        try {
            $data = $request->getParsedBody();
            if (!isset($data['student_id']) || !isset($data['start_date_time']) || !isset($data['end_date_time'])) {
                throw new HttpException('Données invalides pour la mise à jour de l\'indisponibilité', 400);
            }
            $unavailability = $this->unavailabilityService->getUnavailabilityById($id);
            if (!$unavailability) {
                $this->logger->warning('Unavailability not found for update', ['id' => $id]);
                throw new HttpException('Indisponibilité non trouvée', 404);
            }
            $unavailability->setStudentId((int)$data['student_id']);
            $unavailability->setStartDateTime(new \DateTime($data['start_date_time']));
            $unavailability->setEndDateTime(new \DateTime($data['end_date_time']));
            $unavailability->setReason($data['reason'] ?? null);
            $success = $this->unavailabilityService->updateUnavailability($unavailability);
            $this->logger->info('Successfully updated unavailability', ['id' => $id, 'success' => $success]);
            return $response->withJson(['success' => $success]);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error updating unavailability', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la mise à jour de l\'indisponibilité', 500);
        }
    }

    public function deleteUnavailability(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for deleting unavailability', ['id' => $id]);
        try {
            $unavailability = $this->unavailabilityService->getUnavailabilityById($id);
            if (!$unavailability) {
                $this->logger->warning('Unavailability not found for deletion', ['id' => $id]);
                throw new HttpException('Indisponibilité non trouvée', 404);
            }
            $success = $this->unavailabilityService->deleteUnavailability($id);
            $this->logger->info('Unavailability deletion attempt completed', ['id' => $id, 'success' => $success]);
            if (!$success) {
                throw new HttpException('L\'indisponibilité n\'a pas pu être supprimée', 500);
            }
            return $response->withJson(['success' => $success]);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error deleting unavailability', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la suppression de l\'indisponibilité', 500);
        }
    }

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
            throw new HttpException('Une erreur est survenue lors de la récupération des indisponibilités pour cet étudiant', 500);
        }
    }
}
