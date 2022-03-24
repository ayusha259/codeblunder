<?php

    namespace Src\Config;

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    class Auth {

        private function definePayload($id, $username) {
            $issuedAt = new \DateTimeImmutable("now", new \DateTimeZone("Asia/Kolkata"));
            $res = [
                'iat'=> $issuedAt->getTimestamp(),
                'iss'=> "root",
                'nbf'=>$issuedAt->getTimestamp(),
                'exp'=>$issuedAt->modify('+30 days')->getTimestamp(),
                'username'=> $username,
                'id'=> $id
            ];
            return $res;
        }

        public static function generatToken($id, $username) {
            
            $auth = new Auth();
            $payload = $auth->definePayload($id, $username);
            
            $jwt = JWT::encode($payload, $_ENV['JWT_KEY'], 'HS256');

            return $jwt;
        }

        public static function decodeToken($token) {
            try {
                $decoded = JWT::decode($token, new Key($_ENV['JWT_KEY'], 'HS256'));
                return (array) $decoded;
            } catch (\Exception $err) {
                return false;
            }
        }
    }