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

            $UserModel = new User();
            $users = $UserModel->All();

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
                $person = $this->db->All('User')->Where('UserId')->Equals("$id")->Exec();
                
                if($person) {
                    RenderJSON($person);
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
            $user = $this->db->GetModelData('User');
            $editedUser = $this->db->Update('User')->Set('Email')->Equals("'$user->Email'")->Where('UserId')->Equals("'$user->UserId'")->Exec();
            RenderJSON($editedUser);
            Status200();
        }
        catch(Exception $e) {
            Error($e);
        }
    }
}