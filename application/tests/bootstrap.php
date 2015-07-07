<?php

define('BASEPATH', true);

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../framework/yiit.php';
require_once __DIR__.'/../core/LSYii_Application.php';

$config = include __DIR__.'/../config/internal.php';
unset($config['defaultController']);
unset($config['config']);

Yii::createConsoleApplication($config);
Yii::$enableIncludePath = false;

abstract class CTestCase extends PHPUnit_Framework_TestCase
{
}
