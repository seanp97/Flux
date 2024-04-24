<?php 

class HomeController {
    private $db;

    function __construct() {
        $this->db = new Flux();
    }

    public static function index() {
        view("Home/index");
    }
}