<?php

class User extends ModelLoad {
    public ?int $UserId = null;
    public string $UserName = '';
    public string $Email = '';
    public string $Password = '';

    // Default constructor with optional parameters
    public function __construct(?int $userId = null, string $userName = '', string $email = '', string $password = '') {
        $this->UserId = $userId;
        $this->UserName = $userName;
        $this->Email = $email;
        $this->Password = $password;
    }
}

