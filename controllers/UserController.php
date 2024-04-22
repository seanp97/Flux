<?php 

class UserController {
    private $db;

    function __construct() {
        $this->db = new Flux();
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