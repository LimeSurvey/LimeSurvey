<?php

require \dirname(__FILE__) . \DIRECTORY_SEPARATOR . './vendor/autoload.php';

$config = \dirname(__FILE__) . \DIRECTORY_SEPARATOR . 'protected/config/twig.php';

YiiBase::setPathOfAlias('root', \realpath(\dirname(__FILE__)));
Yii::createWebApplication($config)->run();
