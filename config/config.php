<?php 

class Configuration {
    public static $connection = [
        "servername" => "localhost",
        "username" => "root",
        "password" => "",
        "database" => ""
    ];

    public static function AccessControlAllowOrigin($cors = "null") {
        header("Access-Control-Allow-Origin: $cors");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
    }
}
