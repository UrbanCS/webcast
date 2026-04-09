<?php
declare(strict_types=1);

define('LSB_APP', true);
define('APP_ROOT', dirname(__DIR__));

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = APP_ROOT . '/app/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($path)) {
        require_once $path;
    }
});

$configFile = APP_ROOT . '/config/config.php';
if (!is_file($configFile)) {
    $configFile = APP_ROOT . '/config/config.example.php';
}

$GLOBALS['lsb_config'] = require $configFile;
$GLOBALS['lsb_lang'] = require APP_ROOT . '/config/lang/fr.php';

require_once APP_ROOT . '/app/Helpers/functions.php';

bootstrap_app();
