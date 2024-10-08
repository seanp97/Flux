<?php
require './autoload.php';

Http::get('/', function() {
    echo 'Hello World';
});

Http::get('/{id}', function($id) {
    echo 'The ID: ' . $id;
});

Http::post('/add-user', function() {
    UserController::AddNewUser();
});

Http::get('/about', function() {
    $all_users = AboutController::GetUserAbout();
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

Http::get('/update-table', function() {
    // Create User table

    User::MigrateTable(function() {
        echo Success('User table updated');
    });
});