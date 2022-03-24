<?php

    namespace Src\Models;

    class Reply extends \Src\Config\Database {
        public function getReplies($id){
            $query = 'SELECT * FROM replies WHERE replies.answer_id = :id';
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



 }