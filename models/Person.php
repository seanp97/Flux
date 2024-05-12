<?php

class Person extends FluxModel {
    public int $PersonId;
    public string $FirstName = '';
    public string $LastName = '';
    public string $Email = '';
    public string $Gender = '';
    public string $PhoneNumber = '';

    // Constructor
    public function __construct(int $PersonId, string $firstName = '', string $lastName = '', string $email = '', string $gender = '', string $phoneNumber = '') {
        $this->PersonId = $PersonId;
        $this->FirstName = $firstName;
        $this->LastName = $lastName;
        $this->Email = $email;
        $this->Gender = $gender;
        $this->PhoneNumber = $phoneNumber;
    }
}