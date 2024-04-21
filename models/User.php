<?php

class User {
    public ?int $UserId;
    public string $UserName;
    public string $Email;
    public string $Password;

    // Constructor with optional UserId
    public function __construct(?int $userId = null, string $userName, string $email, string $password) {
        $this->UserId = $userId;
        $this->UserName = $userName;
        $this->Email = $email;
        $this->Password = $password;
    }
}
