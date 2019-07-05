<?php

use Slim\App;
use Slim\Views;

return function (App $app) {
    $container = $app->getContainer();

    // view renderer
    $container['renderer'] = function ($c) {
        $settings = $c->get('settings')['renderer'];
        return new Views\PhpRenderer($settings['template_path']);
    };

/*	$container['environment'] = function () {
		$scriptName = $_SERVER['SCRIPT_NAME'];
		$_SERVER['SCRIPT_NAME'] = dirname(dirname($scriptName)) . '/' . basename($scriptName);
		return new Slim\Http\Environment($_SERVER);
	};*/

    // monolog
    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
        return $logger;
    };

	$container['db'] = function ($c) {
		$settings = $c->get('settings')['dbase'];
		return new PDO($settings['dsn'], $settings['username'], $settings['password'], $settings['options']);
	};

};
