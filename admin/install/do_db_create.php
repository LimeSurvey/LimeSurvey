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


require_once(dirname(__FILE__).'/../../config-defaults.php');
require_once(dirname(__FILE__).'/../../common.php');
// SET THE LANGUAGE???? -> DEFAULT SET TO EN FOR NOW
require_once($rootdir.'/classes/core/language.php');
$clang = new limesurvey_lang("en");
ob_implicit_flush(true);

if ($databasetype=='mysql') {@$connect->Execute("ALTER DATABASE `$dbname` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;");} //Set the collation also for manually created DBs
   
if (modify_database(dirname(__FILE__).'/create-'.$databasetype.'.sql'))
{
	print("Creating $dbname on $databasetype\n");
	return 0;
} else {
	print("Could not create $dbname on $databasetype\n");
	return 1;
}
?>
