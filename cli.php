<?php

use Slim\App;
use Slim\Http\Environment;
// php cli.php /update

require __DIR__ . '/vendor/autoload.php';

if (PHP_SAPI == 'cli') {
	$argv = $GLOBALS['argv'];
	array_shift($argv);
	$pathInfo = implode('/', $argv);

	$env = Environment::mock(['REQUEST_URI' => $pathInfo]);

	$settings = require __DIR__ . '/config/settings.php'; // here are return ['settings'=>'']
	$settings['environment'] = $env;

	$app = new App($settings);

	$dependencies = require __DIR__ . '/config/dependencies.php';
	$dependencies($app);
	$middleware = require __DIR__ . '/config/middleware.php';
	$middleware($app);

	$routes = require __DIR__ . '/routes/routes-cli.php';
	$routes($app);

	$app->run();
}