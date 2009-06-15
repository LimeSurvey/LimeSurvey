<?php
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* $Id: index.php 4037 2008-01-20 15:23:53Z c_schmitz $
*/

require_once(dirname(__FILE__).'/../../config-defaults.php');  
require_once(dirname(__FILE__).'/../../common.php');
require_once($rootdir.'/classes/core/language.php');
$clang = new limesurvey_lang("en");
ob_implicit_flush(true);
sendcacheheaders();

switch ($databasetype) 
{
    case 'mysqli':
    case 'mysql' : modify_database("",'UPDATE `prefix_users` set password=\'$defaultpass\' where uid=1;'); 
                echo $modifyoutput; flush();
                break;
    case 'odbtp':
    case 'mssql_n':         
    case 'odbc_mssql':modify_database("",'UPDATE [prefix_users] set password=\'$defaultpass\' where uid=1;'); 
		     echo $modifyoutput; flush();
		     break;
    case 'postgres':modify_database("",'UPDATE prefix_users set \"password\"=\'$defaultpass\' where uid=1;'); 
		     echo $modifyoutput; flush();
		     break;
}
?>
