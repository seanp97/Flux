<?php
require './autoload.php';

Http::get("/", function($id) {
    $User = new UserController();

    if(!$id) {
        // Get All People
        $User->GetAllUsers();
    }
    else {
        // Get Single Person
        $User->GetUser($id);
    }
});

Http::post('/add-user', function() {
    $User = new UserController();
    $User->AddNewUser();
});

Http::get('/about', function() {
    $About = new AboutController();
    $About->index();
});

Http::post("/create-user", function() {
    $User = new UserController();
    // Create Person
    $User->CreateUser();
});

Http::post("/edit-user", function() {
    $User = new UserController();
    // Create Person
    $User->EditUser();
});

Http::post('/update-table', function() {
    // Create User table

    User::MigrateTable(function() {
        echo Success('User table updated');
    });
});