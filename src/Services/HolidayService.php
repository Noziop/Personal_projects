<?php

namespace App\Services;

use App\Models\Holiday;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use DateTime;

class HolidayService
{
    private $holidayModel;
    private $logger;
    private $httpClient;
    private $apiUrl = 'https://calendrier.api.gouv.fr/jours-feries/metropole/';

    public function __construct(Holiday $holidayModel, LoggerInterface $logger, Client $httpClient)
    {
        $this->holidayModel = $holidayModel;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }

    public function createHoliday($date, $description)
    {
        $this->logger->info('Creating new holiday', [
            'date' => $date,
            'description' => $description
        ]);

        return $this->holidayModel->create($date, $description);
    }

    public function getHolidayById($id)
    {
        $this->logger->info('Fetching holiday by ID', ['id' => $id]);
        return $this->holidayModel->findById($id);
    }

    public function updateHoliday($id, $date, $description)
    {
        $this->logger->info('Updating holiday', [
            'id' => $id,
            'date' => $date,
            'description' => $description
        ]);

        return $this->holidayModel->update($id, $date, $description);
    }

    public function deleteHoliday($id)
    {
        $this->logger->info('Deleting holiday', ['id' => $id]);
        return $this->holidayModel->delete($id);
    }

    public function getAllHolidays()
    {
        $this->logger->info('Fetching all holidays');
        return $this->holidayModel->findAll();
    }

    public function getHolidaysByDateRange($startDate, $endDate)
    {
        $this->logger->info('Fetching holidays by date range', [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        return $this->holidayModel->findByDateRange($startDate, $endDate);
    }

    public function isHoliday($date)
    {
        $this->logger->info('Checking if date is a holiday', ['date' => $date]);
        return $this->holidayModel->isHoliday($date);
    }

    public function syncHolidaysWithGovernmentAPI($year = null)
    {
        $year = $year ?? date('Y');
        $this->logger->info('Syncing holidays with government API', ['year' => $year]);

        try {
            $response = $this->httpClient->request('GET', $this->apiUrl . $year);
            $holidays = json_decode($response->getBody(), true);

            foreach ($holidays as $date => $description) {
                $existingHoliday = $this->holidayModel->findByDate($date);
                if ($existingHoliday) {
                    $this->updateHoliday($existingHoliday['id'], $date, $description);
                } else {
                    $this->createHoliday($date, $description);
                }
            }

            $this->logger->info('Holiday sync completed successfully');
            return true;
        } catch (GuzzleException $e) {
            $this->logger->error('Error syncing holidays with API', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function syncHolidaysForYearRange($startYear, $endYear)
    {
        $this->logger->info('Syncing holidays for year range', [
            'start_year' => $startYear,
            'end_year' => $endYear
        ]);

        for ($year = $startYear; $year <= $endYear; $year++) {
            $this->syncHolidaysWithGovernmentAPI($year);
        }
    }

    public function cleanupOldHolidays()
    {
        $today = new DateTime();
        $this->holidayModel->deleteOlderThan($today);
    }

    public function syncHolidays()
    {
        $this->logger->info('Starting holiday synchronization');
        
        try {
            $this->cleanupOldHolidays();
            $currentYear = (int)date('Y');
            $this->syncHolidaysForYearRange($currentYear, $currentYear + 2);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error during holiday synchronization', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
