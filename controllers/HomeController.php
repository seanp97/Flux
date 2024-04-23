<?php 

class HomeController {
    private $db;

    function __construct() {
        $this->db = new Flux();
    }

    public static function index() {
        // Load index.php file in views/Home folder
        view("Home/index");
    }
}