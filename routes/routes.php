<?php
require './autoload.php';

Http::get("/", function() {
    $User = new UserController();
    $id = QueryString('id');

    if(!$id) {
        // Get All People
        $User->GetAllUsers();
    }
    else {
        // Get Single Person
        $User->GetUser($id);
    }
});

Http::post('/test', function() {
    $User = new UserController();
    $User->Test();
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
    $User = new Flux();
    
    // Create User table
    $User->MigrateTable('Person', function() {
        echo Success('User table updated');
    });
});