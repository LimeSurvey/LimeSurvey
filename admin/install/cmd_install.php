<?php
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Nicolas Barcet
* All rights reserved.
* License: http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* $Id: index.php 4729 2008-05-31 21:32:02Z nijaba $
*/

if (isset($argv[1]) && ($argv[1]=='install'|| $argv[1]=='upgrade') && isset($argv[2]) && isset($argv[3]))    
{
    require_once($argv[2]);
    require_once($argv[3]);
}
else
{
    require_once(dirname(__FILE__).'/../../config-defaults.php');
    require_once(dirname(__FILE__).'/../../common.php');
}
// SET THE LANGUAGE???? -> DEFAULT SET TO EN FOR NOW
require_once($rootdir.'/classes/core/language.php');
$clang = new limesurvey_lang("en");
ob_implicit_flush(true);


if (isset($argv[1]) && $argv[1]=='install')
{
    if ($databasetype=='mysql') {@$connect->Execute("ALTER DATABASE `$databasename` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;");} //Set the collation also for manually created DBs
       
    if (modify_database(dirname(__FILE__).'/create-'.$databasetype.'.sql'))
    {
    	print("Creating $databasename on $databasetype\n");
    	return 0;
    } else {
    	print("Could not create $databasename on $databasetype\n");
    	return 1;
    }
}

elseif (isset($argv[1]) && $argv[1]=='upgrade')

{
    global $connect, $databasetype, $dbprefix, $dbversionnumber, $clang;
    include ('upgrade-'.$databasetype.'.php');
    $tables = $connect->MetaTables();
    
    $usquery = "SELECT stg_value FROM ".db_table_name("settings_global")." where stg_name='DBVersion'";
    $usresult = db_execute_assoc($usquery);
    $usrow = $usresult->FetchRow();
    if (intval($usrow['stg_value'])<$dbversionnumber)
    {
    	print("Upgrading db to $dbversionnumber\n");
            db_upgrade(intval($usrow['stg_value']));
    } else {
            print("Already at db version $dbversionnumber\n");
    }
    return 0;    
}
else 
{
    print("LimeSurvey Commandline Install\n");     
    print("Usage: cmd_install <option> < <<path1>> <<path2>> >\n");     
    print("<option> - 'install' or 'upgrade' \n");     
    print("<<path1>> - Full path including filename to a custom config-defaults.php\n");     
    print("<<path2>> - Full path including filename to a custom common.php\n");     
    print("Paths are optional. If used both paths must be set.\n");     
    return 2;
}
?>
