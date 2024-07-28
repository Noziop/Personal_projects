<?php

/**
 * Cohort Model
 *
 * This class represents a cohort in the SOD (Speaker of the Day) application.
 * It contains properties and methods related to cohort management.
 */

namespace App\Models;

class Cohort
{
    /**
     * @var int The unique identifier for the cohort
     */
    private $id;

    /**
     * @var string The name of the cohort
     */
    private $name;

    /**
     * @var \DateTime The start date of the cohort
     */
    private $startDate;

    /**
     * @var \DateTime The end date of the cohort
     */
    private $endDate;

    /**
     * Cohort constructor.
     *
     * @param string $name The name of the cohort
     * @param \DateTime $startDate The start date of the cohort
     * @param \DateTime $endDate The end date of the cohort
     * @param int|null $id The unique identifier for the cohort (optional)
     */
    public function __construct(string $name, \DateTime $startDate, \DateTime $endDate, ?int $id = null)
    {
        $this->name = $name;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->id = $id;
    }

    /**
     * Get the cohort's ID.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set the cohort's ID.
     *
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get the cohort's name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the cohort's name.
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get the cohort's start date.
     *
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    /**
     * Set the cohort's start date.
     *
     * @param \DateTime $startDate
     * @return void
     */
    public function setStartDate(\DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * Get the cohort's end date.
     *
     * @return \DateTime
     */
    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    /**
     * Set the cohort's end date.
     *
     * @param \DateTime $endDate
     * @return void
     */
    public function setEndDate(\DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }
}
