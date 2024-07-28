<?php

namespace App\Controllers;

use App\Models\Vacation;
use App\Services\VacationService;
use App\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class VacationController
{
    private $vacationService;
    private $logger;

    public function __construct(VacationService $vacationService, LoggerInterface $logger)
    {
        $this->vacationService = $vacationService;
        $this->logger = $logger;
    }

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
            throw new HttpException('Une erreur est survenue lors de la récupération des vacances', 500);
        }
    }

    public function getVacation(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for getting vacation', ['id' => $id]);
        try {
            $vacation = $this->vacationService->getVacationById($id);
            if (!$vacation) {
                $this->logger->warning('Vacation not found', ['id' => $id]);
                throw new HttpException('Vacances non trouvées', 404);
            }
            $this->logger->info('Successfully retrieved vacation', ['id' => $id]);
            $response->getBody()->write(json_encode($vacation));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving vacation', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la récupération des vacances', 500);
        }
    }

    public function createVacation(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for creating a new vacation');
        try {
            $data = $request->getParsedBody();
            if (!isset($data['cohort_id']) || !isset($data['name']) || !isset($data['start_date']) || !isset($data['end_date'])) {
                throw new HttpException('Données invalides pour la création des vacances', 400);
            }
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
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error creating new vacation', ['error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la création des vacances', 500);
        }
    }

    public function updateVacation(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for updating vacation', ['id' => $id]);
        try {
            $data = $request->getParsedBody();
            if (!isset($data['cohort_id']) || !isset($data['name']) || !isset($data['start_date']) || !isset($data['end_date'])) {
                throw new HttpException('Données invalides pour la mise à jour des vacances', 400);
            }
            $vacation = $this->vacationService->getVacationById($id);
            if (!$vacation) {
                $this->logger->warning('Vacation not found for update', ['id' => $id]);
                throw new HttpException('Vacances non trouvées', 404);
            }
            $vacation->setCohortId((int)$data['cohort_id']);
            $vacation->setName($data['name']);
            $vacation->setStartDate(new \DateTime($data['start_date']));
            $vacation->setEndDate(new \DateTime($data['end_date']));
            $success = $this->vacationService->updateVacation($vacation);
            $this->logger->info('Successfully updated vacation', ['id' => $id, 'success' => $success]);
            return $response->withJson(['success' => $success]);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error updating vacation', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la mise à jour des vacances', 500);
        }
    }

    public function deleteVacation(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for deleting vacation', ['id' => $id]);
        try {
            $vacation = $this->vacationService->getVacationById($id);
            if (!$vacation) {
                $this->logger->warning('Vacation not found for deletion', ['id' => $id]);
                throw new HttpException('Vacances non trouvées', 404);
            }
            $success = $this->vacationService->deleteVacation($id);
            $this->logger->info('Vacation deletion attempt completed', ['id' => $id, 'success' => $success]);
            if (!$success) {
                throw new HttpException('Les vacances n\'ont pas pu être supprimées', 500);
            }
            return $response->withJson(['success' => $success]);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error deleting vacation', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la suppression des vacances', 500);
        }
    }

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
            throw new HttpException('Une erreur est survenue lors de la récupération des vacances pour cette cohorte', 500);
        }
    }
}
