<?php

    namespace Src\Controllers;
    use Exception;
    use \Src\Config\StatusException;

    class AnsController {
        private $Answer = null;

        public function __construct()
        {
            $this->Answer = new \Src\Models\Answer;
            $cloud = new \Src\Config\MyCloud;
            $this->cloudinary = $cloud->getCloud();
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

        public function create($req, $res) {
            try {
                $data = $req->getParams();
                $userId = $req->getAttribute('userId');
                $files = $req->getUploadedFiles();
                $image = $files['image'] ?? null;
                $content = $data['content'] ?? null;
                $question_id = $data['question_id'] ?? null;
                if(!$content) {
                    throw new StatusException("Content is required");
                }
                if(!$question_id) {
                    throw new StatusException("Question id required");
                }

                $question = $this->Answer->findQues($question_id);

                if(!$question) {
                    throw new StatusException("Question does not exist");
                }

                if($image) {
                    $uploadedImage = $this->cloudinary->uploadApi()->upload($image->file, [
                        'public_id' => $userId . "_" . $this->randStr(8),
                        'folder' => "codeblunder/" . $userId
                    ]);
                    unlink($image->file);
                    $data['image'] = $uploadedImage['url'];
                } else {
                    $data['image'] = null;
                }
                $data['user_id'] = $userId;
                
                $result = $this->Answer->create($data);
                return $res->withJson(["message" => $result], 200);
            } catch(StatusException $err) {
                throw new StatusException($err->getMessage(), $err->getStatus());
            }
        }

    //upvote
    public function upvote($req,$res, $args){
        try{
            $userId = $req->getAttribute('userId');
            $answerId = $args['id'];
            $ans = $this->Answer->getById($answerId);
            if(!$ans) {
                throw new StatusException("No Answer Exists");
            }
            $ans_user = $ans['user_id'];
            if($ans_user == $userId) {
                return $res->withJson(["message" => "Cant Upvote"], 400);
            }
            $upvote = $this->Answer->upvote($userId, $answerId);
            if(!$upvote) {
                throw new StatusException("Something went wrong");
            }
            return $res->withJson($upvote, 200);
        }
        catch(StatusException $err) {
            throw new StatusException($err->getMessage(), $err->getStatus());
        }
    }

    public function downvote($req,$res, $args){
        try{
            $userId = $req->getAttribute('userId');
            $answerId = $args['id'];
            $ans = $this->Answer->getById($answerId);
            if(!$ans) {
                throw new StatusException("No Answer Exists");
            }
            $ans_user = $ans['user_id'];
            if($ans_user == $userId) {
                return $res->withJson(["message" => "Cant downvote"], 400);
            }
            $downvote = $this->Answer->downvote($userId, $answerId);
            if(!$downvote) {
                throw new StatusException("Something went wrong");
            }
            return $res->withJson($downvote, 200);
        }
        catch(StatusException $err) {
            throw new StatusException($err->getMessage(), $err->getStatus());
        }
    }

        public function getall($req,$res,$args){
            try{
                $id = $args['id'];
                $result = $this->Answer->getall($id);
                return $res->withJson($result,200);
            }
            catch(StatusException $err) {
                throw new StatusException($err->getMessage(), $err->getStatus());
            }
        }

        public function edit($req, $res)
        {
            try {
                $data = $req->getParams();
                $userId = $req->getAttribute('userId');
                $content = $data['content'] ?? null;
                $question_id = $data['question_id'] ?? null;
                $data['user_id'] = $userId;
                if(!$content) {
                    throw new StatusException("Content is required");
                }
                if(!$question_id) {
                    throw new StatusException("Question id required");
                }

                $result = $this->Answer->edit($data);
                if(!$result) {
                    throw new StatusException("Answer does not exist");
                }
                return $res->withJson($result, 200);
            }catch(StatusException $err) {
                throw new StatusException($err->getMessage(), $err->getStatus());
            }
        }


        public function getById($req, $res, $args){
            try{
                $id = $args['id'];
                $answer = $this->Answer->getById($id);
                if(!$answer) {
                    throw new StatusException("No question exist, Bad request");
                }
                return $res->withJson($answer, 200);
            }
            catch(StatusException $err) {
                throw new StatusException($err->getMessage(), $err->getStatus());
            }
        }
        public function markSolution($req, $res, $args){
            $id = $args['id'];
            $userId = $req->getAttribute('userId');
            $ques = $this->Answer->getQuesById($id);
            if(!$ques) {
                throw new StatusException("No question exist, Bad request");
            }
            else if ($ques['user_id'] != $userId) {
                throw new StatusException("Not your question, byebye!", 401);
            }
            else {
                $result = $this->Answer->markSolution($id);
                if(!$result) {
                    throw new StatusException("Something went wrong!");
                }
                return $res->withJson($result, 200);
            }
        }

        public function getAnsReplies($req, $res, $args) {
            $id = $args['id'];
            $replies = $this->Answer->getReplies($id);
            return $res->withJson($replies, 200);
        }

        public function postReply($req, $res){
            $data = $req->getParams();
            $userId = $req->getAttribute('userId');

            if(!$data['content']){
                throw new StatusException("Content is required");
            }
            $data['user_id'] = $userId;

            $result = $this->Answer->addReply($data);
            return $res->withJson($result,200);
        }
    }