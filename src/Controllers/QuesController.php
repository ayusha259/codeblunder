<?php 
namespace Src\Controllers;

use Exception;
use \Src\Config\StatusException;


//post questions

class QuesController {
    private $Question = null;
    private $cloudinary = null;

    public function __construct()
    {
        $this->Question = new \Src\Models\Question;
        $cloud = new \Src\Config\MyCloud;
        $this->cloudinary = $cloud->getCloud();
    }

    private function checkAuth($req) {
        try {
            $header = $req->getHeader('Authorization') ?? null;
            if($header && str_starts_with($header[0], 'Bearer')) {
                $token = explode(" ", $header[0])[1];
                $decodedToken = \Src\Config\Auth::decodeToken($token);
                if($decodedToken) {
                    return $decodedToken['id'];
                }   
            }
            return null;
        } catch(StatusException $err) {
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

    public function create($req, $res) {
        try {
            $data = $req->getParams();
            $userId = $req->getAttribute('userId');
            $files = $req->getUploadedFiles();
            $image = $files['image'] ?? null;
            $content = $data['content'] ?? null;
            $title = $data['title'] ?? null;
            $tags = $data['tags'] ?? null;
            if(!$title) {
                throw new StatusException("Title is required");
            }
            if(!$content) {
                throw new StatusException("Content is required");
            }
            if(!$tags) {
                throw new StatusException("At least one tag is required");
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
            $data['tags'] = strtolower($data['tags']);
            
            $result = $this->Question->create($data);

            return $res->withJson(["message" => $result], 200);
        } catch(StatusException $err) {
            throw new StatusException($err->getMessage(), $err->getStatus());
        }
    }

    public function getquestion($req, $res, $args) {
        try {
            $id = $args['id'];
            $question = $this->Question->findById($id);
            if(!$question) {
                throw new StatusException("No question exist, Bad request");
            }
            return $res->withJson($question, 200);
        } catch(StatusException $err) {
            throw new StatusException($err->getMessage(), $err->getStatus());
        }
    }

    //get all questions
    public function getall($req,$res){
        try{
            $result = $this->Question->getall();
            return $res->withJson($result,200);
        }catch(StatusException $err) {
            throw new StatusException($err->getMessage(), $err->getStatus());
        }
    }
    //upvote
    public function upvote($req,$res, $args){
        try{
            $userId = $req->getAttribute('userId');
            $questionId = $args['id'];
            $ques = $this->Question->findById($questionId);
            if(!$ques) {
                throw new StatusException("No Question Exists");
            }
            $ques_user = $ques['question']['user_id'];
            if($ques_user == $userId) {
                return $res->withJson(["message" => "Cant upvote"], 400);
            }
            $upvote = $this->Question->upvote($userId, $questionId);
            if(!$upvote) {
                throw new StatusException("Something went wrong");
            }
            return $res->withJson($ques_user, 200);
        }
        catch(StatusException $err) {
            throw new StatusException($err->getMessage(), $err->getStatus());
        }
    }
    public function downvote($req,$res, $args){
        try{
            $userId = $req->getAttribute('userId');
            $questionId = $args['id'];
            $ques = $this->Question->findById($questionId);
            if(!$ques) {
                throw new StatusException("No Question Exists");
            }
            $ques_user = $ques['question']['user_id'];
            if($ques_user == $userId) {
                return $res->withJson(["message" => "Cant downvote"], 400);
            }
            $downvote = $this->Question->downvote($userId, $questionId);
            if(!$downvote) {
                throw new StatusException("Something went wrong");
            }
            return $res->withJson($downvote, 200);
        }
        catch(StatusException $err) {
            throw new StatusException($err->getMessage(), $err->getStatus());
        }
    }
    
    //get questions by tag

    public function getByTags($req, $res) {
        try {
            $tags = $req->getParams();
            $questions = $this->Question->getByTags($tags);
            if(!$questions) {
                throw new StatusException("No question exist, Bad request");
            }
            return $res->withJson($questions, 200);
        } catch(StatusException $err) {
            throw new StatusException($err->getMessage(), $err->getStatus());
        }
    }

    public function findByUserId($req,$res, $args){
        try {
            $id = $args['id'];
            $question = $this->Question->findByUserId($id);
            if(!$question) {
                throw new StatusException("No questions ");
            }
            return $res->withJson($question, 200);
        } catch(StatusException $err) {
            throw new StatusException($err->getMessage(), $err->getStatus());
        }
    }
    public function getByTitle($req, $res) {
        try {
            $data = $req->getParams();
            $search = $data['search'];
            $questions = $this->Question->getByTitle($search);
            return $res->withJson($questions, 200);
        } catch(StatusException $err) {
            throw new StatusException($err->getMessage(), $err->getStatus());
        }
    }

    
}
