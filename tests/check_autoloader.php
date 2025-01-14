<?php

/**
 * Small script to make sure running `composer dump-autoload` does not change any files.
 * If it does, it means some vendor package was not added to git.
 *
 * @since 2020-03-10
 * @author GititSurvey GmbH
 */

require_once __DIR__ . '/../vendor/autoload.php';

$packages = include(__DIR__ . '/../vendor/composer/autoload_classmap.php');

echo 'Checking all autoloaded classes...' . PHP_EOL;

foreach ($packages as $class => $file) {
    if (!class_exists($class)
        && !interface_exists($class)
        && !trait_exists($class)) {
        echo 'Autoloader broken: Could not load class/interface/trait ' . json_encode($class) . PHP_EOL;
        exit(1);
    }
}

// All good.
echo 'All good.' . PHP_EOL;
exit(0);
