<?php

use Slim\Http\Request;
use Slim\Http\Response;

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../config/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
$dependencies = require __DIR__ . '/../config/dependencies.php';
$dependencies($app);

// Register middleware
$middleware = require __DIR__ . '/../config/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../routes/routes.php';
$routes($app);

$app->add(function (Request $request, Response $response, $next) {
    if ($request->getUri()->getScheme() !== 'https') {
        $uri = $request->getUri()->withScheme("https")->withPort(null);
        return $response->withRedirect((string)$uri);
    } else {
        return $next($request, $response);
    }
});

// Run app
$app->run();
