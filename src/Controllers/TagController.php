<?php

    namespace Src\Controllers;
    use Exception;
    use \Src\Config\StatusException;

    class TagController {
        private $Tag = null;

        public function __construct()
        {
            $this->Tag = new \Src\Models\Tag;
        }

    
        public function getAllTags($req,$res){
            try{
            $tags = $this->Tag->getAllTags();
            return $res->withJson($tags, 200);
        }
        catch (StatusException $err) {
            throw new StatusException($err->getMessage(), $err->getStatus());
        }           
        }

     

    

       
    }