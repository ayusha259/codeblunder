<?php

    namespace Src\Controllers;

use \Src\Config\StatusException;

class UserController {

        private $User = null;
        private $cloudinary = null;

        public function __construct()
        {
            $this->User = new \Src\Models\User;
            $cloud = new \Src\Config\MyCloud;
            $this->cloudinary = $cloud->getCloud();
        }

        public function index($req, $res) {
            return $res->withJson([
                "message" => "Hello"
            ]);
        }

        public function signup($req, $res) {
            try {
                $email = $req->getParam('email') ?? null;
                $username = $req->getParam('username') ?? null;
                $fullname = $req->getParam('fullname') ?? null;
                $password = $req->getParam('password') ?? null;
                $bio = $req->getParam('bio') ?? null;
                $profile = $req->getParam('profile') ?? null;
                if(!$username || !$fullname || !$password || !$email) {
                    throw new StatusException("All fields are required");
                }
                $doesExist = $this->User->isExist($username, $email);
                if($doesExist) {
                    throw new StatusException("Username or email already exist");
                }
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $result = $this->User->signUp([
                    'email' => $email,
                    'username' => $username,
                    'fullname' => $fullname,
                    'password' => $hashedPassword,
                    'bio' => $bio,
                    'profile' => $profile,
                ]);

                if(!$result) {
                    throw new StatusException("Kal ana");
                }

                $newUser = $this->User->isExist($username);

                $token = \Src\Config\Auth::generatToken($newUser['id'], $newUser['username']);

                return $res->withJson([
                    "token" => $token
                ]);
            } catch (StatusException $err) {
                throw new StatusException($err->getMessage(), $err->getStatus());
            }
        }

        public function login($req, $res){
            try{
                $username = $req->getParam('username');
                $password = $req->getParam('password');
                $user = $this->User->isExist($username,$username);
                if(!$user){
                    throw new StatusException("User does not exists");
                }
                if(!password_verify($password,$user['password'])){
                    throw new StatusException("Incorrect password");
                }
                $token = \Src\Config\Auth::generatToken($user['id'], $user['username']);
                return $res->withJson([
                    "token" => $token
                ]);
            }
            catch (StatusException $err) {
                throw new StatusException($err->getMessage(), $err->getStatus());
            }
        }

        public function getdetails($req, $res)
        {
            try{
                $userId = $req->getAttribute('userId');
                $user = $this->User->getUserById($userId);
                if(!$user) {
                    throw new StatusException("Unathorized no user found", 401);
                }
                return $res->withJson($user, 200);
            }
            catch (StatusException $err) {
                throw new StatusException($err->getMessage(), $err->getStatus());
            }
        }

        public function editpagedetails($req, $res) {
            try {
                $userId = $req->getAttribute('userId');
                $user = $this->User->getUserEditPageDetails($userId);
                if(!$user) {
                    throw new StatusException("Unathorized no user found", 401);
                }
                return $res->withJson($user, 200);
            } catch (StatusException $err) {
                throw new StatusException($err->getMessage(), $err->getStatus());
            }
        }

        private function randStr($len) {
            $char = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $res = '';
            for($i = 0; $i < $len; $i++) {
                $idx = rand(0, strlen($char) - 1);
                $res .= $char[$idx];
            }
            return $res;
        }

        public function updateInfo($req, $res) {
            try {
                $data=$req->getParams();
                $files = $req->getUploadedFiles();
                $userId = $req->getAttribute('userId');
                $fullname = $data['fullname'] ?? null;
                $bio = $data['bio'] ?? null;
                $instagram = $data['instagram'] ?? null;
                $twitter = $data['twitter'] ?? null;
                $linkdin = $data['linkdin'] ?? null;
                $facebook = $data['facebook'] ?? null;
                $github = $data['github'] ?? null;
                $image = $files['image'] ?? null;
                if(!$fullname) {
                    throw new StatusException("Username and Full name are required");
                }

                if($image) {
                    $uploadedImage = $this->cloudinary->uploadApi()->upload($image->file, [
                        'public_id' => $userId . "_" . $this->randStr(8),
                        'folder' => "codeblunder/" . $userId
                    ]);
                    unlink($image->file);
                    $dataImage = $uploadedImage['url'];
                } else {
                    $dataImage = null;
                }
             
                $result = $this->User->update($userId, $fullname, $bio, $instagram, $twitter, $linkdin, $facebook, $github, $dataImage);
                return $res->withJson($result);
            } catch (StatusException $err) {
                throw new StatusException($err->getMessage(), $err->getStatus());
            }
        }

        public function getuser($req, $res, $args) {
            try {
                $username = $args['username'];
                $user = $this->User->getUserDetailsByUsername($username);
                if(!$user) {
                    throw new StatusException("No user found", 400);
                }
                return $res->withJson($user, 200);
            } catch (StatusException $err) {
                throw new StatusException($err->getMessage(), $err->getStatus());
            }
        }

        public function getAllUsers($req,$res){
            try{
            $users = $this->User->getAllUsers();
            return $res->withJson($users, 200);
        }
        catch (StatusException $err) {
            throw new StatusException($err->getMessage(), $err->getStatus());
        }
    }
    }


    