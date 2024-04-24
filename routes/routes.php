<?php
require './autoload.php';

Http::get("/", function($id) {

    if(!$id) {
        // Get All People
        UserController::GetAllUsers();
    }
    else {
        // Get Single Person
        UserController::GetUser($id);
    }
});

Http::post('/add-user', function() {
    UserController::AddNewUser();
});

Http::get('/about', function() {
    $all_users = UserController::GetAllUsers();
    AboutController::index($all_users);
});

Http::post("/create-user", function() {
    // Create Person
    UserController::CreateUser();
});

Http::post("/edit-user", function() {
    // Create Person
    UserController::EditUser();
});

Http::post('/update-table', function() {
    // Create User table

    User::MigrateTable(function() {
        echo Success('User table updated');
    });
});