<?php

$root = __DIR__
    . DIRECTORY_SEPARATOR
    . '..'
    . DIRECTORY_SEPARATOR;

$autoloadPath = $root
    . 'vendor'
    . DIRECTORY_SEPARATOR
    . 'autoload.php';

$buildPath = $root
    . 'build'
    . DIRECTORY_SEPARATOR;

$schemasPath = $root
    . 'schemas'
    . DIRECTORY_SEPARATOR;

require $autoloadPath;

$builder = new \Anper\Iuliia\Builder($schemasPath);

foreach (\Anper\Iuliia\Iuliia::SCHEMAS as $basename) {
    $schema = $builder->build($basename);

    $data = [
        $schema->getDefaultMap()->all(),
        $schema->getPrevMap()->all(),
        $schema->getNextMap()->all(),
        $schema->getEndingMap()->all(),
        $schema->getSamples(),
    ];

    $content = '<?php return ' . \var_export($data, true) . ';';

    $filepath = $buildPath . $basename . '.php';

    \file_put_contents($filepath, $content);

    \printf("%s \n", \realpath($filepath));
}
