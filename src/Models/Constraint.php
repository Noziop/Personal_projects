<?php

/**
 * Constraint Model
 *
 * This class represents a constraint in the SOD (Speaker of the Day) application.
 * Constraints can be used to define periods when drawings cannot occur, such as holidays or specific unavailable dates.
 */

namespace App\Models;

class Constraint
{
    /**
     * @var int The unique identifier for the constraint
     */
    private $id;

    /**
     * @var int The ID of the cohort this constraint applies to (null if it applies to all cohorts)
     */
    private $cohortId;

    /**
     * @var string The type of constraint (e.g., 'holiday', 'unavailable_date')
     */
    private $type;

    /**
     * @var \DateTime The start date of the constraint
     */
    private $startDate;

    /**
     * @var \DateTime The end date of the constraint
     */
    private $endDate;

    /**
     * @var string Additional description or reason for the constraint
     */
    private $description;

    /**
     * Constraint constructor.
     *
     * @param string $type The type of constraint
     * @param \DateTime $startDate The start date of the constraint
     * @param \DateTime $endDate The end date of the constraint
     * @param int|null $cohortId The ID of the cohort this constraint applies to (optional)
     * @param string|null $description Additional description (optional)
     * @param int|null $id The unique identifier for the constraint (optional)
     */
    public function __construct(string $type, \DateTime $startDate, \DateTime $endDate, ?int $cohortId = null, ?string $description = null, ?int $id = null)
    {
        $this->type = $type;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->cohortId = $cohortId;
        $this->description = $description;
        $this->id = $id;
    }

    /**
     * Get the constraint's ID.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set the constraint's ID.
     *
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get the cohort ID this constraint applies to.
     *
     * @return int|null
     */
    public function getCohortId(): ?int
    {
        return $this->cohortId;
    }

    /**
     * Set the cohort ID this constraint applies to.
     *
     * @param int|null $cohortId
     * @return void
     */
    public function setCohortId(?int $cohortId): void
    {
        $this->cohortId = $cohortId;
    }

    /**
     * Get the constraint type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the constraint type.
     *
     * @param string $type
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Get the start date of the constraint.
     *
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    /**
     * Set the start date of the constraint.
     *
     * @param \DateTime $startDate
     * @return void
     */
    public function setStartDate(\DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * Get the end date of the constraint.
     *
     * @return \DateTime
     */
    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    /**
     * Set the end date of the constraint.
     *
     * @param \DateTime $endDate
     * @return void
     */
    public function setEndDate(\DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * Get the constraint description.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the constraint description.
     *
     * @param string|null $description
     * @return void
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
