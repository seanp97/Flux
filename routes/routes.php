<?php
require './autoload.php';

Http::get('/', function() {
   HomeController::index();
});

Http::get('/api', function() {
    FactController::index();
});

Http::get('/api/{id}', function($id) {
    FactController::show($id);
});

Http::post('/api', function() {
    FactController::create();
});

Http::post('/api/{id}', function($id) {
    FactController::destroy($id);
});

Http::get('/about', function() {
    $all_users = AboutController::GetUserAbout();
    AboutController::index($all_users);
});
