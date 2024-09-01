<?php

namespace App\Services;

use DateTime;
use App\Models\Drawing;
use App\Models\Student;
use App\Models\Cohort;
use App\Models\DrawingDay;
use App\Models\Vacation;
use App\Models\Holiday;
use App\Models\Unavailability;
use Psr\Log\LoggerInterface;

class DrawingService
{
    private $drawingModel;
    private $studentModel;
    private $cohortModel;
    private $drawingDayModel;
    private $vacationModel;
    private $holidayModel;
    private $unavailabilityModel;
    private $drawingLogger;

    public function __construct(
        Drawing $drawingModel,
        Student $studentModel,
        Cohort $cohortModel,
        DrawingDay $drawingDayModel,
        Vacation $vacationModel,
        Holiday $holidayModel,
        Unavailability $unavailabilityModel,
        LoggerInterface $drawingLogger
    ) {
        $this->drawingModel = $drawingModel;
        $this->studentModel = $studentModel;
        $this->cohortModel = $cohortModel;
        $this->drawingDayModel = $drawingDayModel;
        $this->vacationModel = $vacationModel;
        $this->holidayModel = $holidayModel;
        $this->unavailabilityModel = $unavailabilityModel;
		$this->drawingLogger = $drawingLogger;
    }

    public function performSODDrawing($date, $cohortIds)
    {
        if (!$this->isDrawingAllowed($date, $cohortIds)) {
            $this->drawingLogger->info("Drawing not allowed for date: $date");
            return false;
        }

        $eligibleStudents = $this->getEligibleStudents($date, $cohortIds);

        if (empty($eligibleStudents)) {
            $this->drawingLogger->info("No eligible students found for drawing on date: $date");
            return false;
        }

        $selectedStudent = $this->selectStudent($eligibleStudents);

        $this->saveDrawingResult($date, $selectedStudent);

        return $selectedStudent;
    }

	private function isDrawingAllowed($date, $cohortIds)
	{
		$this->drawingLogger->info("Checking if drawing is allowed", ['date' => $date, 'cohort_ids' => $cohortIds]);
	
		$isDrawingDay = $this->drawingDayModel->isDrawingDayForCohorts($date, $cohortIds);
		$this->drawingLogger->info("Is drawing day", ['result' => $isDrawingDay]);
	
		$isHoliday = $this->holidayModel->isHoliday($date);
		$this->drawingLogger->info("Is holiday", ['result' => $isHoliday]);
	
		$vacationCohorts = $this->vacationModel->isVacationForAnyCohort($date, $cohortIds);
		$this->drawingLogger->info("Vacation cohorts", ['cohorts' => $vacationCohorts]);
	
		$availableCohorts = array_diff($cohortIds, $vacationCohorts);
		$this->drawingLogger->info("Available cohorts", ['cohorts' => $availableCohorts]);
	
		$isAllowed = $isDrawingDay && !$isHoliday && !empty($availableCohorts);
		$this->drawingLogger->info("Drawing allowed", ['result' => $isAllowed]);
	
		return $isAllowed;
	}

	private function getEligibleStudents($date, $cohortIds)
	{
		$allStudents = $this->studentModel->getStudentsByCohorts($cohortIds);
		$eligibleStudents = [];
	
		foreach ($allStudents as $student) {
			if ($this->isStudentEligible($student, $date)) {
				$eligibleStudents[] = $student;
			} else {
				$this->drawingLogger->info("Student {$student['id']} not eligible for date {$date}");
			}
		}
	
		return $eligibleStudents;
	}

	private function isStudentEligible($student, $date)
	{
		if ($this->unavailabilityModel->isStudentUnavailable($student['id'], $date)) {
			$this->drawingLogger->info("Student {$student['id']} unavailable on {$date}");
			return false;
		}
	
		$lastDrawing = $this->drawingModel->getLastDrawingForStudent($date, $student['id']);
		if ($lastDrawing && $this->isDrawingTooRecent($lastDrawing, $date)) {
			$this->drawingLogger->info("Last drawing for student {$student['id']} too recent");
			return false;
		}
	
		$drawingsCount = $this->drawingModel->getDrawingsCountForStudent($student['id']);
		$studentCohort = $this->cohortModel->getCohortById($student['cohort_id']);
		$expectedDrawings = $this->calculateExpectedDrawings($studentCohort, $date);
	
		$this->drawingLogger->info("Student {$student['id']}: Drawings count: {$drawingsCount}, Expected: {$expectedDrawings}");
	
		return $drawingsCount < $expectedDrawings;
	}

    private function selectStudent($eligibleStudents)
    {
        $weights = [];
        foreach ($eligibleStudents as $index => $student) {
            $drawingsCount = $this->drawingModel->getDrawingsCountForStudent($student['id']);
            $weights[$index] = 1 / ($drawingsCount + 1);
        }

        return $this->weightedRandomChoice($eligibleStudents, $weights);
    }

    private function weightedRandomChoice($items, $weights)
    {
        $totalWeight = array_sum($weights);
        $randomNumber = mt_rand() / mt_getrandmax() * $totalWeight;

        foreach ($items as $index => $item) {
            if (($randomNumber -= $weights[$index]) <= 0) {
                return $item;
            }
        }
    }

    private function saveDrawingResult($date, $student)
    {
        $this->drawingModel->createDrawing([
            'student_id' => $student['id'],
            'drawing_date' => $date,
            'presentation_date' => $date
        ]);
    }

	private function isDrawingTooRecent($lastDrawing, $currentDate)
	{
		if (!$lastDrawing) {
			return false;
		}
		$daysSinceLastDrawing = (strtotime($currentDate) - strtotime($lastDrawing['drawing_date'])) / (60 * 60 * 24);
		return $daysSinceLastDrawing < 14; // Pas plus d'un tirage toutes les 2 semaines
	}

    private function calculateExpectedDrawings($cohort, $currentDate)
    {
        $daysSinceStart = (strtotime($currentDate) - strtotime($cohort['start_date'])) / (60 * 60 * 24);
        $totalDays = (strtotime($cohort['end_date']) - strtotime($cohort['start_date'])) / (60 * 60 * 24);
        
        $progress = $daysSinceStart / $totalDays;
        
        if ($progress < 0.33) {
            return 1;
        } elseif ($progress < 0.67) {
            return 2;
        } else {
            return 3;
        }
    }

	public function performMultipleDayDrawing($startDate, $cohortIds)
	{
		$this->drawingLogger->info("Starting multiple day drawing", ['start_date' => $startDate, 'cohort_ids' => $cohortIds]);
	
		$drawingResults = [];
		$currentDate = new \DateTime($startDate);
		$endDate = (new \DateTime($startDate))->modify('+3 months');
	
		while ($currentDate <= $endDate) {
			$dateString = $currentDate->format('Y-m-d');
			$this->drawingLogger->info("Processing date", ['date' => $dateString]);
	
			$vacationCohorts = $this->vacationModel->isVacationForAnyCohort($dateString, $cohortIds);
			$availableCohorts = array_diff($cohortIds, $vacationCohorts);
	
			$this->drawingLogger->info("Available cohorts", ['cohorts' => $availableCohorts]);
	
			if (!empty($availableCohorts) && $this->isDrawingAllowed($dateString, $availableCohorts)) {
				$eligibleStudents = $this->getEligibleStudents($dateString, $availableCohorts);
				$this->drawingLogger->info("Eligible students count", ['count' => count($eligibleStudents)]);
	
				if (!empty($eligibleStudents)) {
					$selectedStudent = $this->selectStudent($eligibleStudents);
					$drawingResults[$dateString] = $selectedStudent;
					$this->saveDrawingResult($dateString, $selectedStudent);
					$this->drawingLogger->info("Selected student", ['student_id' => $selectedStudent['id'], 'date' => $dateString]);
				} else {
					$this->drawingLogger->info("No eligible students for date", ['date' => $dateString]);
				}
			} else {
				$this->drawingLogger->info("Drawing not allowed or no available cohorts", ['date' => $dateString]);
			}
	
			$currentDate->modify('+1 day');
		}
	
		$this->drawingLogger->info("Multiple day drawing completed", ['results_count' => count($drawingResults)]);
	
		return $drawingResults;
	}

	public function getDrawingHistory()
	{
		return $this->drawingModel->getAllDrawings();
	}

	public function archiveDrawings()
    {
        try {
            $result = $this->drawingModel->archiveDrawings();
            $this->drawingLogger->info('Drawings archived successfully');
            return $result;
        } catch (\Exception $e) {
            $this->drawingLogger->error('Error archiving drawings: ' . $e->getMessage());
            return false;
        }
    }

    public function resetDrawings()
    {
        try {
            $result = $this->drawingModel->resetDrawings();
            $this->drawingLogger->info('Drawings reset successfully');
            return $result;
        } catch (\Exception $e) {
            $this->drawingLogger->error('Error resetting drawings: ' . $e->getMessage());
            return false;
        }
    }
}