<?php

/**
 * Run this command to check a file for problems.
 * If this file doesn't work or the listed errors are weird, please
 * contact author.
 *
 * Usage:
 *   php tests/rulesets/run_checkers.php <file to check>
 *
 * Before running this file, you need to install phpmd, phpcs and psalm (composer install
 * should be enough for psalm). TODO: More instructions.
 *
 * @author Olle Haerstedt
 * @since 2018-04-16
 */

if (empty($argv[1])) {
    die('No filename');
}

$phpmd_output = [];
exec(sprintf('phpmd %s text tests/rulesets/phpmd_ruleset.xml', $argv[1]), $phpmd_output);

$phpcs_output = [];
exec(sprintf('phpcs --report=emacs --standard=tests/rulesets/phpcs_ruleset.xml %s', $argv[1]), $phpcs_output);

$psalm_output = [];
exec(sprintf('./third_party/bin/psalm -m --output-format=emacs %s', $argv[1]), $psalm_output);

$output = array_merge($phpmd_output, $phpcs_output, $psalm_output);

echo implode(PHP_EOL, $output);
