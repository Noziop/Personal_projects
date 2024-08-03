<?php

namespace App\Models;

class User
{
    private $id;
    private $username;
    private $firstName;
    private $lastName;
    private $email;
    private $role;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->firstName = $data['first_name'];
        $this->lastName = $data['last_name'];
        $this->email = $data['email'];
        $this->role = $data['role'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getRole()
    {
        return $this->role;
    }
}