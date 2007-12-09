<?PHP
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
* $Id: upgrade-odbc_mssql.php 3631 2007-11-12 18:13:06Z c_schmitz $
*/

// There will be a file for each database (accordingly named to the dbADO scheme)
// where based on the current database version the database is upgraded
// For this there will be a settings table which holds the last time the database was upgraded

function db_upgrade($oldversion) {

    if ($oldversion < 118) {
	//Adds new public field (Provided by Kadejo)
    modify_database("","ALTER TABLE \"prefix_surveys\" ADD  \"public\" CHAR(1) DEFAULT 'N'"); echo $modifyoutput; flush(); 
    upgrade_survey_tables117();
	upgrade_survey_tables118()
	modify_database("","UPDATE \"prefix_settings_global\" SET \"stg_value\"='118' WHERE \"stg_name\"='DBVersion'");	echo $modifyoutput;	flush();
	}


    return true;
}

function upgrade_survey_tables117()
{
    global $modifyoutput;
    $surveyidquery = "SELECT sid FROM ".db_table_name('surveys')." WHERE active='Y' and datestamp='Y'";
    $surveyidresult = db_execute_num($surveyidquery);
    if (!$surveyidresult) {return "Database Error";}
    else
        {
        while ( $sv = $surveyidresult->FetchRow() )
            {
            modify_database("","ALTER TABLE ".db_table_name('survey_'.$sv[0])." ADD \"startdate\" datetime NOT NULL"); echo $modifyoutput; flush();
            }
        }
}

function upgrade_survey_tables118()
{
  	global $connect,$modifyoutput,$dbprefix;
  	$tokentables=$connect->MetaTables('TABLES',false,$dbprefix."tokens%");
    foreach ($tokentables as $sv)
            {
            modify_database("","ALTER TABLE ".$sv." ALTER \"token\" VARCHAR(15)"); echo $modifyoutput; flush();
            }
}

?>
