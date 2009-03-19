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
global $modifyoutput;

	if ($oldversion < 127) {
        modify_database("","create index answers_idx2 on prefix_answers (sortorder)"); echo $modifyoutput;  flush();
        modify_database("","create index assessments_idx2 on prefix_assessments (sid)"); echo $modifyoutput;  flush();
        modify_database("","create index assessments_idx on prefix_assessments (gid)"); echo $modifyoutput;  flush();
        modify_database("","create index conditions_idx2 on prefix_conditions (qid)"); echo $modifyoutput;  flush();
        modify_database("","create index conditions_idx3 on prefix_conditions (cqid)"); echo $modifyoutput;  flush();
        modify_database("","create index groups_idx2 on prefix_groups (sid)"); echo $modifyoutput;  flush();
        modify_database("","create index question_attributes_idx2 on prefix_question_attributes (qid)"); echo $modifyoutput;  flush();
        modify_database("","create index questions_idx2 on prefix_questions (sid)"); echo $modifyoutput;  flush();
        modify_database("","create index questions_idx3 on prefix_questions (gid)"); echo $modifyoutput;  flush();
        modify_database("","create index questions_idx4 on prefix_questions (type)"); echo $modifyoutput;  flush();
        modify_database("","create index quota_idx2 on prefix_quota (sid)"); echo $modifyoutput;  flush();
        modify_database("","create index saved_control_idx2 on prefix_saved_control (sid)"); echo $modifyoutput;  flush();
        modify_database("","create index user_in_groups_idx1 on prefix_user_in_groups (ugid, uid)"); echo $modifyoutput;  flush();
        modify_database("","update prefix_settings_global set stg_value='127' where stg_name='DBVersion'"); echo $modifyoutput; flush();        
	}

	if ($oldversion < 128) {
		//128
		upgrade_token_tables128();
	    modify_database("","update prefix_settings_global set stg_value='128' where stg_name='DBVersion'"); echo $modifyoutput; flush();        
	}

	if ($oldversion < 129) {
		//129
        modify_database("","ALTER TABLE prefix_surveys ADD startdate date"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE prefix_surveys ADD usestartdate char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();
	    modify_database("","update prefix_settings_global set stg_value='129' where stg_name='DBVersion'"); echo $modifyoutput; flush();        
	}
	
	if ($oldversion < 130)
	{
		modify_database("","ALTER TABLE prefix_conditions ADD scenario integer NOT NULL default '1'"); echo $modifyoutput; flush();
		modify_database("","UPDATE prefix_conditions SET scenario=1 where (scenario is null) or scenario=0"); echo $modifyoutput; flush();
	    modify_database("","update prefix_settings_global set stg_value='130' where stg_name='DBVersion'"); echo $modifyoutput; flush();        
	}
    if ($oldversion < 131)
    {
        modify_database("","ALTER TABLE prefix_surveys ADD publicstatistics char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();
        modify_database("","update prefix_settings_global set stg_value='131' where stg_name='DBVersion'"); echo $modifyoutput; flush();        
    }
    if ($oldversion < 132)
    {
        modify_database("","ALTER TABLE prefix_surveys ADD publicgraphs char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();
        modify_database("","update prefix_settings_global set stg_value='132' where stg_name='DBVersion'"); echo $modifyoutput; flush();        
    }
	if ($oldversion < 133)
    {
        modify_database("","ALTER TABLE prefix_users ADD one_time_pw bytea"); echo $modifyoutput; flush();
        modify_database("","update prefix_settings_global set stg_value='133' where stg_name='DBVersion'"); echo $modifyoutput; flush();        
    }
    return true;
}

function upgrade_token_tables128()
{
    global $modifyoutput,$dbprefix;
    $surveyidquery = db_select_tables_like($dbprefix."tokens%");
    $surveyidresult = db_execute_num($surveyidquery);
    if (!$surveyidresult) {return "Database Error";}
    else
    {
		while ( $sv = $surveyidresult->FetchRow() )
		{
			modify_database("","ALTER TABLE ".$sv[0]." ADD remindersent VARCHAR(17) DEFAULT 'N'"); echo $modifyoutput; flush();
			modify_database("","ALTER TABLE ".$sv[0]." ADD remindercount INTEGER DEFAULT 0"); echo $modifyoutput; flush();
		}
	}
}

?>
