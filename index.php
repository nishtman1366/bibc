<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';

use Jenssegers\Blade\Blade;
use Pecee\SimpleRouter\SimpleRouter;

define('BASE_DIR', '/');
define('BASE_PATH', 'http://localhost/bibc/');

/*
 * Create the view instance
 */
$blade = new Blade('resources/views', 'storage/framework/cache/views');

/*
 * All About Routes
 */
require_once 'routes/web.php';

/**
 * The default namespace for route-callbacks, so we don't have to specify it each time.
 * Can be overwritten by using the namespace config option on your routes.
 */
SimpleRouter::setDefaultNamespace('\App\Controllers');

// Start the routing
try {
    SimpleRouter::start();
} catch (\Pecee\Http\Middleware\Exceptions\TokenMismatchException $e) {
    echo $e->getMessage();
} catch (\Pecee\SimpleRouter\Exceptions\NotFoundHttpException $e) {
    echo $e->getMessage();
} catch (\Pecee\SimpleRouter\Exceptions\HttpException $e) {
    echo $e->getMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}
/*
 * End Routes
 */