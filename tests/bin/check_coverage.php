<?php

// @see https://ocramius.github.io/blog/automated-code-coverage-check-for-github-pull-requests-with-travis/
// Usage:
// $ XDEBUG_MODE=coverage php7.4 ./vendor/bin/phpunit --coverage-clover cov.xml
// $ php coverage-checker.php cov.xml 80

$inputFile  = $argv[1];
$percentage = min(100, max(0, (int) $argv[2]));

if (!file_exists($inputFile)) {
    // We don't throw an exception here, because we want the check to be ignored for PHP 8 in CI (phpunit 8 does not support coverage for PHP 8).
    echo "Did not find coverage file, skipping check" . PHP_EOL;
    exit(0);
}

if (!$percentage) {
    throw new InvalidArgumentException('An integer checked percentage must be given as second parameter');
}

$xml             = new SimpleXMLElement(file_get_contents($inputFile));
$metrics         = $xml->xpath('//metrics');
$totalElements   = 0;
$checkedElements = 0;

foreach ($metrics as $metric) {
    $totalElements   += (int) $metric['elements'];
    $checkedElements += (int) $metric['coveredelements'];
}

$coverage = ($checkedElements / $totalElements) * 100;

if ($coverage < $percentage) {
    echo 'Code coverage is ' . $coverage . '%, which is below the accepted ' . $percentage . '%' . PHP_EOL;
    exit(1);
}

echo 'Code coverage is ' . $coverage . '% - OK!' . PHP_EOL;
