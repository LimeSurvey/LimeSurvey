<?php

/**
 * Run this command to check a folder for errors and style.
 * If this file doesn't work or the listed errors are weird, please
 * contact author.
 *
 * Usage:
 *   php tests/rulesets/run_checkers.php <folder with php files>
 *
 * Before running this file, you need to install phpmd, phpcs and psalm (composer install
 * should be enough for psalm). TODO: More instructions.
 *
 * @author Olle Haerstedt
 * @since 2018-04-16
 */

if (empty($argv[1])) {
    die('No extension folder name');
}

if (!is_dir($argv[1])) {
    die($argv[1] . ' is not a folder');
}

function checkFile($file)
{
    $phpmd_output = [];
    exec(sprintf('phpmd %s text tests/rulesets/phpmd_ruleset.xml', $file), $phpmd_output);

    $phpcs_output = [];
    exec(sprintf('phpcs --report=emacs --standard=tests/rulesets/phpcs_ruleset.xml %s', $file), $phpcs_output);

    $psalm_output = [];
    exec(sprintf('./third_party/bin/psalm -m --output-format=emacs %s', $file), $psalm_output);

    $output = array_merge($phpmd_output, $phpcs_output, $psalm_output);

    echo implode(PHP_EOL, $output);
}

// TODO: Loop all PHP files in folder.
function checkDir($dirname)
{
    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Recurse
        checkDir($dirname.DIRECTORY_SEPARATOR.$entry);
    }
    // Clean up
    $dir->close();
}

exit (count($output) == 0 ? 0 : 1);
