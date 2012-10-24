<?php
require_once dirname(__FILE__) . '/../../../framework/yiit.php';

define('BASEPATH', dirname(__FILE__) . '/../../../');
define('APPPATH', dirname(__FILE__) . '/../../../application/');

require_once APPPATH . 'core/LSYii_Application.php';

define('EXT', '.php');

yii::createApplication('LSYii_Application', APPPATH . 'config/config-sample.php');