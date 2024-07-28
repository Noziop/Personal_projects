<?php

namespace App\Controllers;

use App\Models\Cohort;
use App\Services\CohortService;
use App\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class CohortController
{
    private $cohortService;
    private $logger;

    public function __construct(CohortService $cohortService, LoggerInterface $logger)
    {
        $this->cohortService = $cohortService;
        $this->logger = $logger;
    }

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
            throw new HttpException('Une erreur est survenue lors de la récupération des cohortes', 500);
        }
    }

    public function getCohort(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for getting cohort', ['id' => $id]);
        try {
            $cohort = $this->cohortService->getCohortById($id);
            if (!$cohort) {
                $this->logger->warning('Cohort not found', ['id' => $id]);
                throw new HttpException('Cohorte non trouvée', 404);
            }
            $this->logger->info('Successfully retrieved cohort', ['id' => $id]);
            $response->getBody()->write(json_encode($cohort));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving cohort', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la récupération de la cohorte', 500);
        }
    }

    public function createCohort(Request $request, Response $response): Response
    {
        $this->logger->info('Request received for creating a new cohort');
        try {
            $data = $request->getParsedBody();
            if (!isset($data['name']) || !isset($data['start_date']) || !isset($data['end_date'])) {
                throw new HttpException('Données invalides pour la création de la cohorte', 400);
            }
            $cohort = new Cohort(
                $data['name'],
                new \DateTime($data['start_date']),
                new \DateTime($data['end_date'])
            );
            $id = $this->cohortService->createCohort($cohort);
            $this->logger->info('Successfully created new cohort', ['id' => $id]);
            $response->getBody()->write(json_encode(['id' => $id]));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error creating new cohort', ['error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la création de la cohorte', 500);
        }
    }

    public function updateCohort(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for updating cohort', ['id' => $id]);
        try {
            $data = $request->getParsedBody();
            if (!isset($data['name']) || !isset($data['start_date']) || !isset($data['end_date'])) {
                throw new HttpException('Données invalides pour la mise à jour de la cohorte', 400);
            }
            $cohort = $this->cohortService->getCohortById($id);
            if (!$cohort) {
                $this->logger->warning('Cohort not found for update', ['id' => $id]);
                throw new HttpException('Cohorte non trouvée', 404);
            }
            $cohort->setName($data['name']);
            $cohort->setStartDate(new \DateTime($data['start_date']));
            $cohort->setEndDate(new \DateTime($data['end_date']));
            $success = $this->cohortService->updateCohort($cohort);
            $this->logger->info('Successfully updated cohort', ['id' => $id, 'success' => $success]);
            return $response->withJson(['success' => $success]);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error updating cohort', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la mise à jour de la cohorte', 500);
        }
    }

    public function deleteCohort(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $this->logger->info('Request received for deleting cohort', ['id' => $id]);
        try {
            $cohort = $this->cohortService->getCohortById($id);
            if (!$cohort) {
                $this->logger->warning('Cohort not found for deletion', ['id' => $id]);
                throw new HttpException('Cohorte non trouvée', 404);
            }
            $success = $this->cohortService->deleteCohort($id);
            $this->logger->info('Cohort deletion attempt completed', ['id' => $id, 'success' => $success]);
            if (!$success) {
                throw new HttpException('La cohorte n\'a pas pu être supprimée', 500);
            }
            return $response->withJson(['success' => $success]);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error deleting cohort', ['id' => $id, 'error' => $e->getMessage()]);
            throw new HttpException('Une erreur est survenue lors de la suppression de la cohorte', 500);
        }
    }
}
