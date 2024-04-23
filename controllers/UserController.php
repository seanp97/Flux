<?php 

class UserController {
    private $db;

    function __construct() {
        $this->db = new Flux();
    }

    public function AddNewUser() {
        try {
            $user = User::HydratedPostModelData();
            $user->Password = Hasher::SHA256($user->Password);
        
            $newUser = new User(null, $user->UserName, $user->Email, $user->Password);
            User::Insert($newUser);
        }
        catch(Exeption $e) {
            Error($e);
        }

    }

    public function GetAllUsers() {
        try {
            $users = User::All()::Exec();

            // $db = new Flux();
            // $users = $this->db->Query('SELECT * FROM User')->fetchAll();
            
            if($users) {
                RenderJSON($users);
            }
            else {
                NotFound('No Data');
                Status404();
            }
        }
        catch(Exception $e) {
            Error($e);
            Status500();
        }
    }

    public function GetUser($id) {
        try {
            if($id && is_numeric($id)) {

                $user = User::All()::Where('UserId')::Is("$id")::Exec();
                
                if($user) {
                    RenderJSON($user);
                }
                else {
                    NotFound('No Data');
                    Status404();
                }
            }
            else {
                NotFound('No Data');
                Status404();
            }
        }
        catch(Exception $e) {
            Error($e);
            Status500();
        }
    }

    public function CreateUser() {
        // This is a different version of above AddNewUser

        try {
            $user = User::HydratedPostModelData();
            $newUser = new User(null, $user->UserName, $user->Email, Hasher::SHA1($user->Password));
            User::InsertObject($newUser, 'User');
            RenderJSON($user);
            Status200();
        }
        catch(Exception $e) {
            Error($e);
        }
    }


    public function EditUser() {
        try {
            $editUserData = User::HydratedPostModelData('User');
            User::Update()::Set('Email')::To($editUserData->Email)::Where('UserId')::Is($editUserData->UserId)::Exec();
            RenderJSON($editUserData);
            Status200();
        }
        catch(Exception $e) {
            Error($e);
        }
    }
}