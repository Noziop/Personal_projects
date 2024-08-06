<?php

/**
 * Drawing Model
 *
 * This class represents a drawing in the SOD (Speaker of the Day) application.
 * It contains information about a specific drawing event.
 */

namespace App\Models;

class Drawing
{
    /**
     * @var int The unique identifier for the drawing
     */
    private $id;

    /**
     * @var int The ID of the student selected in this drawing
     */
    private $studentId;

    /**
     * @var int The ID of the cohort for which this drawing was made
     */
    private $cohortId;

    /**
     * @var \DateTime The date when this drawing was performed
     */
    private $drawingDate;

    /**
     * @var \DateTime The date for which this student was selected to speak
     */
    private $speakingDate;

    /**
     * Drawing constructor.
     *
     * @param int $studentId The ID of the selected student
     * @param int $cohortId The ID of the cohort
     * @param \DateTime $drawingDate The date of the drawing
     * @param \DateTime $speakingDate The date the student is to speak
     * @param int|null $id The unique identifier for the drawing (optional)
     */
    public function __construct(int $studentId, int $cohortId, \DateTime $drawingDate, \DateTime $speakingDate, ?int $id = null)
    {
        $this->studentId = $studentId;
        $this->cohortId = $cohortId;
        $this->drawingDate = $drawingDate;
        $this->speakingDate = $speakingDate;
        $this->id = $id;
    }

    /**
     * Get the drawing's ID.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set the drawing's ID.
     *
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get the ID of the selected student.
     *
     * @return int
     */
    public function getStudentId(): int
    {
        return $this->studentId;
    }

    /**
     * Set the ID of the selected student.
     *
     * @param int $studentId
     * @return void
     */
    public function setStudentId(int $studentId): void
    {
        $this->studentId = $studentId;
    }

    /**
     * Get the ID of the cohort.
     *
     * @return int
     */
    public function getCohortId(): int
    {
        return $this->cohortId;
    }

    /**
     * Set the ID of the cohort.
     *
     * @param int $cohortId
     * @return void
     */
    public function setCohortId(int $cohortId): void
    {
        $this->cohortId = $cohortId;
    }

    /**
     * Get the date of the drawing.
     *
     * @return \DateTime
     */
    public function getDrawingDate(): \DateTime
    {
        return $this->drawingDate;
    }

    /**
     * Set the date of the drawing.
     *
     * @param \DateTime $drawingDate
     * @return void
     */
    public function setDrawingDate(\DateTime $drawingDate): void
    {
        $this->drawingDate = $drawingDate;
    }

    /**
     * Get the speaking date for the selected student.
     *
     * @return \DateTime
     */
    public function getSpeakingDate(): \DateTime
    {
        return $this->speakingDate;
    }

    /**
     * Set the speaking date for the selected student.
     *
     * @param \DateTime $speakingDate
     * @return void
     */
    public function setSpeakingDate(\DateTime $speakingDate): void
    {
        $this->speakingDate = $speakingDate;
    }
}
