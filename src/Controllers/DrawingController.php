<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\DrawingService;
use App\Services\CohortService;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;

class DrawingController
{
    private $view;
    private $drawingService;
    private $cohortService;
    private $logger;

    public function __construct(Twig $view, DrawingService $drawingService, CohortService $cohortService, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->drawingService = $drawingService;
        $this->cohortService = $cohortService;
        $this->logger = $logger;
    }

	public function index(Request $request, Response $response): Response
	{
		$cohorts = $this->cohortService->getAllCohorts();
		return $this->view->render($response, 'drawing/index.twig', ['cohorts' => $cohorts]);
	}
	
	public function viewDrawingHistory(Request $request, Response $response): Response
	{
		$drawings = $this->drawingService->getDrawingHistory();
		return $this->view->render($response, 'drawing/history.twig', ['drawings' => $drawings]);
	}

	public function performDrawing(Request $request, Response $response): Response
	{
		try {
			$data = $request->getParsedBody();
			$date = $data['date'] ?? date('Y-m-d');
			$cohortIds = $data['cohort_ids'] ?? [];
	
			$result = $this->drawingService->performSODDrawing($date, $cohortIds);
	
			if ($result) {
				$payload = json_encode(['success' => true, 'student' => $result]);
				$response->getBody()->write($payload);
				return $response->withHeader('Content-Type', 'application/json');
			} else {
				$payload = json_encode(['success' => false, 'message' => 'Aucun étudiant éligible pour le tirage au sort.']);
				$response->getBody()->write($payload);
				return $response->withHeader('Content-Type', 'application/json');
			}
		} catch (\Exception $e) {
			$this->logger->error('Error during drawing', ['error' => $e->getMessage()]);
			$payload = json_encode(['success' => false, 'message' => 'Une erreur est survenue lors du tirage au sort.']);
			$response->getBody()->write($payload);
			return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
		}
	}

}