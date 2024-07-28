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
     * @var string The base URL for the French government's public holiday API
     */
    private const API_BASE_URL = 'https://calendrier.api.gouv.fr/jours-feries/';

    /**
     * FetchHolidaysService constructor.
     *
     * @param PDO $db The database connection
     */
    public function __construct(PDO $db)
    {
        $this->client = new Client();
        $this->db = $db;
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
        try {
            $holidays = $this->fetchHolidays($year);
            $this->storeHolidays($holidays, $year);
            return $holidays;
        } catch (\Exception $e) {
            // Log the error
            error_log("Error fetching or storing holidays for year $year: " . $e->getMessage());
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
        $response = $this->client->request('GET', self::API_BASE_URL . "metropole/$year.json");
        $data = json_decode($response->getBody(), true);

        $holidays = [];
        foreach ($data as $date => $name) {
            $holidays[] = new PublicHoliday($name, new \DateTime($date), $year);
        }

        return $holidays;
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
        $stmt = $this->db->prepare("INSERT INTO public_holidays (name, date, year) VALUES (:name, :date, :year)");

        foreach ($holidays as $holiday) {
            $stmt->execute([
                ':name' => $holiday->getName(),
                ':date' => $holiday->getDate()->format('Y-m-d'),
                ':year' => $year
            ]);
        }
    }
}
