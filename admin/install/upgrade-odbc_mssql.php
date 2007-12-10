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
* $Id$
*/

// There will be a file for each database (accordingly named to the dbADO scheme)
// where based on the current database version the database is upgraded
// For this there will be a settings table which holds the last time the database was upgraded

function db_upgrade($oldversion) {
/// This function does anything necessary to upgrade 
/// older versions to match current functionality
global $modifyoutput;
echo str_pad('Loading... ',4096)."<br />\n";
    if ($oldversion < 111) {
      // Language upgrades from version 110 to 111 since the language names did change

       $oldnewlanguages=array('german_informal'=>'german-informal',
                              'cns'=>'cn-Hans',
                              'cnt'=>'cn-Hant',
                              'pt_br'=>'pt-BR',
                              'gr'=>'el',
                              'jp'=>'ja',
                              'si'=>'sl',
                              'se'=>'sv',
                              'vn'=>'vi');

        foreach  ($oldnewlanguages as $oldlang=>$newlang)
        {
            modify_database("","update [prefix_answers] set [language`='$newlang' where language='$oldlang'");  echo $modifyoutput;      flush();
            modify_database("","update [prefix_questions] set [language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update [prefix_groups] set [language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update [prefix_labels] set [language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update [prefix_surveys] set [language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update [prefix_surveys_languagesettings] set [surveyls_language`='$newlang' where surveyls_language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update [prefix_users] set [lang`='$newlang' where lang='$oldlang'");echo $modifyoutput;flush();
        }



        $resultdata=db_execute_assoc("select * from ".db_table_name("labelsets"));
        while ($datarow = $resultdata->FetchRow()){
           $toreplace=$datarow['languages'];
           $toreplace=str_replace('german_informal','german-informal',$toreplace);
           $toreplace=str_replace('cns','cn-Hans',$toreplace);
           $toreplace=str_replace('cnt','cn-Hant',$toreplace);
           $toreplace=str_replace('pt_br','pt-BR',$toreplace);
           $toreplace=str_replace('gr','el',$toreplace);
           $toreplace=str_replace('jp','ja',$toreplace);
           $toreplace=str_replace('si','sl',$toreplace);
           $toreplace=str_replace('se','sv',$toreplace);
           $toreplace=str_replace('vn','vi',$toreplace);
           modify_database("","update [prefix_labelsets] set [languages`='$toreplace' where lid=".$datarow['lid']);echo $modifyoutput;flush();
        }


        $resultdata=db_execute_assoc("select * from ".db_table_name("surveys"));                 
        while ($datarow = $resultdata->FetchRow()){
           $toreplace=$datarow['additional_languages'];
           $toreplace=str_replace('german_informal','german-informal',$toreplace);
           $toreplace=str_replace('cns','cn-Hans',$toreplace);
           $toreplace=str_replace('cnt','cn-Hant',$toreplace);
           $toreplace=str_replace('pt_br','pt-BR',$toreplace);
           $toreplace=str_replace('gr','el',$toreplace);
           $toreplace=str_replace('jp','ja',$toreplace);
           $toreplace=str_replace('si','sl',$toreplace);
           $toreplace=str_replace('se','sv',$toreplace);
           $toreplace=str_replace('vn','vi',$toreplace);
           modify_database("","update [prefix_surveys] set [additional_languages`='$toreplace' where sid=".$datarow['sid']);echo $modifyoutput;flush();
        }
        modify_database("","update [prefix_settings_global] set [stg_value`='111' where stg_name='DBVersion'"); echo $modifyoutput;

    }

    if ($oldversion < 112) {
        //The size of the users_name field is now 64 char (20 char before version 112)
        modify_database("","ALTER TABLE [prefix_users] ALTER COLUMN [users_name] VARCHAR( 64 ) NOT NULL"); echo $modifyoutput; flush();
        modify_database("","update [prefix_settings_global] set [stg_value`='112' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    
    if ($oldversion < 113) {
	//No action needed
        modify_database("","update [prefix_settings_global] set [stg_value]='113' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    
    if ($oldversion < 114) {
        modify_database("","ALTER TABLE [prefix_saved_control] ALTER COLUMN [email] VARCHAR(320) NOT NULL"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE [prefix_surveys] ALTER COLUMN [adminemail] VARCHAR(320) NOT NULL"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE [prefix_users] ALTER COLUMN [email] VARCHAR(320) NOT NULL"); echo $modifyoutput; flush();
        modify_database("",'INSERT INTO [prefix_settings_global] VALUES (\'SessionName\', \'$sessionname\');');echo $modifyoutput; flush();
        modify_database("","update [prefix_settings_global] set [stg_value]='114' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    
    if ($oldversion < 118) {
    //Adds new "public" field
        modify_database("","ALTER TABLE [prefix_surveys] ADD  [printanswers] CHAR(1) DEFAULT 'N'"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE [prefix_surveys] ADD  [listpublic] CHAR(1) DEFAULT 'N'"); echo $modifyoutput; flush();
        upgrade_survey_tables117();
        upgrade_survey_tables118();
        modify_database("","update [prefix_settings_global] set [stg_value]='118' where stg_name='DBVersion'"); echo $modifyoutput; flush();
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
            modify_database("","ALTER TABLE ".db_table_name('survey_'.$sv[0])." ADD [startdate] datetime NOT NULL"); echo $modifyoutput; flush();
            }
        }
}


function upgrade_survey_tables118()
{
  	global $connect,$modifyoutput,$dbprefix;
  	$tokentables=$connect->MetaTables('TABLES',false,$dbprefix."tokens%");
    foreach ($tokentables as $sv)
            {
            modify_database("","ALTER TABLE ".$sv." ALTER COLUMN [token] VARCHAR(15)"); echo $modifyoutput; flush();
            }
}


?>
