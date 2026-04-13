<?php
define('BASEPATH', true);
// change the following paths if necessary
$yiit = __DIR__.'/../../vendor/yiisoft/yii/framework/yiit.php';

require_once($yiit);
require_once __DIR__.'/../core/LSYii_Application.php';
$config = include(__DIR__.'/../config/internal.php');
//require_once(dirname(__FILE__).'/WebTestCase.php');
unset ($config['defaultController']);
unset ($config['config']);

    Yii::createConsoleApplication($config);
    Yii::$enableIncludePath = false;

//Yii::createWebApplication('CApplication', $config);

    abstract class CTestCase extends PHPUnit_Framework_TestCase
    {
}
