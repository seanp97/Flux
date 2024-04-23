<?php

class Person extends FluxModel {
    public string $FirstName = '';
    public string $LastName = '';
    public string $Email = '';
    public string $Gender = '';
    public string $PhoneNumber = '';

    // Constructor
    public function __construct(string $firstName = '', string $lastName = '', string $email = '', string $gender = '', string $phoneNumber = '') {
        $this->FirstName = $firstName;
        $this->LastName = $lastName;
        $this->Email = $email;
        $this->Gender = $gender;
        $this->PhoneNumber = $phoneNumber;
    }
}