<?php
    namespace Src\Models;

    class Question extends \Src\Config\Database {
        public function create($data){
            $query = 'INSERT INTO questions (`title`, `content`, `image`, `user_id`, `tags`) VALUES (:title, :content, :image, :user_id, :tags)';
            $insertTag = "INSERT INTO tags (title, ques_id) VALUES ";
            foreach (explode(',',$data['tags']) as $value ){
                $insertTag .= "( '" . $value .  "', :ques_id ),";
            }
            try{
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':title', $data['title']);
                $stmt->bindParam(':content', $data['content']);
                $stmt->bindParam(':image', $data['image']);
                $stmt->bindParam(':user_id', $data['user_id']);
                $stmt->bindParam(':tags', $data['tags']);
                $stmt->execute();
                $stmt = null;
                $last_id = $this->conn->lastInsertId();
                $insertTag = substr($insertTag, 0, strlen($insertTag) - 1);
                
                $stmt = $this->conn->prepare($insertTag);
                $stmt->bindParam(':ques_id', $last_id);
                $stmt->execute();
                $stmt = null;
                $result = [
                    "message" => "Question Created"
                ];
                return $result;
            }
            catch (\Exception $err) {
                throw new \Exception($err->getMessage());
            }

        }

        public function getall(){
            $query = 'SELECT q.*, users.username, (SELECT COUNT(*) FROM answers WHERE question_id = q.id) AS num_ans ,(SELECT GROUP_CONCAT(upvotes.user_id SEPARATOR ",") FROM upvotes WHERE upvotes.question_id = q.id AND upvotes.upvote = 1) AS upvotes_users, (SELECT GROUP_CONCAT(upvotes.user_id SEPARATOR ",") FROM upvotes WHERE upvotes.question_id = q.id AND upvotes.upvote = 0) AS downvotes_users FROM questions q INNER JOIN users ON q.user_id = users.id ORDER BY q.created_at DESC';
            try{
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
            }
            catch (\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

        public function findByUserId($id){
            $query = 'SELECT questions.*,users.username FROM questions INNER JOIN users ON users.id = questions.user_id AND users.id = :id';
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

        public function findById($id) {
            $getQuestion = 'SELECT questions.*, users.username, (SELECT COUNT(*) FROM answers WHERE question_id = :id) AS num_ans,(SELECT GROUP_CONCAT(upvotes.user_id SEPARATOR ",") FROM upvotes WHERE upvotes.question_id = :id AND upvotes.upvote = 1) AS upvotes_users, (SELECT GROUP_CONCAT(upvotes.user_id SEPARATOR ",") FROM upvotes WHERE upvotes.question_id = :id AND upvotes.upvote = 0) AS downvotes_users FROM questions INNER JOIN users ON users.id = questions.user_id AND questions.id = :id';
            
            $getAnswers = 'SELECT answers.*, users.username, (SELECT GROUP_CONCAT(answers_upvotes.user_id SEPARATOR ",") FROM answers_upvotes WHERE answers_upvotes.answer_id = answers.id AND answers_upvotes.upvote = 1) AS upvotes_users, (SELECT GROUP_CONCAT(answers_upvotes.user_id SEPARATOR ",") FROM answers_upvotes WHERE answers_upvotes.answer_id = answers.id AND answers_upvotes.upvote = 0) AS downvotes_users FROM answers INNER JOIN users ON users.id = answers.user_id AND answers.question_id = :id ORDER BY issolution DESC, created_at DESC';
            try {
                
                $stmt = $this->conn->prepare($getQuestion);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $rowCount = $stmt->rowCount();
                $result = [];
                if($rowCount > 0) {
                    $result['question'] = $stmt->fetch(\PDO::FETCH_ASSOC);
                    $stmt = null;
                } else {
                    return null;
                }
                $stmt = $this->conn->prepare($getAnswers);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $answers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $result['answers'] = $answers ? $answers  : [];
                return $result;
            } catch (\Exception $err) {
                throw new \Exception($err->getMessage());
            }
        }

        public function upvote($userId, $questionId){
            
            $upvote ="INSERT INTO upvotes (user_id, question_id, upvote) VALUES (:userId, :questionId, 1);
            UPDATE questions SET upvote = upvote + 1 WHERE id = :questionId;
            UPDATE users INNER JOIN questions ON questions.id = :questionId AND questions.user_id = users.id SET users.reputation = users.reputation + 10";
           
            $findVote = "SELECT upvote FROM upvotes WHERE user_id = :userId AND question_id = :questionId";
            
            $deleteVote = "DELETE FROM upvotes WHERE user_id = :userId AND question_id = :questionId; 
            UPDATE questions SET upvote = upvote - 1 WHERE id = :questionId;
            UPDATE users INNER JOIN questions ON questions.id = :questionId AND questions.user_id = users.id SET users.reputation = users.reputation - 10";
            
            $update = "UPDATE upvotes SET upvote = 1 WHERE user_id = :userId AND question_id = :questionId;
            UPDATE questions SET downvote = downvote - 1 WHERE id = :questionId;
            UPDATE questions SET upvote = upvote + 1 WHERE id = :questionId;
            UPDATE users INNER JOIN questions ON questions.id = :questionId AND questions.user_id = users.id SET users.reputation = users.reputation + 12";

            try{
                $stmt = $this->conn->prepare($findVote);
                $stmt->bindParam(":questionId", $questionId);
                $stmt->bindParam(":userId", $userId);
                $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                $stmt = null;
                if(!$result) {
                    $stmt = $this->conn->prepare($upvote);
                    $stmt->bindParam(":questionId", $questionId);
                    $stmt->bindParam(":userId", $userId);
                    $stmt->execute();
                    $stmt = null;
                    $result = [
                        'message' => "Upvoted"
                    ];
                }
                else if($result['upvote'] == '1') {
                    $stmt = $this->conn->prepare($deleteVote);
                    $stmt->bindParam(":questionId", $questionId);
                    $stmt->bindParam(":userId", $userId);
                    $stmt->execute();
                    $result = ['message' => "unvoted"];
                } else if ($result['upvote'] == '0') {
                    $stmt = $this->conn->prepare($update);
                    $stmt->bindParam(":questionId", $questionId);
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

        public function downvote($userId,$questionId){         
            
            $downvote ="INSERT INTO upvotes( user_id, question_id, upvote) VALUES (:userId,:questionId,0);
            UPDATE questions SET downvote = downvote + 1 WHERE id = :questionId;
            UPDATE users INNER JOIN questions ON questions.id = :questionId AND questions.user_id = users.id SET users.reputation = users.reputation - 2";
            
            $findVote = "SELECT upvote FROM upvotes WHERE user_id = :userId AND question_id = :questionId";

            $deleteVote = "DELETE FROM upvotes WHERE user_id = :userId AND question_id = :questionId;
            UPDATE questions SET downvote = downvote - 1 WHERE id = :questionId;
            UPDATE users INNER JOIN questions ON questions.id = :questionId AND questions.user_id = users.id SET users.reputation = users.reputation + 2"; 

            $update = "UPDATE upvotes SET upvote = 0 WHERE user_id = :userId AND question_id = :questionId;
            UPDATE questions SET downvote = downvote + 1 WHERE id = :questionId;
            UPDATE questions SET upvote = upvote - 1 WHERE id = :questionId;
            UPDATE users INNER JOIN questions ON questions.id = :questionId AND questions.user_id = users.id SET users.reputation = users.reputation - 12";
            
            try{
                $stmt = $this->conn->prepare($findVote);
                $stmt->bindParam(':questionId', $questionId);
                $stmt->bindParam(':userId', $userId);
                $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                $stmt = null;
                if(!$result){
                    $stmt = $this->conn->prepare($downvote);
                    $stmt->bindParam(':questionId',$questionId);
                    $stmt->bindParam(':userId',$userId);
                    $stmt->execute();
                    $stmt = null;
                    $result = [
                        'message' => "downvoted"
                    ];
                }
                else if($result["upvote"] == '0'){
                    $stmt = $this->conn->prepare($deleteVote);
                    $stmt->bindParam(':questionId',$questionId);
                    $stmt->bindParam(':userId',$userId);
                    $stmt->execute();
                    $stmt = null;
                    $result = [
                        'message' => "downvote deleted"
                    ];
                }
                else if($result['upvote'] == '1') {
                    $stmt = $this->conn->prepare($update);
                    $stmt->bindParam(":questionId", $questionId);
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

       public function getByTags($tags) {
        $query = "SELECT questions.*,users.username FROM questions INNER JOIN users ON users.id = questions.user_id INNER JOIN tags ON questions.id = tags.ques_id WHERE ";
        foreach (explode(',',$tags['tags']) as $value ){
                $query .= "tags.title = '".$value ."' OR ";
            } 

        try {
                $query = substr($query, 0, strlen($query) - 4);
                $query .= " GROUP BY id ORDER BY upvote DESC";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $rowCount = $stmt->rowCount();
                if($rowCount > 0) {
                    $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return $result;
                } else {
                    return null;
                }
           } catch (\Exception $err) {
            throw new \Exception($err->getMessage());
        }
       }
       
       public function getByTitle($title){
           $query = "SELECT title, id FROM questions WHERE ";
           foreach (explode(' ',$title) as $value ){
            $query .= "title LIKE '%$value%' OR ";
        } 
        try {
            $query = substr($query, 0, -4);
            $query .= " ORDER BY upvote DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $rowCount = $stmt->rowCount();
            if($rowCount > 0) {
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                $result = [];
            }
            return $result;
       } catch (\Exception $err) {
        throw new \Exception($err->getMessage());
    }
       }
    }