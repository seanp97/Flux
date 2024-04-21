<?php
require './autoload.php';

Http::get("/", function() {
    $Home = new HomeController();
    $id = QueryString('id');

    if(!$id) {
        // Get All People
        $Home->GetAllUsers();
    }
    else {
        // Get Single Person
        $Home->GetUser($id);
    }
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

    $User->MigrateTable('User', function() {
        echo Success('User table updated');
    });

});