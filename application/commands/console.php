#!/usr/bin/php
<?php   
    /*
    * LimeSurvey (tm)
    * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
    * All rights reserved.
    * License: GNU/GPL License v2 or later, see LICENSE.php
    * LimeSurvey is free software. This version may have been modified pursuant
    * to the GNU General Public License, and as distributed it includes or
    * is derivative of works licensed under the GNU General Public License or
    * other free or open source software licenses.
    * See COPYRIGHT.php for copyright notices and details.
    *
    * 
    * File edited by Sam Mousa for Marcel Minke.
    * This loader bypasses the default Yii loader and loads a custom console class instead.
    */
  if (!isset($argv[0])) die();
  define('BASEPATH','.');

  require_once(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'yii.php');
  // Load configuration.
  $sCurrentDir=dirname(__FILE__);
  $config=require (dirname($sCurrentDir).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'internal.php');
  $core = dirname($sCurrentDir) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR;
  unset ($config['defaultController']);
  unset ($config['config']);
  
  

    // fix for fcgi
    defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));

    defined('YII_DEBUG') or define('YII_DEBUG',true);

    

    if(isset($config))
    {
        require_once($core . 'ConsoleApplication.php');
        $app=Yii::createApplication('ConsoleApplication', $config);
        define('APPPATH', Yii::app()->getBasePath() . DIRECTORY_SEPARATOR);
        $app->commandRunner->addCommands(YII_PATH.'/cli/commands');
        $env=@getenv('YII_CONSOLE_COMMANDS');
        if(!empty($env))
            $app->commandRunner->addCommands($env);
    }
    $app->run();
?>