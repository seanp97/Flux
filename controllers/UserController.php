<?php 

class UserController {
    private $db;

    function __construct() {
        $this->db = new Flux();
    }

    public function AddNewUser() {
        try {
            $user = $this->db->GetModelData('User');
            $user->Password = Hasher::hash($user->Password);
        
            $newUser = new User(null, $user->UserName, $user->Email, $user->Password);
            $newUser->Insert($newUser);
        }
        catch(Exeption $e) {
            Error($e);
        }

    }

    public function GetAllUsers() {
        try {

            $users = User::All()::Exec();

            // $users = $this->db->All('User')->Exec();

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
            $user = $this->db->GetModelData('User');
            $newUser = new User(null, $user->UserName, $user->Email, Hasher::Hash($user->Password));

            $this->db->InsertObject($newUser, 'User');
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