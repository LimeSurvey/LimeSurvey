<?php

/*
 * ------------------------------------------------------------------
 *  Setup YII_DEBUG constant and error reporting according to config
 * ------------------------------------------------------------------
 */
if (!defined('YII_DEBUG')) {
    if (file_exists(APPPATH . 'config' . DIRECTORY_SEPARATOR . 'config.php')) {
        $settings = include(APPPATH . 'config' . DIRECTORY_SEPARATOR . 'config.php');
    } else {
        $settings = [];
    }

    // Set debug : if not set : set to default from PHP 5.3
    if (isset($settings['config']['debug'])) {
        if ($settings['config']['debug'] > 0) {
            define('YII_DEBUG', true);
            if ($settings['config']['debug'] > 1) {
                error_reporting(E_ALL);

                // @see https://manual.limesurvey.org/Code_quality_guide#Assertions
                assert_options(ASSERT_ACTIVE, true);
                assert_options(ASSERT_WARNING, false);
                assert_options(
                    ASSERT_CALLBACK,
                    function ($file, $line, $assertion, $message) {
                        throw new Exception("The assertion $assertion in $file on line $line has failed: $message");
                    }
                );
            } else {
                error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
            }
        } else {
            define('YII_DEBUG', false);
            error_reporting(0);
        }
    } else {
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);// Not needed if user doesn't remove their 'debug'=>0, for application/config/config.php (Installation is OK with E_ALL)
    }
    unset($settings);
}