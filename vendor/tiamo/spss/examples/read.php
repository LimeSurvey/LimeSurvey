<?php

require __DIR__ . '/../vendor/autoload.php';

$files = array(
    // __DIR__ . '/test2.sav',
    __DIR__ . '/data.sav',
);

function __header($str, $char = '#')
{
    $line = str_repeat($char, 100);

    $res = '';
    $res .= $line . PHP_EOL;
    $res .= "#\t\t" . $str . PHP_EOL;
    $res .= $line . PHP_EOL;

    return $res;
}

function __title($title, $char = '.')
{
    return PHP_EOL .
        str_repeat($char, 10) . ' ' .
        mb_strtoupper($title) . ' ' .
        str_repeat($char, 70) .
        PHP_EOL;
}

function __content($data)
{
    $data = json_encode($data);
    // $data = json_decode($data, true);

    return print_r($data, true);
}

foreach ($files as $file) {
    $reader = \SPSS\Sav\Reader::fromFile($file)->read();

    echo PHP_EOL;

    echo __header(sprintf('OPEN FILE %s', $file));

    echo __title('Header');
    echo __content($reader->header);

    echo __title('Documents');
    echo __content($reader->documents);

    echo __title('Variables');
    echo __content($reader->variables);

    echo __title('Values-labels');
    echo __content($reader->valueLabels);

    echo __title('Additional-info');
    echo __content($reader->info);

    echo __title('Data');
    echo __content($reader->data);

    echo PHP_EOL;
}
