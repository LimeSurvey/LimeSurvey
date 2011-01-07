<?php
$_SERVER['HTTP_HOST']="127.0.1.1";
include_once(dirname(__FILE__).'/../../config-defaults.php');
$use_firebug_lite="";
include_once(dirname(__FILE__).'/../../common.php');
include_once($rootdir.'/classes/core/language.php');
$cron='1';
$clang = new limesurvey_lang("en");
$_SESSION['loginID']='1';
$surveyidoriginal=$argv[1];
$subaction="bounceprocessing";
require_once(dirname(__FILE__).'/../bounceprocessing.php');
?>
