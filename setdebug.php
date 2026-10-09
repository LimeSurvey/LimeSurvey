<?php

/*
 * ------------------------------------------------------------------
 *  Setup YII_DEBUG constant and error reporting according to config
 * ------------------------------------------------------------------
 */
if (!defined('BASEPATH')) {
    http_response_code(403);
    exit('No direct script access allowed');
}
if (!defined('YII_DEBUG')) {
    if (file_exists(APPPATH . 'config' . DIRECTORY_SEPARATOR . 'config.php')) {
        $settings = include(APPPATH . 'config' . DIRECTORY_SEPARATOR . 'config.php');
    } else {
        $settings = [];
    }

    // Set debug : if not set : set to default from PHP 5.3
    if (isset($settings['config']['debug'])) {
        $effectiveDebug = (int) $settings['config']['debug'];
        $envDebug = getenv('LS_DEBUG');

        // LS_DEBUG can reduce debug level but never increase it.
        if ($envDebug !== false && preg_match('/^[0-2]$/', (string) $envDebug)) {
            $effectiveDebug = min($effectiveDebug, (int) $envDebug);
        }

        if ($effectiveDebug > 0) {
            @ini_set("display_errors", 1);
            define('YII_DEBUG', true);
            if ($effectiveDebug > 1) {
                error_reporting(E_ALL);
                // @see https://www.limesurvey.org/manual/Code_quality_guide#Assertions
                // This will not work if the process is started in production mode (see https://www.php.net/manual/en/ini.core.php#ini.zend.assertions)
                @ini_set('zend.assertions', 1);
            } else {
                error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
            }
        } else {
            @ini_set("display_errors", 0);
            define('YII_DEBUG', false);
            error_reporting(0);
        }
    } else {
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);// Not needed if user doesn't remove their 'debug'=>0, for application/config/config.php (Installation is OK with E_ALL)
    }
    unset($settings);
}
