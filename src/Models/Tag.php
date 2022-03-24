<?php

    namespace Src\Models;

    class Tag extends \Src\Config\Database {
        public function getAllTags(){
            $query = 'SELECT * FROM tags';
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