<?php

/**
 * DrawingDay Model
 *
 * This class represents a drawing day in the SOD (Speaker of the Day) application.
 * It defines the days when drawings can occur for a specific cohort.
 */

namespace App\Models;

class DrawingDay
{
    /**
     * @var int The unique identifier for the drawing day
     */
    private $id;

    /**
     * @var int The ID of the cohort this drawing day is associated with
     */
    private $cohortId;

    /**
     * @var string The day of the week (e.g., 'Monday', 'Tuesday', etc.)
     */
    private $dayOfWeek;

    /**
     * @var bool Whether drawings are enabled for this day
     */
    private $isEnabled;

    /**
     * DrawingDay constructor.
     *
     * @param int $cohortId The ID of the associated cohort
     * @param string $dayOfWeek The day of the week
     * @param bool $isEnabled Whether drawings are enabled for this day
     * @param int|null $id The unique identifier for the drawing day (optional)
     */
    public function __construct(int $cohortId, string $dayOfWeek, bool $isEnabled, ?int $id = null)
    {
        $this->cohortId = $cohortId;
        $this->dayOfWeek = $dayOfWeek;
        $this->isEnabled = $isEnabled;
        $this->id = $id;
    }

    /**
     * Get the drawing day's ID.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set the drawing day's ID.
     *
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get the cohort ID.
     *
     * @return int
     */
    public function getCohortId(): int
    {
        return $this->cohortId;
    }

    /**
     * Set the cohort ID.
     *
     * @param int $cohortId
     * @return void
     */
    public function setCohortId(int $cohortId): void
    {
        $this->cohortId = $cohortId;
    }

    /**
     * Get the day of the week.
     *
     * @return string
     */
    public function getDayOfWeek(): string
    {
        return $this->dayOfWeek;
    }

    /**
     * Set the day of the week.
     *
     * @param string $dayOfWeek
     * @return void
     */
    public function setDayOfWeek(string $dayOfWeek): void
    {
        $this->dayOfWeek = $dayOfWeek;
    }

    /**
     * Check if drawings are enabled for this day.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * Set whether drawings are enabled for this day.
     *
     * @param bool $isEnabled
     * @return void
     */
    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }
}
