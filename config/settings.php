<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Monolog\Logger;

return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        
        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],
        
        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            //'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'path' => __DIR__ . '/../logs/app.log',
            // 'path' => isset($_ENV['docker']) ? 'php://stdout' : '/var/www/svc.centerld.ru/api/logs/app.log',
            'level' => Logger::DEBUG,
        ],
        'dbase' => [
            'username' => '***',
            'password' => '***',
            'dsn' => "mysql:host=***;dbname=***;charset=utf8",
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => false,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8 COLLATE utf8_unicode_ci"
            ],
        ],
        // app config
        'appConfig' => [
            'APP_PATH' => $_SERVER['DOCUMENT_ROOT'] . '/api',
            'LisApiUrl' => 'http://***',
            'PPH' => '***',
            'SV' => '***',
            'IV' => '***',
            'AuthKey' => '***',
            'XmlTmpPath' => $_SERVER['DOCUMENT_ROOT'] . '/api/runtime/xmltmp/',
            'RequestPath' => $_SERVER['DOCUMENT_ROOT'] . '/api/runtime/requestpath/',
            'AttachFilesPath' => $_SERVER['DOCUMENT_ROOT'] . '/api/runtime/attaches/',
            'AttachFileExt' => '.pdf',
            'LisFtp' => [
                'url' => '***',
                'login' => '***',
                'password' => '***'
            ],
        ],
    ],
    // error settings
    'notFoundHandler' => function () {
        return function (Request $request, Response $response) {
            return $response->withStatus(404)->withJson([
                'error' => 'not_found',
                'error_description' => 'Method not found'
            ]);
        };
    },
    'errorHandler' => function () {
        return function (Request $request, Response $response, Exception $exception) {
            return $response->withStatus(500)->withJson([
                'error' => 'bad_request',
                'error_description' => 'Error: ' . $exception->getMessage(),
                //'error_line' => 'Error: file ' . $exception->getFile() . ', line ' . $exception->getLine()
                'error_trace' => $exception->getTrace()
            ]);
        };
    },
    'notAllowedHandler' => function () {
        return function (Request $request, Response $response) {
            return $response->withStatus(405)->withJson([
                'error' => 'not_allowed',
                'error_description' => 'Method not allowed'
            ]);
        };
    },
    'phpErrorHandler' => function () {
        return function (Request $request, Response $response, Error $error) {
            return $response->withStatus(500)->withJson([
                'error' => 'bad_request',
                'error_description' => 'php Error: ' . $error->getMessage(),
                'error_line' => 'Error: file ' . $error->getFile() . ', line ' . $error->getLine(),
                'error_trace' => $error->getTrace()
            ]);
        };
    }


];
