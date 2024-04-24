<?php 

class AboutController {

    public static function index($all_users) {
        // Load index.php file in views/About folder
        view("About/index", compact('all_users'));
    }

    public static function GetUserAbout() {
        try {
            $users = User::All()::Exec();
            
            if($users) {
                return $users;
            }
            else {
                Status404();
            }
        }
        catch(Exception $e) {
            Error($e);
            Status500();
        }
    }

}