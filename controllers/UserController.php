<?php 

class UserController {
    private $db;

    function __construct() {
        $this->db = new Flux();
    }

    public static function AddNewUser() {
        try {
            $user = new User();
            $user = User::ModelData();
            $user->Password = Hasher::SHA256($user->Password);
        
            User::Create($user, function() {
                echo 'Added user';
            });
        }
        catch(Exception $e) {
            Error($e);
        }

    }

    public static function GetAllUsers() {
        try {
            $users = User::All()::Exec();

            // $db = new Flux();
            // $users = $this->db->Query('SELECT * FROM User')->fetchAll();
            
            if($users) {
                Ok($users);
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

    public static function GetUser($id) {
        try {
            if($id && Validator::Numeric($id)) {
                //$user = User::All()::Where('UserId')::Is($id)::Exec();
                $user = User::Find($id)->Exec();
                
                if($user) {
                    Ok($user);
                }
                else {
                    Status404();
                }
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

    public static function CreateUser() {
        // This is a different version of above AddNewUser

        try {
            $user = new User();
            $user = User::ModelData();
            $user->Password = Hasher::SHA256($user->Password);
            
            User::InsertObject($user, 'User');
            RenderJSON($user);
            Status200();
        }
        catch(Exception $e) {
            Error($e);
        }
    }


    public static function EditUser() {
        try {
            $editUserData = User::ModelData();
            User::Update()::Set('Email')::To($editUserData->Email)::Where('UserId')::Is($editUserData->UserId)::Exec();
            Ok($editUserData);
            Status200();
        }
        catch(Exception $e) {
            Error($e);
        }
    }
}