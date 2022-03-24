<?php

    namespace Src\Models;

    class Answer extends \Src\Config\Database {

        public function findQues($id) {
            $query = "SELECT * FROM questions WHERE id = :id";

            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $rowCount = $stmt->rowCount();
                if($rowCount > 0) {
                    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                return true;
                } else {
                    return null;
                }
            } catch (\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

        public function create($data)
        {
            $insertNew = "INSERT INTO answers (content, image, question_id, user_id) VALUES (:content, :image, :question_id, :user_id)";
            try {
                $stmt = $this->conn->prepare($insertNew);
                $stmt->bindParam(':content', $data['content']);
                $stmt->bindParam(':image', $data['image']);
                $stmt->bindParam(':user_id', $data['user_id']);
                $stmt->bindParam(':question_id', $data['question_id']);
                $stmt->execute();
                $result = [
                    "message" => "Answer Created"
                ];
                return $result;
            } catch (\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

        public function upvote($userId, $answerId){
            $upvote ="INSERT INTO answers_upvotes (user_id, answer_id, upvote) VALUES (:userId, :answerId, 1); 
            UPDATE answers SET upvote = upvote + 1 WHERE id = :answerId;
            UPDATE users INNER JOIN answers ON answers.id = :answerId AND answers.user_id = users.id SET users.reputation = users.reputation + 10";
            
            $findVote = "SELECT upvote FROM answers_upvotes WHERE user_id = :userId AND answer_id = :answerId";
            
            $deleteVote = "DELETE FROM answers_upvotes WHERE user_id = :userId AND answer_id = :answerId; 
            UPDATE answers SET upvote = upvote - 1 WHERE id = :answerId;
            UPDATE users INNER JOIN answers ON answers.id = :answerId AND answers.user_id = users.id SET users.reputation = users.reputation - 10";
            
            $update = "UPDATE answers_upvotes SET upvote = 1 WHERE user_id = :userId AND answerId = :answerId;
            UPDATE answers SET downvote = downvote - 1 WHERE id = :answerId;
            UPDATE answers SET upvote = upvote + 1 WHERE id = :answerId;
            UPDATE users INNER JOIN answers ON answers.id = :answerId AND answers.user_id = users.id SET users.reputation = users.reputation + 12";

            try{
                $stmt = $this->conn->prepare($findVote);
                $stmt->bindParam(":answerId", $answerId);
                $stmt->bindParam(":userId", $userId);
                $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                $stmt = null;
                if(!$result) {
                    $stmt = $this->conn->prepare($upvote);
                    $stmt->bindParam(":answerId", $answerId);
                    $stmt->bindParam(":userId", $userId);
                    $stmt->execute();
                    $stmt = null;
                    $result = [
                        'message' => "Upvoted"
                    ];
                }
                else if($result['upvote'] == '1') {
                    $stmt = $this->conn->prepare($deleteVote);
                    $stmt->bindParam(":answerId", $answerId);
                    $stmt->bindParam(":userId", $userId);
                    $stmt->execute();
                    $result = ['message' => "unvoted"];
                } else if ($result['upvote'] == '0') {
                    $stmt = $this->conn->prepare($update);
                    $stmt->bindParam(":answerId", $answerId);
                    $stmt->bindParam(":userId", $userId);
                    $stmt->execute();
                    $result = ['message' => "upvoted"];
                }
                
                else {
                    $result = null;
                }
                
                return $result;
            }
            catch (\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

        public function downvote($userId,$answerId){         
            $downvote ="INSERT INTO answers_upvotes( user_id, answer_id, upvote) VALUES (:userId,:answerId,0);
            UPDATE answers SET downvote = downvote + 1 WHERE id = :answerId;
            UPDATE users INNER JOIN answers ON answers.id = :answerId AND answers.user_id = users.id SET users.reputation = users.reputation - 2";
            $findVote = "SELECT upvote FROM answers_upvotes WHERE user_id = :userId AND answer_id = :answerId";

            $deleteVote = "DELETE FROM answers_upvotes WHERE user_id = :userId AND answer_id = :answerId;
            UPDATE answers SET downvote = downvote - 1 WHERE id = :answerId;
            UPDATE users INNER JOIN answers ON answers.id = :answerId AND answers.user_id = users.id SET users.reputation = users.reputation + 2"; 

            $update = "UPDATE answers_upvotes SET upvote = 0 WHERE user_id = :userId AND answer_id = :answerId;
            UPDATE answers SET downvote = downvote + 1 WHERE id = :answerId;
            UPDATE answers SET upvote = upvote - 1 WHERE id = :answerId;
            UPDATE users INNER JOIN answers ON answers.id = :answerId AND answers.user_id = users.id SET users.reputation = users.reputation - 12";
            try{
                $stmt = $this->conn->prepare($findVote);
                $stmt->bindParam(':answerId', $answerId);
                $stmt->bindParam(':userId', $userId);
                $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                $stmt = null;
                if(!$result){
                    $stmt = $this->conn->prepare($downvote);
                    $stmt->bindParam(':answerId',$answerId);
                    $stmt->bindParam(':userId',$userId);
                    $stmt->execute();
                    $stmt = null;
                    $result = [
                        'message' => "downvoted"
                    ];
                }
                else if($result["upvote"] == '0'){
                    $stmt = $this->conn->prepare($deleteVote);
                    $stmt->bindParam(':answerId',$answerId);
                    $stmt->bindParam(':userId',$userId);
                    $stmt->execute();
                    $stmt = null;
                    $result = [
                        'message' => "downvote deleted"
                    ];
                }
                else if($result['upvote'] == '1') {
                    $stmt = $this->conn->prepare($update);
                    $stmt->bindParam(":answerId", $answerId);
                    $stmt->bindParam(":userId", $userId);
                    $stmt->execute();
                    $result = ['message' => "downvote"];
                }
                 else {
                    $result = null;
                }

                return $result;
            }
            catch (\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

        public function edit($data)
        {
            $insertNew = "UPDATE answers SET content=:content, i WHERE user_id=:user_id AND question_id=:question_id";
            $findAns = "SELECT * FROM answers WHERE user_id=:user_id AND question_id=:question_id";
            try {
                $stmt = $this->conn->prepare($findAns);
                $stmt->bindParam(':user_id', $data['user_id']);
                $stmt->bindParam(':question_id', $data['question_id']);
                $stmt->execute();

                $ans = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                if($stmt->rowCount() > 0) {
                    $stmt = $this->conn->prepare($insertNew);
                    $stmt->bindParam(':content', $data['content']);
                    $stmt->bindParam(':user_id', $data['user_id']);
                    $stmt->bindParam(':question_id', $data['question_id']);
                    $stmt->execute();
                    $result = [
                        "message" => "Answer Updated"
                    ];
                } else {
                    $result = null;
                }
                return $result;
            } catch (\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

        public function getall($id)
        {
            $query ="SELECT answers.*, users.username FROM answers INNER JOIN questions ON answers.question_id = questions.id AND questions.id = :id INNER JOIN users ON users.id = answers.user_id ORDER BY upvote DESC";
            try{
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return $result;
            }
            catch (\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

        public function getById($id){
            $query = "SELECT answers.*, users.username FROM answers INNER JOIN users ON users.id = answers.user_id AND answers.id = :id";
            try{
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $rowCount = $stmt->rowCount();
                if($rowCount > 0) {
                    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                return $result;
                } else {
                    return null;
                } 
            }catch (\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

        public function markSolution($id){
            $query = "SELECT issolution FROM answers WHERE question_id = (SELECT question_id FROM answers WHERE id = :id);
                    UPDATE answers SET issolution = 0 WHERE question_id = (SELECT question_id FROM answers WHERE id = :id);";
            $updateQ = "UPDATE answers SET issolution = 1 WHERE id = :id;";
            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $rowCount = $stmt->rowCount();
                if($rowCount > 0) {
                    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                    if($result['issolution'] == 0) {
                        $stmt = $this->conn->prepare($updateQ);
                        $stmt->bindParam(':id', $id);
                        $stmt->execute();
                    }
                } else {
                    return null;
                } 
                return [
                    "message"=>"Marked as solution"
                ];
            } catch (\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

        public function getQuesById($ansId){
            $query = "SELECT * FROM questions WHERE id = (SELECT question_id FROM answers WHERE id = :id)";
            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $ansId);
                $stmt->execute();
                $rowCount = $stmt->rowCount();
                if($rowCount > 0) {
                    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                } else {
                    return null;
                } 
                return $result;
            } catch (\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

        public function getReplies($ansId){
            $query = "SELECT replies.*, users.username FROM `replies` INNER JOIN users ON replies.user_id = users.id AND replies.answer_id = :id";
            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":id", $ansId);
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return $result;
            } catch (\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

        public function addReply($data){
            $query = "INSERT INTO replies (content, answer_id, user_id) VALUES (:content, :answer_id, :user_id)";

            try {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":content", $data['content']);
                $stmt->bindParam(":answer_id", $data['answer_id']);
                $stmt->bindParam(":user_id", $data['user_id']);
                $stmt->execute();
                return [
                    "message"=>"Reply added"
                ];
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }