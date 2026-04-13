<?php

/**
 * Autoloader for ReactEditor namespace classes.
 *
 * Registers an autoload function that automatically loads classes from the ReactEditor namespace.
 * The function converts the namespace to a file path and requires the corresponding PHP file
 * if it exists in the plugin directory.
 *
 * @param string $class The fully-qualified class name to be loaded.
 *
 * @return void Returns nothing if the class doesn't match the namespace prefix or if the file is successfully loaded.
 *              Returns early without loading if the class is not in the ReactEditor namespace.
 */
spl_autoload_register(function ($class) {
    $prefix = 'ReactEditor\\';
    $baseDir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});