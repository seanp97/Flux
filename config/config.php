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
    }
}
