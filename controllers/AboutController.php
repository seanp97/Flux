<?php 

class AboutController {

    public static function index($all_users) {
        // Load index.php file in views/About folder
        view("About/index", compact('all_users'));
    }

}