<?php

/**
 * ISSUE: https://github.com/tiamo/spss/issues/12.
 */
require __DIR__ . '/../vendor/autoload.php';

$file = __DIR__ . '/data12.sav';

$writer = new \SPSS\Sav\Writer(array(
    'header' => array(
        'prodName' => '@(#) IBM SPSS STATISTICS',
        'layoutCode' => 2,
        'creationDate' => date('d M y'),
        'creationTime' => date('H:i:s'),
    ),
    'variables' => array(
        array(
            'name' => 'aaa',
            'width' => 16,
            'format' => 1,
        ),
        array(
            'name' => 'ccc',
            'format' => 5,
            'values' => array(
                1 => 'Panel',
            ),
        ),
    ),
));

$writer->save($file);
