<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\DrawingService;
use App\Services\CohortService;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;

class DrawingController
{
    private $view;
    private $drawingService;
    private $cohortService;
    private $drawingLogger;
	private $flash;

    public function __construct(Twig $view, DrawingService $drawingService, CohortService $cohortService, LoggerInterface $drawingLogger, Messages $flash)
    {
        $this->view = $view;
        $this->drawingService = $drawingService;
        $this->cohortService = $cohortService;
        $this->drawingLogger = $drawingLogger;
		$this->flash = $flash;
    }

	public function index(Request $request, Response $response): Response
	{
		$cohorts = $this->cohortService->getAllCohorts();
		return $this->view->render($response, 'drawing/index.twig', ['cohorts' => $cohorts]);
	}

	public function archiveDrawings(Request $request, Response $response): Response
	{
		$this->drawingLogger->info('Archiving drawings');
		
		$result = $this->drawingService->archiveDrawings();
		
		if ($result) {
			$this->drawingLogger->info('Drawings archived successfully');
			$this->flash->addMessage('success', 'Tirages archivés avec succès.');
			$payload = json_encode(['success' => true, 'message' => 'Tirages archivés avec succès']);
		} else {
			$this->drawingLogger->error('Failed to archive drawings');
			$this->flash->addMessage('error', 'Erreur lors de l\'archivage des tirages.');
			$payload = json_encode(['success' => false, 'message' => 'Erreur lors de l\'archivage des tirages']);
		}
	
		$response->getBody()->write($payload);
		return $response->withHeader('Content-Type', 'application/json');
	}
	
	public function resetDrawings(Request $request, Response $response): Response
	{
		$this->drawingLogger->info('Resetting drawings');
		
		$result = $this->drawingService->resetDrawings();
		
		if ($result) {
			$this->drawingLogger->info('Drawings reset successfully');
			$this->flash->addMessage('success', 'Tirages réinitialisés avec succès.');
			$payload = json_encode(['success' => true, 'message' => 'Tirages réinitialisés avec succès']);
		} else {
			$this->drawingLogger->error('Failed to reset drawings');
			$this->flash->addMessage('error', 'Erreur lors de la réinitialisation des tirages.');
			$payload = json_encode(['success' => false, 'message' => 'Erreur lors de la réinitialisation des tirages']);
		}
	
		$response->getBody()->write($payload);
		return $response->withHeader('Content-Type', 'application/json');
	}	

	private function jsonResponse(Response $response, array $data): Response
	{
		$payload = json_encode($data);
		$response->getBody()->write($payload);
		return $response->withHeader('Content-Type', 'application/json');
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
	
		$this->drawingLogger->info('Performing drawing', ['start_date' => $startDate, 'cohort_ids' => $cohortIds]);
	
		$drawingResults = $this->drawingService->performMultipleDayDrawing($startDate, $cohortIds);
	
		if (!empty($drawingResults)) {
			$this->drawingLogger->info('Multiple day SOD Drawing performed successfully', ['results_count' => count($drawingResults)]);
			$this->flash->addMessage('success', 'Tirage au sort effectué avec succès. ' . count($drawingResults) . ' tirages réalisés.');
			$payload = json_encode(['success' => true, 'message' => 'Tirage au sort effectué avec succès', 'drawings' => $drawingResults]);
		} else {
			$this->drawingLogger->warning('SOD Drawing failed', ['start_date' => $startDate, 'cohorts' => $cohortIds]);
			$this->flash->addMessage('error', 'Aucun tirage possible pour la période donnée.');
			$payload = json_encode(['success' => false, 'message' => 'Aucun tirage possible pour la période donnée.']);
		}
	
		$response->getBody()->write($payload);
		return $response->withHeader('Content-Type', 'application/json');
	}
	

}