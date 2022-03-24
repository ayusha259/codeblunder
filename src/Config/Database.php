<?php

    namespace Src\Config;

class Database {
        protected $conn = null;

        public function __construct()
        {
            try {
                
                $this->conn = new \PDO("mysql:host=localhost;dbname=codeblunder", 'rick', '123456');
                $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            } catch(\PDOException $err) {
                throw new \Exception($err->getMessage());
            }
        }
    }