<?php

    namespace Src\Config;

use Cloudinary\Cloudinary;

class MyCloud {
        private $cloud = null;

        public function __construct()
        {
            $this->cloud = new Cloudinary([
                'cloud' => [
                    'cloud_name' => $_ENV["CLOUD_NAME"],
                    'api_key' => $_ENV["CLOUD_API_KEY"],
                    'api_secret' => $_ENV["CLOUD_SECRET_KEY"]
                ],
            ]);
        }

        public function getCloud() {
            return $this->cloud;
        }
    }