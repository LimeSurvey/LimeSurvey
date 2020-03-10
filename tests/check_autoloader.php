<?php

/**
 * Small script to make sure running `composer dump-autoload` does not change any files.
 * If it does, it means some vendor package was not added to git.
 *
 * @since 2020-03-10
 * @author Olle Haerstedt
 */

chdir(__DIR__ . '/..');

/** @var string */
$command = "composer dump-autoload --no-dev";
$output = [];
$returnValue = null;

exec($command, $output, $returnValue);

if ($returnValue !== 0) {
    echo 'Could not run `composer dump-autoload --no-dev`.';
    exit(3);
}

/** @var string */
$command = "git status -s | sed 's/^ //g' | grep ^M | wc -l";

/** @var string[] */
$output = [];

exec($command, $output);

if (count($output) !== 1) {
    echo 'Got more than one line from command, aborting' . PHP_EOL;
    exit(1);
}

/** @var int */
$changedLines = intval(trim($output[0]));

if ($changedLines !== 0) {
    echo 'Some lines changed when running `composer dump-autoload`, which means some vendor package is not added to git. Please review.' . PHP_EOL;
    exit(2);
} else {
    // All good.
    echo 'Autoload check OK.' . PHP_EOL;
    exit(0);
}
