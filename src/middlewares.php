<?php
 use \Src\Config\StatusException;

    $auth = function ($req, $res, $next) {
    try {
        $header = $req->getHeader('Authorization') ?? null;
        if($header && str_starts_with($header[0], 'Bearer')) {
            $token = explode(" ", $header[0])[1];
            $decodedToken = \Src\Config\Auth::decodeToken($token);
            if(!$decodedToken) {
                throw new StatusException("Unauthorized", 401);
            }
            $req = $req->withAttribute('username', $decodedToken['username']);
            $req = $req->withAttribute('userId', $decodedToken['id']);
            $res = $next($req, $res);       
        } else {
            throw new StatusException("Unauthorized", 401);
        }
        return $res;
    } catch(StatusException $err) {
        throw new StatusException($err->getMessage(), $err->getStatus());
    }
};