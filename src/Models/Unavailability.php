<?php

/**
 * Unavailability Model
 *
 * This class represents a period of unavailability for a student in the SOD (Speaker of the Day) application.
 * It is used to track times when a student cannot participate in the drawing or speaking events.
 */

namespace App\Models;

class Unavailability
{
    /**
     * @var int The unique identifier for the unavailability period
     */
    private $id;

    /**
     * @var int The ID of the student who is unavailable
     */
    private $studentId;

    /**
     * @var \DateTime The start date and time of the unavailability period
     */
    private $startDateTime;

    /**
     * @var \DateTime The end date and time of the unavailability period
     */
    private $endDateTime;

    /**
     * @var string The reason for the unavailability (optional)
     */
    private $reason;

    /**
     * Unavailability constructor.
     *
     * @param int $studentId The ID of the student who is unavailable
     * @param \DateTime $startDateTime The start date and time of the unavailability period
     * @param \DateTime $endDateTime The end date and time of the unavailability period
     * @param string|null $reason The reason for the unavailability (optional)
     * @param int|null $id The unique identifier for the unavailability period (optional)
     */
    public function __construct(int $studentId, \DateTime $startDateTime, \DateTime $endDateTime, ?string $reason = null, ?int $id = null)
    {
        $this->studentId = $studentId;
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
        $this->reason = $reason;
        $this->id = $id;
    }

    /**
     * Get the unavailability's ID.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set the unavailability's ID.
     *
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get the ID of the student who is unavailable.
     *
     * @return int
     */
    public function getStudentId(): int
    {
        return $this->studentId;
    }

    /**
     * Set the ID of the student who is unavailable.
     *
     * @param int $studentId
     * @return void
     */
    public function setStudentId(int $studentId): void
    {
        $this->studentId = $studentId;
    }

    /**
     * Get the start date and time of the unavailability period.
     *
     * @return \DateTime
     */
    public function getStartDateTime(): \DateTime
    {
        return $this->startDateTime;
    }

    /**
     * Set the start date and time of the unavailability period.
     *
     * @param \DateTime $startDateTime
     * @return void
     */
    public function setStartDateTime(\DateTime $startDateTime): void
    {
        $this->startDateTime = $startDateTime;
    }

    /**
     * Get the end date and time of the unavailability period.
     *
     * @return \DateTime
     */
    public function getEndDateTime(): \DateTime
    {
        return $this->endDateTime;
    }

    /**
     * Set the end date and time of the unavailability period.
     *
     * @param \DateTime $endDateTime
     * @return void
     */
    public function setEndDateTime(\DateTime $endDateTime): void
    {
        $this->endDateTime = $endDateTime;
    }

    /**
     * Get the reason for the unavailability.
     *
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * Set the reason for the unavailability.
     *
     * @param string|null $reason
     * @return void
     */
    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }
}
