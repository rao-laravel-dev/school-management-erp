<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Windows XAMPP (thread-safe PHP in threaded Apache): putenv() is shared by all threads,
// so a parallel request can lose .env values (MissingAppKeyException / random 401).
// Keep .env values per request ($_SERVER/$_ENV only). No effect on Linux or artisan.
if (PHP_OS_FAMILY === 'Windows' && PHP_ZTS) {
    \Illuminate\Support\Env::disablePutenv();
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
