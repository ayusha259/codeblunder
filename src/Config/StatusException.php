<?php

    namespace Src\Config;

    class StatusException extends \Exception
    {
        private $statusCode;
        public function __construct($message,
                                    $statusCode=500,
                                    $code = 0, 
                                    \Exception $previous = null, 
                                    ) 
        {
            parent::__construct($message, $code, $previous);

            $this->statusCode = $statusCode; 
        }

        public function getStatus(){
            return $this->statusCode;
        }
    }