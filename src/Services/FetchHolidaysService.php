<?php

/**
 * FetchHolidaysService
 *
 * This service is responsible for fetching public holidays from the French government API
 * and storing them in the database for use in the SOD (Speaker of the Day) application.
 */

namespace App\Services;

use App\Models\PublicHoliday;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PDO;
use Psr\Log\LoggerInterface;

class FetchHolidaysService
{
    /**
     * @var Client The HTTP client used to make API requests
     */
    private $client;

    /**
     * @var PDO The database connection
     */
    private $db;

    /**
     * @var LoggerInterface The logger
     */
    private $logger;

    /**
     * @var string The base URL for the French government's public holiday API
     */
    private const API_BASE_URL = 'https://calendrier.api.gouv.fr/jours-feries/';

    /**
     * FetchHolidaysService constructor.
     *
     * @param PDO $db The database connection
     * @param LoggerInterface $logger The logger
     */
    public function __construct(PDO $db, LoggerInterface $logger)
    {
        $this->client = new Client();
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Fetch public holidays for a specific year and store them in the database.
     *
     * @param int $year The year for which to fetch holidays
     * @return array An array of PublicHoliday objects
     * @throws \Exception If there's an error fetching or storing the holidays
     */
    public function fetchAndStoreHolidays(int $year): array
    {
        $this->logger->info('Starting to fetch and store holidays', ['year' => $year]);
        try {
            $holidays = $this->fetchHolidays($year);
            $this->storeHolidays($holidays, $year);
            $this->logger->info('Successfully fetched and stored holidays', ['year' => $year, 'count' => count($holidays)]);
            return $holidays;
        } catch (\Exception $e) {
            $this->logger->error('Error fetching or storing holidays', ['year' => $year, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Fetch public holidays from the API for a specific year.
     *
     * @param int $year The year for which to fetch holidays
     * @return array An array of PublicHoliday objects
     * @throws GuzzleException If there's an error with the API request
     */
    private function fetchHolidays(int $year): array
    {
        $this->logger->info('Fetching holidays from API', ['year' => $year]);
        try {
            $response = $this->client->request('GET', self::API_BASE_URL . "metropole/$year.json");
            $data = json_decode($response->getBody(), true);

            if (!is_array($data)) {
                throw new \RuntimeException('Invalid API response format');
            }

            $holidays = [];
            foreach ($data as $date => $name) {
                $holidays[] = new PublicHoliday($name, new \DateTime($date), $year);
            }

            $this->logger->info('Successfully fetched holidays from API', ['year' => $year, 'count' => count($holidays)]);
            return $holidays;
        } catch (GuzzleException $e) {
            $this->logger->error('Error fetching holidays from API', ['year' => $year, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Store the fetched holidays in the database.
     *
     * @param array $holidays An array of PublicHoliday objects
     * @param int $year The year of the holidays
     * @throws \PDOException If there's an error inserting into the database
     */
    private function storeHolidays(array $holidays, int $year): void
    {
        $this->logger->info('Storing holidays in database', ['year' => $year, 'count' => count($holidays)]);
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("INSERT INTO public_holidays (name, date, year) VALUES (:name, :date, :year)");

            foreach ($holidays as $holiday) {
                $stmt->execute([
                    ':name' => $holiday->getName(),
                    ':date' => $holiday->getDate()->format('Y-m-d'),
                    ':year' => $year
                ]);
            }

            $this->db->commit();
            $this->logger->info('Successfully stored holidays in database', ['year' => $year, 'count' => count($holidays)]);
        } catch (\PDOException $e) {
            $this->db->rollBack();
            $this->logger->error('Error storing holidays in database', ['year' => $year, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Clear existing holidays for a specific year from the database.
     *
     * @param int $year The year for which to clear holidays
     * @throws \PDOException If there's an error deleting from the database
     */
    public function clearHolidays(int $year): void
    {
        $this->logger->info('Clearing existing holidays from database', ['year' => $year]);
        try {
            $stmt = $this->db->prepare("DELETE FROM public_holidays WHERE year = :year");
            $stmt->execute([':year' => $year]);
            $this->logger->info('Successfully cleared existing holidays', ['year' => $year]);
        } catch (\PDOException $e) {
            $this->logger->error('Error clearing existing holidays', ['year' => $year, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
