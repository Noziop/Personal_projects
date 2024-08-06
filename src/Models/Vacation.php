<?php

/**
 * Vacation Model
 *
 * This class represents a vacation period for a cohort in the SOD (Speaker of the Day) application.
 * It is used to track periods when no drawings or speaking events should occur for a specific cohort.
 */

namespace App\Models;

class Vacation
{
    /**
     * @var int The unique identifier for the vacation period
     */
    private $id;

    /**
     * @var int The ID of the cohort this vacation period applies to
     */
    private $cohortId;

    /**
     * @var string The name or description of the vacation period
     */
    private $name;

    /**
     * @var \DateTime The start date of the vacation period
     */
    private $startDate;

    /**
     * @var \DateTime The end date of the vacation period
     */
    private $endDate;

    /**
     * Vacation constructor.
     *
     * @param int $cohortId The ID of the cohort this vacation period applies to
     * @param string $name The name or description of the vacation period
     * @param \DateTime $startDate The start date of the vacation period
     * @param \DateTime $endDate The end date of the vacation period
     * @param int|null $id The unique identifier for the vacation period (optional)
     */
    public function __construct(int $cohortId, string $name, \DateTime $startDate, \DateTime $endDate, ?int $id = null)
    {
        $this->cohortId = $cohortId;
        $this->name = $name;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->id = $id;
    }

    /**
     * Get the vacation's ID.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set the vacation's ID.
     *
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get the ID of the cohort this vacation period applies to.
     *
     * @return int
     */
    public function getCohortId(): int
    {
        return $this->cohortId;
    }

    /**
     * Set the ID of the cohort this vacation period applies to.
     *
     * @param int $cohortId
     * @return void
     */
    public function setCohortId(int $cohortId): void
    {
        $this->cohortId = $cohortId;
    }

    /**
     * Get the name or description of the vacation period.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the name or description of the vacation period.
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get the start date of the vacation period.
     *
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    /**
     * Set the start date of the vacation period.
     *
     * @param \DateTime $startDate
     * @return void
     */
    public function setStartDate(\DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * Get the end date of the vacation period.
     *
     * @return \DateTime
     */
    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    /**
     * Set the end date of the vacation period.
     *
     * @param \DateTime $endDate
     * @return void
     */
    public function setEndDate(\DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }
}
