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
 * $Id$
 */

//Ensure script is not run directly, avoid path disclosure
if (isset($_REQUEST['rootdir'])) {die('You cannot start this script directly');}
require_once(dirname(__FILE__).'/../../config-defaults.php');
require_once(dirname(__FILE__).'/../../common.php');
require_once($rootdir.'/classes/core/language.php');
$clang = new limesurvey_lang("en");

$dbname = $databasename;

sendcacheheaders();

echo "<br />\n";
echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>".$clang->gT("Create Database")."</strong></td></tr>\n";
echo "\t<tr bgcolor='#CCCCCC'><td align='center'>$setfont\n";

// In Step2 fill the database with data
if (returnglobal('createdbstep2')==$clang->gT("Populate Database"))
{
    $createdbtype=$databasetype;
    if ($databasetype=='mysql' || $databasetype=='mysqli') {
        @$connect->Execute("ALTER DATABASE `$dbname` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
        $createdbtype='mysql';
    }
    if ($createdbtype=='mssql_n' || $createdbtype=='odbc_mssql' || $createdbtype=='odbtp') $createdbtype='mssql';
   if($createdbtype=='mssqlnative') $createdbtype='mssqlnative';
    if (modify_database(dirname(__FILE__).'/create-'.$createdbtype.'.sql'))
    {
        echo sprintf($clang->gT("Database `%s` has been successfully populated."),$dbname)."</font></strong></font><br /><br />\n";
        echo "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick='location.href=\"../$scriptname\"'>";
        exit;
    }
    else
    {
        echo $modifyoutput;
        echo"Error";
    }

}

if (!$dbname)
{
    echo "<br /><strong>$setfont<font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
    echo $clang->gT("Database Information not provided. This script must be run from admin.php only.");

    echo "<br /><br />\n";
    echo "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick='location.href=\"../$scriptname\"'>";
    exit;
}

if (!$database_exists) //Database named in config-defaults.php does not exist
{
    // TODO SQL: Portable to other databases??
    switch ($databasetype)
    {
        case 'mysqli':
        case 'mysql': $createDb=$connect->Execute("CREATE DATABASE `$dbname` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        break;
        case 'mssql_n':
        case 'odbc_mssql':
		case 'mssqlnative':
        case 'odbtp': $createDb=$connect->Execute("CREATE DATABASE [$dbname];");
        break;
        default: $createDb=$connect->Execute("CREATE DATABASE $dbname");
    }
    if ($createDb) //Database has been successfully created
    {
        $connect->database = $dbname;
        $connect->Execute("USE DATABASE `$dbname`");
        echo "<br />$setfont<strong><font class='successtitle'>\n";
        echo $clang->gT("Database has been created.")."</font></strong></font><br /><br />\n";
        echo $clang->gT("Please click below to populate the database")."<br /><br />\n";
        echo "<form method='post'>";
        echo "<input type='submit' name='createdbstep2' value='".$clang->gT("Populate Database")."' onclick='location.href=\"createdb.php\"'></form>";
    }
    else
    {
        echo "<strong>$setfont<font color='red'>".$clang->gT("Error")."</font></strong></font><br />\n";
        echo $clang->gT("Could not create database")." ($dbname)<br /><font size='1'>\n";
        echo $connect->ErrorMsg();
        echo "</font><br /><br />\n";
        echo "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick='location.href=\"../$scriptname\"'>";
    }
}
echo "</td></tr></table>\n";

?>
