#!/usr/bin/php
<?php   
  if (!isset($argv[0])) die();
  define('BASEPATH','.');
  $config=require ('..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');
  unset ($config['defaultController']);
  unset ($config['config']);
  require ('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'yiic.php');

?>