<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';

use Jenssegers\Blade\Blade;
use Pecee\SimpleRouter\SimpleRouter;

require_once __DIR__ . '/bootstrap.php';

define('BASE_DIR', '/');
define('BASE_PATH', 'http://localhost/bibc/');
/*
 * Configuration of Whoops exception handler
 */
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();
/*
 * End of Whoops configuration
 */
/*
 * Dependency injection
 */
//$container = (new \DI\ContainerBuilder())
//    ->useAutowiring(true)->addDefinitions([
//        \Illuminate\http\Request::class => function () {
//            $request = new \Illuminate\Http\Request();
//            return $request;
//        },
//    ])
//    ->build();
/*
 * End of dependency injection
 */

/*
 * Create the view instance
 */
$blade = new Blade('resources/views', 'storage/framework/cache/views');

/*
 * All About Routes
 */
require_once 'routes/web.php';
require_once 'routes/api.php';

/**
 * The default namespace for route-callbacks, so we don't have to specify it each time.
 * Can be overwritten by using the namespace config option on your routes.
 */
SimpleRouter::setDefaultNamespace('\App\Controllers');
// Add our container to simple-router and enable dependency injection
//SimpleRouter::enableDependencyInjection($container);
// Start the routing
SimpleRouter::start();
/*
 * End Routes
 */