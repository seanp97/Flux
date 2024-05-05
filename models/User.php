<?php

class User extends FluxModel {
    public ?int $UserId = null;
    public string $UserName = '';
    public string $Email = '';
    public string $Password = '';
    public Person $PersonId;

    // Default constructor with optional parameters
    public function __construct(?int $userId = null, string $userName = '', string $email = '', string $password = '', Person $personId) {
        $this->UserId = $userId;
        $this->UserName = $userName;
        $this->Email = $email;
        $this->Password = $password;
        $this->PersonId = $personId;
    }
}