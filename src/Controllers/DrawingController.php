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
		$data = $request->getParsedBody();
		$startDate = $data['start_date'] ?? date('Y-m-d');
		$cohortIds = $data['cohort_ids'] ?? [];
	
		$drawingResults = $this->drawingService->performMultipleDayDrawing($startDate, $cohortIds);
	
		if (!empty($drawingResults)) {
			$this->logger->info('Multiple day SOD Drawing performed successfully', ['start_date' => $startDate, 'cohorts' => $cohortIds]);
			$payload = json_encode(['success' => true, 'drawings' => $drawingResults]);
		} else {
			$this->logger->warning('SOD Drawing failed', ['start_date' => $startDate, 'cohorts' => $cohortIds]);
			$payload = json_encode(['success' => false, 'message' => 'Aucun tirage possible pour la période donnée.']);
		}
	
		$response->getBody()->write($payload);
		return $response->withHeader('Content-Type', 'application/json');
	}

}