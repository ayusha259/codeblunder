<?php 
namespace Src\Controllers;

use Exception;

class ReplyController {
    private $Replies = null;

    public function __construct()
    {
        $this->Replies = new \Src\Models\Reply;
    }

    public function getReplies($req,$res,$args){
        try{
            $id = $args['id'];
            $replies = $this->Replies->getReplies($id);
            return $res->withJson($replies, 200);
    }
    catch (Exception $err) {
        throw new Exception($err->getMessage());
    }
}

}