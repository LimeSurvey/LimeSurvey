<?php

/** This file is part of KCFinder project
  *
  *      @desc Autoload classes magic function
  *   @package KCFinder
  *   @version 2.21
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

require_once(dirname(__FILE__).'/../../../../config-defaults.php');
require_once(dirname(__FILE__).'/../../../../common.php');
require_once(dirname(__FILE__).'/../../../admin_functions.php');

$usquery = "SELECT stg_value FROM ".db_table_name("settings_global")." where stg_name='SessionName'";
$usresult = db_execute_assoc($usquery,'',true);
if ($usresult)
{
    $usrow = $usresult->FetchRow();
    @session_name($usrow['stg_value']);
}
else
{
    session_name("LimeSurveyAdmin");
}

session_set_cookie_params(0,$relativeurl.'/');



function __autoload($class) {
    if ($class == "uploader")
        require "core/uploader.php";
    elseif ($class == "browser")
        require "core/browser.php";
    elseif (file_exists("core/types/$class.php"))
        require "core/types/$class.php";
    elseif (file_exists("lib/class_$class.php"))
        require "lib/class_$class.php";
    elseif (file_exists("lib/helper_$class.php"))
        require "lib/helper_$class.php";
}

?>