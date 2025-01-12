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
 * @author GititSurvey GmbH
 * @since 2018-04-16
 */

if (empty($argv[1])) {
    die('No extension folder name');
}

if (!is_dir($argv[1])) {
    die($argv[1] . ' is not a folder');
}

$numberOfFiles = 0;

/**
 * @param string $file
 * @return array
 */
function checkFile($file)
{
    global $numberOfFiles;
    $numberOfFiles++;

    $phpmd_output = [];
    exec(sprintf('./vendor/bin/phpmd %s text tests/rulesets/phpmd_ruleset.xml', $file), $phpmd_output);

    $phpcs_output = [];
    exec(sprintf('./vendor/bin/phpcs --report=emacs --standard=tests/rulesets/phpcs_ruleset.xml %s', $file), $phpcs_output);

    $psalm_output = [];
    exec(sprintf('./vendor/bin/psalm -m --output-format=emacs %s', $file), $psalm_output);

    $output = array_merge($phpmd_output, $phpcs_output, $psalm_output);

    return $output;
}

/**
 * @param string $dirname
 * @param string[] $output
 * @return array
 */
function checkDir($dirname, $output = [])
{
    // Simple delete for a file
    if (is_file($dirname)) {
        $parts = pathinfo($dirname);
        if (isset($parts['extension']) && $parts['extension'] == 'php') {
            return checkFile($dirname);
        } else {
            return [];
        }
    }

    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        $output = array_merge(checkDir($dirname . DIRECTORY_SEPARATOR . $entry), $output);
    }
    // Clean up
    $dir->close();

    return $output;
}

$output = checkDir($argv[1]);

echo implode(PHP_EOL, $output) . PHP_EOL;

printf('Found %d errors or warnings in %d PHP files.' . PHP_EOL, count($output), $numberOfFiles);

//exit (count($output) == 0 ? 0 : 1);
exit(0);
