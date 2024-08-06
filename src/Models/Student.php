<?php

/**
 * Student Model
 *
 * This class represents a student in the SOD (Speaker of the Day) application.
 * It contains properties and methods related to student management.
 */

namespace App\Models;

class Student
{
    /**
     * @var int The unique identifier for the student
     */
    private $id;

    /**
     * @var string The first name of the student
     */
    private $firstName;

    /**
     * @var string The last name of the student
     */
    private $lastName;

    /**
     * @var string The email of the student
     */
    private $email;

    /**
     * @var int The ID of the cohort the student belongs to
     */
    private $cohortId;

    /**
     * Student constructor.
     *
     * @param string $firstName The first name of the student
     * @param string $lastName The last name of the student
     * @param string $email The email of the student
     * @param int $cohortId The ID of the cohort the student belongs to
     * @param int|null $id The unique identifier for the student (optional)
     */
    public function __construct(string $firstName, string $lastName, string $email, int $cohortId, ?int $id = null)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->cohortId = $cohortId;
        $this->id = $id;
    }

    /**
     * Get the student's ID.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set the student's ID.
     *
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get the student's first name.
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Set the student's first name.
     *
     * @param string $firstName
     * @return void
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * Get the student's last name.
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Set the student's last name.
     *
     * @param string $lastName
     * @return void
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * Get the student's email.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set the student's email.
     *
     * @param string $email
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * Get the student's cohort ID.
     *
     * @return int
     */
    public function getCohortId(): int
    {
        return $this->cohortId;
    }

    /**
     * Set the student's cohort ID.
     *
     * @param int $cohortId
     * @return void
     */
    public function setCohortId(int $cohortId): void
    {
        $this->cohortId = $cohortId;
    }

    /**
     * Get the student's full name.
     *
     * @return string
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
