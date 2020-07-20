#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap.php';

switch ($argv[1]) {
    case 'migrate':
        echo 'Migrating...' . PHP_EOL;
        $dir = 'database/migrations';
        $files = scandir($dir);
        foreach ($files as $file) {
            if (is_file($dir . '/' . $file)) {
                echo 'Migrating ' . $file . PHP_EOL;
                include $dir . '/' . $file;
                $className = 'UpdateDriverVehicleTable';
                $migrationClass = new $className;
                $migrationClass->up();
                echo 'Migrated' . $file . PHP_EOL;
            }
        }
        break;
}
