<?php
require './autoload.php';


Http::get('/', function() {
    echo 'Hello World';
});

Http::get('/{id}', function($id) {
    echo 'The ID: ' . $id;
});