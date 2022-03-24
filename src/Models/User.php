<?php

    namespace Src\Models;

    class User extends \Src\Config\Database {
        public function signUp($data){
            $query = 'INSERT INTO users (`fullname`, `username`, `email`, `password`, `bio`, `profile`) VALUES (:fullname, :username, :email, :password, :bio, :profile)';
            try {

                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':fullname', $data['fullname']);
                $stmt->bindParam(':username', $data['username']);
                $stmt->bindParam(':email', $data['email']);
                $stmt->bindParam(':password', $data['password']);
                $stmt->bindParam(':bio', $data['bio']);
                $stmt->bindParam(':profile', $data['profile']);

                $stmt->execute();

                $result = [
                    "message" => "User Created"
                ];

                return $result;
                
            } catch (\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

       

        
        public function isExist($username = "", $email = "") {
            $query = "SELECT * FROM users WHERE email = :email OR username = :username";

            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $rows = $stmt->rowCount();
                
                if($rows == 0) {
                    return null;
                }else {
                    $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                    return $user;
                };
            } catch(\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

        public function update($id, $fullname, $bio, $instagram, $twitter, $linkdin, $facebook, $github, $imageUrl) {
            $query = "UPDATE users SET fullname=:fullname,profile=:imgPic,bio=:bio,instagram=:instagram,twitter=:twitter,linkdin=:linkdin,facebook=:facebook,github=:github WHERE id = :id";
            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':fullname', $fullname);
                $stmt->bindParam(':bio', $bio);
                $stmt->bindParam(':imgPic', $imageUrl);
                $stmt->bindParam(':instagram', $instagram);
                $stmt->bindParam(':twitter', $twitter);
                $stmt->bindParam(':linkdin', $linkdin);
                $stmt->bindParam(':facebook', $facebook);
                $stmt->bindParam(':github', $github);
                $stmt->bindParam(':id', $id);

                $stmt->execute();
                $result = [
                    "message" => "User Updated"
                ];

                return $result;
            } catch(\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

        public function getUserById($id) {
            $query = "SELECT username, fullname, profile, reputation, bio, created_at, id, (SELECT COUNT(*) FROM questions WHERE questions.user_id = :id) AS num_ques, (SELECT COUNT(*) FROM answers WHERE user_id = :id) AS num_ans FROM users WHERE id = :id";
            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":id", $id);
                $stmt-> execute();
                $rowCount = $stmt->rowCount();
                if($rowCount > 0) {
                    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                } else {
                    $result = null;
                }
                return $result;

            }catch(\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        } 

        public function getUserEditPageDetails($id) {
            $query = "SELECT username, fullname, profile, bio, twitter, instagram, linkdin, facebook, github, password FROM users WHERE id = :id";
            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":id", $id);
                $stmt-> execute();
                $rowCount = $stmt->rowCount();
                if($rowCount > 0) {
                    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                } else {
                    $result = null;
                }
                return $result;
            } catch(\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

        public function getUserDetailsByUsername($username) {
            $query = "SELECT username, fullname, profile, reputation, bio, created_at, id, twitter, instagram, linkdin, facebook, github FROM users WHERE username = :username";
            $getQuestions = "SELECT * FROM questions WHERE user_id = :id";
            $getAnswers = "SELECT * FROM answers WHERE user_id = :id";
            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":username", $username);
                $stmt-> execute();
                $rowCount = $stmt->rowCount();
                if($rowCount > 0) {
                    $result["user"] = $stmt->fetch(\PDO::FETCH_ASSOC);
                } else {
                    $result = null;
                    return $result;
                }
                $stmt = $this->conn->prepare($getQuestions);
                $stmt->bindParam(":id", $result['user']['id']);
                $stmt-> execute();
                $questions = $stmt->fetchAll((\PDO::FETCH_ASSOC));
                $result['questions'] = $questions ? $questions : [];

                $stmt = $this->conn->prepare($getAnswers);
                $stmt->bindParam(":id", $result['user']['id']);
                $stmt-> execute();
                $answers = $stmt->fetchAll((\PDO::FETCH_ASSOC));
                $result['answers'] = $answers ? $answers : [];
                return $result;
            }catch(\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

        public function getAllUsers(){
            $query = "SELECT * FROM users";
            try{
                $stmt = $this->conn->prepare($query);
                $stmt-> execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                 return $result;

            }
        
        catch(\Exception $err) {
            throw new \Exception($err->getMessage());
        }
    }
    }