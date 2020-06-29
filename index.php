<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';

use Jenssegers\Blade\Blade;
use Pecee\SimpleRouter\SimpleRouter;

/*
 * Eloquent ORM to use in MVC architecture
 */

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

/*
 * Configuration Eloquent ORM
 */
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'bibc',
    'username' => 'root',
    'password' => 'Nil00f@r1869',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);
$capsule->setEventDispatcher(new Dispatcher(new Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();
/*
 * End configuration of ORM
 */

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