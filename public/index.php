<?php

    require_once __DIR__ . '/../vendor/autoload.php';

    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();

    require_once __DIR__ . '/config.php';
    
    $app = new \Slim\App([
        'settings' => [
            'displayErrorDetails' => true,
        ]
        ]);

    $container = $app->getContainer();

    $container['UserController'] = function() {
        return new \Src\Controllers\UserController;
    };

    $container['QuesController'] = function() {
        return new \Src\Controllers\QuesController;
    };

    $container['AnsController'] = function() {
        return new \Src\Controllers\AnsController;
    };

    $container['TagController'] = function() {
        return new \Src\Controllers\TagController;
    };

    unset($app->getContainer()['notFoundHandler']);
    $container['notFoundHandler'] = function ($container) {
        return function ($req, $res) use ($container) {
            $res = new \Slim\Http\Response(404);
            return $res->write("Page not found");
        };
    };

    $container['errorHandler'] = function ($container) {
        return function ($req, $res, $exception) use ($container) {
            $status = $exception->getStatus();
            $data = [
                'message' => $exception->getMessage()
            ];
            return $res->withJson($data, $status);
        };
    };

    require_once __DIR__ . "/../src/routes.php";
    
    $app->run();