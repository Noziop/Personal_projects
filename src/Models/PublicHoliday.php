<?php

/**
 * PublicHoliday Model
 *
 * This class represents a public holiday in the SOD (Speaker of the Day) application.
 * It is used to store holidays fetched from the French government API via FetchHolidaysService.
 */

namespace App\Models;

class PublicHoliday
{
    /**
     * @var int The unique identifier for the public holiday
     */
    private $id;

    /**
     * @var string The name of the public holiday
     */
    private $name;

    /**
     * @var \DateTime The date of the public holiday
     */
    private $date;

    /**
     * @var int The year for which this holiday is applicable
     */
    private $year;

    /**
     * PublicHoliday constructor.
     *
     * @param string $name The name of the public holiday
     * @param \DateTime $date The date of the public holiday
     * @param int $year The year for which this holiday is applicable
     * @param int|null $id The unique identifier for the public holiday (optional)
     */
    public function __construct(string $name, \DateTime $date, int $year, ?int $id = null)
    {
        $this->name = $name;
        $this->date = $date;
        $this->year = $year;
        $this->id = $id;
    }

    /**
     * Get the public holiday's ID.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set the public holiday's ID.
     *
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get the name of the public holiday.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the name of the public holiday.
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get the date of the public holiday.
     *
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * Set the date of the public holiday.
     *
     * @param \DateTime $date
     * @return void
     */
    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * Get the year for which this holiday is applicable.
     *
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * Set the year for which this holiday is applicable.
     *
     * @param int $year
     * @return void
     */
    public function setYear(int $year): void
    {
        $this->year = $year;
    }
}
