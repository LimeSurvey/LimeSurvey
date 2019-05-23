<?PHP
/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/* Rules:
- Never use models in the upgrade process - never ever!
- Use the provided addColumn, alterColumn, dropPrimaryKey etc. functions where applicable - they ensure cross-DB compatibility
- Never use foreign keys
- Do not use fancy database field types (like mediumtext, timestamp, etc) - only use the ones provided by Yii which are:

    pk: auto-incremental primary key type (“int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY”).
    string: string type (“varchar(255)”).
    text: a long string type (“text”).
    integer: integer type (“int(11)”).
    boolean: boolean type (“tinyint(1)”).
    float: float number type (“float”).
    decimal: decimal number type (“decimal”).
    datetime: datetime type (“datetime”).
    timestamp: timestamp type (“timestamp”).
    time: time type (“time”).
    date: date type (“date”).
    binary: binary data type (“blob”).

    These are case-sensitive - only use lowercase!
- If you want to use database functions make sure they exist on all three supported database types
- Always prefix key names by using curly brackets {{ }}

*/

/**
* @param integer $iOldDBVersion The previous database version
* @param boolean $bSilent Run update silently with no output - this checks if the update can be run silently at all. If not it will not run any updates at all.
*/
function db_upgrade_all($iOldDBVersion, $bSilent = false)
{
    /**
     * If you add a new database version add any critical database version numbers to this array. See link
     * @link https://manual.limesurvey.org/Database_versioning for explanations
     * @var array $aCriticalDBVersions An array of cricital database version.
     */
    $aCriticalDBVersions = array(310);
    $aAllUpdates         = range($iOldDBVersion + 1, Yii::app()->getConfig('dbversionnumber'));

    // If trying to update silenty check if it is really possible
    if ($bSilent && (count(array_intersect($aCriticalDBVersions, $aAllUpdates)) > 0)) {
        return false;
    }
    // If DBVersion is older than 184 don't allow database update
    If ($iOldDBVersion < 132) {
        return false;
    }

    /// This function does anything necessary to upgrade
    /// older versions to match current functionality

    Yii::app()->loadHelper('database');
    Yii::import('application.helpers.admin.import_helper', true);
    $sUserTemplateRootDir       = Yii::app()->getConfig('userthemerootdir');
    $sStandardTemplateRootDir   = Yii::app()->getConfig('standardthemerootdir');
    $oDB                        = Yii::app()->getDb();
    $oDB->schemaCachingDuration = 0; // Deactivate schema caching
    Yii::app()->setConfig('Updating', true);

    try {

        // Version 1.80 had database version 132
        // This is currently the oldest version we need support to update from
        if ($iOldDBVersion < 133)
        {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{users}}','one_time_pw','binary');
            // Add new assessment setting
            addColumn('{{surveys}}','assessments',"string(1) NOT NULL default 'N'");
            // add new assessment value fields to answers & labels
            addColumn('{{answers}}','assessment_value',"integer NOT NULL default '0'");
            addColumn('{{labels}}','assessment_value',"integer NOT NULL default '0'");
            // copy any valid codes from code field to assessment field
            switch (Yii::app()->db->driverName){
                case 'mysql':
                case 'mysqli':
                    $oDB->createCommand("UPDATE {{answers}} SET assessment_value=CAST(`code` as SIGNED) where `code` REGEXP '^-?[0-9]+$'")->execute();
                    $oDB->createCommand("UPDATE {{labels}} SET assessment_value=CAST(`code` as SIGNED) where `code` REGEXP '^-?[0-9]+$'")->execute();
                    // copy assessment link to message since from now on we will have HTML assignment messages
                    $oDB->createCommand("UPDATE {{assessments}} set message=concat(replace(message,'/''',''''),'<br /><a href=\"',link,'\">',link,'</a>')")->execute();
                    break;
                case 'sqlsrv':
                case 'dblib':
                case 'mssql':
                try{
                    $oDB->createCommand("UPDATE {{answers}} SET assessment_value=CAST([code] as int) WHERE ISNUMERIC([code])=1")->execute();
                    $oDB->createCommand("UPDATE {{labels}} SET assessment_value=CAST([code] as int) WHERE ISNUMERIC([code])=1")->execute();
                } catch(Exception $e){};
                // copy assessment link to message since from now on we will have HTML assignment messages
                alterColumn('{{assessments}}','link',"text",false);
                alterColumn('{{assessments}}','message',"text",false);
                $oDB->createCommand("UPDATE {{assessments}} set message=replace(message,'/''','''')+'<br /><a href=\"'+link+'\">'+link+'</a>'")->execute();
                break;
                case 'pgsql':
                    $oDB->createCommand("UPDATE {{answers}} SET assessment_value=CAST(code as integer) where code ~ '^[0-9]+'")->execute();
                    $oDB->createCommand("UPDATE {{labels}} SET assessment_value=CAST(code as integer) where code ~ '^[0-9]+'")->execute();
                    // copy assessment link to message since from now on we will have HTML assignment messages
                    $oDB->createCommand("UPDATE {{assessments}} set message=replace(message,'/''','''')||'<br /><a href=\"'||link||'\">'||link||'</a>'")->execute();
                    break;
            }
            // activate assessment where assessment rules exist
            $oDB->createCommand("UPDATE {{surveys}} SET assessments='Y' where sid in (SELECT sid FROM {{assessments}} group by sid)")->execute();
            // add language field to assessment table
            addColumn('{{assessments}}','language',"string(20) NOT NULL default 'en'");
            // update language field with default language of that particular survey
            $oDB->createCommand("UPDATE {{assessments}} SET language=(select language from {{surveys}} where sid={{assessments}}.sid)")->execute();
            // drop the old link field
            dropColumn('{{assessments}}','link');

            // Add new fields to survey language settings
            addColumn('{{surveys_languagesettings}}','surveyls_url',"string");
            addColumn('{{surveys_languagesettings}}','surveyls_endtext','text');
            // copy old URL fields ot language specific entries
            $oDB->createCommand("UPDATE {{surveys_languagesettings}} set surveyls_url=(select url from {{surveys}} where sid={{surveys_languagesettings}}.surveyls_survey_id)")->execute();
            // drop old URL field
            dropColumn('{{surveys}}','url');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>133),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 134)
        {
            $oTransaction = $oDB->beginTransaction();
            // Add new tokens setting
            addColumn('{{surveys}}','usetokens',"string(1) NOT NULL default 'N'");
            addColumn('{{surveys}}','attributedescriptions','text');
            dropColumn('{{surveys}}','attribute1');
            dropColumn('{{surveys}}','attribute2');
            upgradeTokenTables134();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>134),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 135)
        {
            $oTransaction = $oDB->beginTransaction();
            alterColumn('{{question_attributes}}','value','text');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>135),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 136) //New Quota Functions
        {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{quota}}','autoload_url',"integer NOT NULL default 0");
            // Create quota table
            $aFields = array(
                'quotals_id' => 'pk',
                'quotals_quota_id' => 'integer NOT NULL DEFAULT 0',
                'quotals_language' => "string(45) NOT NULL default 'en'",
                'quotals_name' => 'string',
                'quotals_message' => 'text NOT NULL',
                'quotals_url' => 'string',
                'quotals_urldescrip' => 'string',
            );
            $oDB->createCommand()->createTable('{{quota_languagesettings}}',$aFields);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>136),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 137) //New Quota Functions
        {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{surveys_languagesettings}}','surveyls_dateformat',"integer NOT NULL default 1");
            addColumn('{{users}}','dateformat',"integer NOT NULL default 1");
            $oDB->createCommand()->update('{{surveys}}',array('startdate'=>NULL),"usestartdate='N'");
            $oDB->createCommand()->update('{{surveys}}',array('expires'=>NULL),"useexpiry='N'");
            dropColumn('{{surveys}}','useexpiry');
            dropColumn('{{surveys}}','usestartdate');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>137),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 138) //Modify quota field
        {
            $oTransaction = $oDB->beginTransaction();
            alterColumn('{{quota_members}}','code',"string(11)");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>138),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 139) //Modify quota field
        {
            $oTransaction = $oDB->beginTransaction();
            upgradeSurveyTables139();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>139),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 140) //Modify surveys table
        {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{surveys}}','emailresponseto','text');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>140),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 141) //Modify surveys table
        {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{surveys}}','tokenlength','integer NOT NULL default 15');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>141),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 142) //Modify surveys table
        {
            $oTransaction = $oDB->beginTransaction();
            upgradeQuestionAttributes142();
            $oDB->createCommand()->alterColumn('{{surveys}}','expires',"datetime");
            $oDB->createCommand()->alterColumn('{{surveys}}','startdate',"datetime");
            $oDB->createCommand()->update('{{question_attributes}}',array('value'=>0),"value='false'");
            $oDB->createCommand()->update('{{question_attributes}}',array('value'=>1),"value='true'");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>142),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 143)
        {

            $oTransaction = $oDB->beginTransaction();
            addColumn('{{questions}}','parent_qid','integer NOT NULL default 0');
            addColumn('{{answers}}','scale_id','integer NOT NULL default 0');
            addColumn('{{questions}}','scale_id','integer NOT NULL default 0');
            addColumn('{{questions}}','same_default','integer NOT NULL default 0');
            dropPrimaryKey('answers');
            addPrimaryKey('answers', array('qid','code','language','scale_id'));

            $aFields = array(
                'qid' => "integer NOT NULL default 0",
                'scale_id' => 'integer NOT NULL default 0',
                'sqid' => 'integer  NOT NULL default 0',
                'language' => 'string(20) NOT NULL',
                'specialtype' => "string(20) NOT NULL default ''",
                'defaultvalue' => 'text',
            );
            $oDB->createCommand()->createTable('{{defaultvalues}}',$aFields);
            addPrimaryKey('defaultvalues', array('qid','specialtype','language','scale_id','sqid'));

            // -Move all 'answers' that are subquestions to the questions table
            // -Move all 'labels' that are answers to the answers table
            // -Transscribe the default values where applicable
            // -Move default values from answers to questions
            upgradeTables143();

            dropColumn('{{answers}}','default_value');
            dropColumn('{{questions}}','lid');
            dropColumn('{{questions}}','lid1');

            $aFields = array(
                'sesskey' => "string(64) NOT NULL DEFAULT ''",
                'expiry' => "datetime NOT NULL",
                'expireref' => "string(250) DEFAULT ''",
                'created' => "datetime NOT NULL",
                'modified' => "datetime NOT NULL",
                'sessdata' => 'text'
            );
            $oDB->createCommand()->createTable('{{sessions}}',$aFields);
            addPrimaryKey('sessions',array('sesskey'));
            $oDB->createCommand()->createIndex('sess2_expiry','{{sessions}}','expiry');
            $oDB->createCommand()->createIndex('sess2_expireref','{{sessions}}','expireref');
            // Move all user templates to the new user template directory
            echo "<br>".sprintf(gT("Moving user templates to new location at %s..."),$sUserTemplateRootDir)."<br />";
            $hTemplateDirectory = opendir($sStandardTemplateRootDir);
            $aFailedTemplates=array();
            // get each entry
            while($entryName = readdir($hTemplateDirectory)) {
                if (!in_array($entryName,array('.','..','.svn')) && is_dir($sStandardTemplateRootDir.DIRECTORY_SEPARATOR.$entryName) && !isStandardTemplate($entryName))
                {
                    if (!rename($sStandardTemplateRootDir.DIRECTORY_SEPARATOR.$entryName,$sUserTemplateRootDir.DIRECTORY_SEPARATOR.$entryName))
                    {
                        $aFailedTemplates[]=$entryName;
                    };
                }
            }
            if (count($aFailedTemplates)>0)
            {
                echo "The following templates at {$sStandardTemplateRootDir} could not be moved to the new location at {$sUserTemplateRootDir}:<br /><ul>";
                foreach ($aFailedTemplates as $sFailedTemplate)
                {
                    echo "<li>{$sFailedTemplate}</li>";
                }
                echo "</ul>Please move these templates manually after the upgrade has finished.<br />";
            }
            // close directory
            closedir($hTemplateDirectory);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>143),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 145)
        {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{surveys}}','savetimings',"string(1) NULL default 'N'");
            addColumn('{{surveys}}','showXquestions',"string(1) NULL default 'Y'");
            addColumn('{{surveys}}','showgroupinfo',"string(1) NULL default 'B'");
            addColumn('{{surveys}}','shownoanswer',"string(1) NULL default 'Y'");
            addColumn('{{surveys}}','showqnumcode',"string(1) NULL default 'X'");
            addColumn('{{surveys}}','bouncetime','integer');
            addColumn('{{surveys}}','bounceprocessing',"string(1) NULL default 'N'");
            addColumn('{{surveys}}','bounceaccounttype',"string(4)");
            addColumn('{{surveys}}','bounceaccounthost',"string(200)");
            addColumn('{{surveys}}','bounceaccountpass',"string(100)");
            addColumn('{{surveys}}','bounceaccountencryption',"string(3)");
            addColumn('{{surveys}}','bounceaccountuser',"string(200)");
            addColumn('{{surveys}}','showwelcome',"string(1) default 'Y'");
            addColumn('{{surveys}}','showprogress',"string(1) default 'Y'");
            addColumn('{{surveys}}','allowjumps',"string(1) default 'N'");
            addColumn('{{surveys}}','navigationdelay',"integer default 0");
            addColumn('{{surveys}}','nokeyboard',"string(1) default 'N'");
            addColumn('{{surveys}}','alloweditaftercompletion',"string(1) default 'N'");


            $aFields = array(
                'sid' => "integer NOT NULL",
                'uid' => "integer NOT NULL",
                'permission' => 'string(20) NOT NULL',
                'create_p' => "integer NOT NULL default 0",
                'read_p' => "integer NOT NULL default 0",
                'update_p' => "integer NOT NULL default 0",
                'delete_p' => "integer NOT NULL default 0",
                'import_p' => "integer NOT NULL default 0",
                'export_p' => "integer NOT NULL default 0"
            );
            $oDB->createCommand()->createTable('{{survey_permissions}}',$aFields);
            addPrimaryKey('survey_permissions', array('sid','uid','permission'));

            upgradeSurveyPermissions145();

            // drop the old survey rights table
            $oDB->createCommand()->dropTable('{{surveys_rights}}');

            // Add new fields for email templates
            addColumn('{{surveys_languagesettings}}','email_admin_notification_subj',"string");
            addColumn('{{surveys_languagesettings}}','email_admin_responses_subj',"string");
            addColumn('{{surveys_languagesettings}}','email_admin_notification',"text");
            addColumn('{{surveys_languagesettings}}','email_admin_responses',"text");

            //Add index to questions table to speed up subquestions
            $oDB->createCommand()->createIndex('parent_qid_idx','{{questions}}','parent_qid');

            addColumn('{{surveys}}','emailnotificationto',"text");

            upgradeSurveys145();
            dropColumn('{{surveys}}','notification');
            alterColumn('{{conditions}}','method',"string(5)",false,'');

            $oDB->createCommand()->renameColumn('{{surveys}}','private','anonymized');
            $oDB->createCommand()->update('{{surveys}}',array('anonymized'=>'N'),"anonymized is NULL");
            alterColumn('{{surveys}}','anonymized',"string(1)",false,'N');

            //now we clean up things that were not properly set in previous DB upgrades
            $oDB->createCommand()->update('{{answers}}',array('answer'=>''),"answer is NULL");
            $oDB->createCommand()->update('{{assessments}}',array('scope'=>''),"scope is NULL");
            $oDB->createCommand()->update('{{assessments}}',array('name'=>''),"name is NULL");
            $oDB->createCommand()->update('{{assessments}}',array('message'=>''),"message is NULL");
            $oDB->createCommand()->update('{{assessments}}',array('minimum'=>''),"minimum is NULL");
            $oDB->createCommand()->update('{{assessments}}',array('maximum'=>''),"maximum is NULL");
            $oDB->createCommand()->update('{{groups}}',array('group_name'=>''),"group_name is NULL");
            $oDB->createCommand()->update('{{labels}}',array('code'=>''),"code is NULL");
            $oDB->createCommand()->update('{{labelsets}}',array('label_name'=>''),"label_name is NULL");
            $oDB->createCommand()->update('{{questions}}',array('type'=>'T'),"type is NULL");
            $oDB->createCommand()->update('{{questions}}',array('title'=>''),"title is NULL");
            $oDB->createCommand()->update('{{questions}}',array('question'=>''),"question is NULL");
            $oDB->createCommand()->update('{{questions}}',array('other'=>'N'),"other is NULL");

            alterColumn('{{answers}}','answer',"text",false);
            alterColumn('{{answers}}','assessment_value','integer',false , '0');
            alterColumn('{{assessments}}','scope',"string(5)",false , '');
            alterColumn('{{assessments}}','name',"text",false);
            alterColumn('{{assessments}}','message',"text",false);
            alterColumn('{{assessments}}','minimum',"string(50)",false , '');
            alterColumn('{{assessments}}','maximum',"string(50)",false , '');
            // change the primary index to include language
            if (Yii::app()->db->driverName=='mysql') // special treatment for mysql because this needs to be in one step since an AUTOINC field is involved
            {
                modifyPrimaryKey('assessments', array('id', 'language'));
            }
            else
            {
                dropPrimaryKey('assessments');
                addPrimaryKey('assessments',array('id','language'));
            }


            alterColumn('{{conditions}}','cfieldname',"string(50)",false , '');
            dropPrimaryKey('defaultvalues');
            alterColumn('{{defaultvalues}}','specialtype',"string(20)",false , '');
            addPrimaryKey('defaultvalues', array('qid','specialtype','language','scale_id','sqid'));

            alterColumn('{{groups}}','group_name',"string(100)",false , '');
            alterColumn('{{labels}}','code',"string(5)",false , '');
            dropPrimaryKey('labels');
            alterColumn('{{labels}}','language',"string(20)",false , 'en');
            addPrimaryKey('labels', array('lid', 'sortorder', 'language'));
            alterColumn('{{labelsets}}','label_name',"string(100)",false , '');
            alterColumn('{{questions}}','parent_qid','integer',false ,'0');
            alterColumn('{{questions}}','title',"string(20)",false , '');
            alterColumn('{{questions}}','question',"text",false);
            try { setTransactionBookmark(); $oDB->createCommand()->dropIndex('questions_idx4','{{questions}}'); } catch(Exception $e) { rollBackToTransactionBookmark();}

            alterColumn('{{questions}}','type',"string(1)",false , 'T');
            try{ $oDB->createCommand()->createIndex('questions_idx4','{{questions}}','type');} catch(Exception $e){};
            alterColumn('{{questions}}','other',"string(1)",false , 'N');
            alterColumn('{{questions}}','mandatory',"string(1)");
            alterColumn('{{question_attributes}}','attribute',"string(50)");
            alterColumn('{{quota}}','qlimit','integer');

            $oDB->createCommand()->update('{{saved_control}}',array('identifier'=>''),"identifier is NULL");
            alterColumn('{{saved_control}}','identifier',"text",false);
            $oDB->createCommand()->update('{{saved_control}}',array('access_code'=>''),"access_code is NULL");
            alterColumn('{{saved_control}}','access_code',"text",false);
            alterColumn('{{saved_control}}','email',"string(320)");
            $oDB->createCommand()->update('{{saved_control}}',array('ip'=>''),"ip is NULL");
            alterColumn('{{saved_control}}','ip',"text",false);
            $oDB->createCommand()->update('{{saved_control}}',array('saved_thisstep'=>''),"saved_thisstep is NULL");
            alterColumn('{{saved_control}}','saved_thisstep',"text",false);
            $oDB->createCommand()->update('{{saved_control}}',array('status'=>''),"status is NULL");
            alterColumn('{{saved_control}}','status',"string(1)",false , '');
            $oDB->createCommand()->update('{{saved_control}}',array('saved_date'=>'1980-01-01 00:00:00'),"saved_date is NULL");
            alterColumn('{{saved_control}}','saved_date',"datetime",false);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>''),"stg_value is NULL");
            alterColumn('{{settings_global}}','stg_value',"string",false , '');

            alterColumn('{{surveys}}','admin',"string(50)");
            $oDB->createCommand()->update('{{surveys}}',array('active'=>'N'),"active is NULL");

            alterColumn('{{surveys}}','active',"string(1)",false , 'N');

            alterColumn('{{surveys}}','startdate',"datetime");
            alterColumn('{{surveys}}','adminemail',"string(320)");
            alterColumn('{{surveys}}','anonymized',"string(1)",false , 'N');

            alterColumn('{{surveys}}','faxto',"string(20)");
            alterColumn('{{surveys}}','format',"string(1)");
            alterColumn('{{surveys}}','language',"string(50)");
            alterColumn('{{surveys}}','additional_languages',"string");
            alterColumn('{{surveys}}','printanswers',"string(1)",true , 'N');
            alterColumn('{{surveys}}','publicstatistics',"string(1)",true , 'N');
            alterColumn('{{surveys}}','publicgraphs',"string(1)",true , 'N');
            alterColumn('{{surveys}}','assessments',"string(1)",true , 'N');
            alterColumn('{{surveys}}','usetokens',"string(1)",true , 'N');
            alterColumn('{{surveys}}','bounce_email',"string(320)");
            alterColumn('{{surveys}}','tokenlength','integer',true , 15);

            $oDB->createCommand()->update('{{surveys_languagesettings}}',array('surveyls_title'=>''),"surveyls_title is NULL");
            alterColumn('{{surveys_languagesettings}}','surveyls_title',"string(200)",false);
            alterColumn('{{surveys_languagesettings}}','surveyls_endtext',"text");
            alterColumn('{{surveys_languagesettings}}','surveyls_url',"string");
            alterColumn('{{surveys_languagesettings}}','surveyls_urldescription',"string");
            alterColumn('{{surveys_languagesettings}}','surveyls_email_invite_subj',"string");
            alterColumn('{{surveys_languagesettings}}','surveyls_email_remind_subj',"string");
            alterColumn('{{surveys_languagesettings}}','surveyls_email_register_subj',"string");
            alterColumn('{{surveys_languagesettings}}','surveyls_email_confirm_subj',"string");
            alterColumn('{{surveys_languagesettings}}','surveyls_dateformat','integer',false , 1);

            $oDB->createCommand()->update('{{users}}',array('users_name'=>''),"users_name is NULL");
            $oDB->createCommand()->update('{{users}}',array('full_name'=>''),"full_name is NULL");
            alterColumn('{{users}}','users_name',"string(64)",false , '');
            alterColumn('{{users}}','full_name',"string(50)",false);
            alterColumn('{{users}}','lang',"string(20)");
            alterColumn('{{users}}','email',"string(320)");
            alterColumn('{{users}}','superadmin','integer',false , 0);
            alterColumn('{{users}}','htmleditormode',"string(7)",true,'default');
            alterColumn('{{users}}','dateformat','integer',false , 1);
            try{
                setTransactionBookmark();
                $oDB->createCommand()->dropIndex('email','{{users}}');
            }
            catch(Exception $e)
            {
                // do nothing
                rollBackToTransactionBookmark();
            }

            $oDB->createCommand()->update('{{user_groups}}',array('name'=>''),"name is NULL");
            $oDB->createCommand()->update('{{user_groups}}',array('description'=>''),"description is NULL");
            alterColumn('{{user_groups}}','name',"string(20)",false);
            alterColumn('{{user_groups}}','description',"text",false);

            try { $oDB->createCommand()->dropIndex('user_in_groups_idx1','{{user_in_groups}}'); } catch(Exception $e) {}
            try { addPrimaryKey('user_in_groups', array('ugid','uid')); } catch(Exception $e) {}

            addColumn('{{surveys_languagesettings}}','surveyls_numberformat',"integer NOT NULL DEFAULT 0");

            $oDB->createCommand()->createTable('{{failed_login_attempts}}',array(
                'id' => "pk",
                'ip' => 'string(37) NOT NULL',
                'last_attempt' => 'string(20) NOT NULL',
                'number_attempts' => "integer NOT NULL"
            ));
            upgradeTokens145();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>145),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 146) //Modify surveys table
        {
            $oTransaction = $oDB->beginTransaction();
            upgradeSurveyTimings146();
            // Fix permissions for new feature quick-translation
            try { setTransactionBookmark(); $oDB->createCommand("INSERT into {{survey_permissions}} (sid,uid,permission,read_p,update_p) SELECT sid,owner_id,'translations','1','1' from {{surveys}}")->execute();} catch(Exception $e) { rollBackToTransactionBookmark();}
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>146),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 147)
        {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{users}}','templateeditormode',"string(7) NOT NULL default 'default'");
            addColumn('{{users}}','questionselectormode',"string(7) NOT NULL default 'default'");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>147),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 148)
        {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{users}}','participant_panel',"integer NOT NULL default 0");

            $oDB->createCommand()->createTable('{{participants}}',array(
                'participant_id' => 'string(50) NOT NULL',
                'firstname' => 'string(40) default NULL',
                'lastname' => 'string(40) default NULL',
                'email' => 'string(80) default NULL',
                'language' => 'string(40) default NULL',
                'blacklisted' => 'string(1) NOT NULL',
                'owner_uid' => "integer NOT NULL"
            ));
            addPrimaryKey('participants', array('participant_id'));

            $oDB->createCommand()->createTable('{{participant_attribute}}',array(
                'participant_id' => 'string(50) NOT NULL',
                'attribute_id' => "integer NOT NULL",
                'value' => 'string(50) NOT NULL'
            ));
            addPrimaryKey('participant_attribute', array('participant_id','attribute_id'));

            $oDB->createCommand()->createTable('{{participant_attribute_names}}',array(
                'attribute_id' => 'autoincrement',
                'attribute_type' => 'string(4) NOT NULL',
                'visible' => 'string(5) NOT NULL',
                'PRIMARY KEY (attribute_id,attribute_type)'
            ));

            $oDB->createCommand()->createTable('{{participant_attribute_names_lang}}',array(
                'attribute_id' => 'integer NOT NULL',
                'attribute_name' => 'string(30) NOT NULL',
                'lang' => 'string(20) NOT NULL'
            ));
            addPrimaryKey('participant_attribute_names_lang', array('attribute_id','lang'));

            $oDB->createCommand()->createTable('{{participant_attribute_values}}',array(
                'attribute_id' => 'integer NOT NULL',
                'value_id' => 'pk',
                'value' => 'string(20) NOT NULL'
            ));

            $oDB->createCommand()->createTable('{{participant_shares}}',array(
                'participant_id' => 'string(50) NOT NULL',
                'share_uid' => 'integer NOT NULL',
                'date_added' => 'datetime NOT NULL',
                'can_edit' => 'string(5) NOT NULL'
            ));
            addPrimaryKey('participant_shares', array('participant_id','share_uid'));

            $oDB->createCommand()->createTable('{{survey_links}}',array(
                'participant_id' => 'string(50) NOT NULL',
                'token_id' => 'integer NOT NULL',
                'survey_id' => 'integer NOT NULL',
                'date_created' => 'datetime NOT NULL'
            ));
            addPrimaryKey('survey_links', array('participant_id','token_id','survey_id'));
            // Add language field to question_attributes table
            addColumn('{{question_attributes}}','language',"string(20)");
            upgradeQuestionAttributes148();
            fixSubquestions();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>148),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 149)
        {
            $oTransaction = $oDB->beginTransaction();
            $aFields = array(
                'id' => 'integer',
                'sid' => 'integer',
                'parameter' => 'string(50)',
                'targetqid' => 'integer',
                'targetsqid' => 'integer'
            );
            $oDB->createCommand()->createTable('{{survey_url_parameters}}',$aFields);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>149),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 150)
        {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{questions}}','relevance','text');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>150),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 151)
        {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{groups}}','randomization_group',"string(20) NOT NULL default ''");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>151),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 152)
        {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->createIndex('question_attributes_idx3','{{question_attributes}}','attribute');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>152),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 153)
        {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->createTable('{{expression_errors}}',array(
                'id' => 'pk',
                'errortime' => 'string(50)',
                'sid' => 'integer',
                'gid' => 'integer',
                'qid' => 'integer',
                'gseq' => 'integer',
                'qseq' => 'integer',
                'type' => 'string(50)',
                'eqn' => 'text',
                'prettyprint' => 'text'
            ));
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>153),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 154)
        {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->addColumn('{{groups}}','grelevance',"text");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>154),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 155)
        {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{surveys}}','googleanalyticsstyle',"string(1)");
            addColumn('{{surveys}}','googleanalyticsapikey',"string(25)");
            try { setTransactionBookmark(); $oDB->createCommand()->renameColumn('{{surveys}}','showXquestions','showxquestions');} catch(Exception $e) { rollBackToTransactionBookmark();}
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>155),"stg_name='DBVersion'");
            $oTransaction->commit();
        }


        if ($iOldDBVersion < 156)
        {
            $oTransaction = $oDB->beginTransaction();
            try
            {
                $oDB->createCommand()->dropTable('{{survey_url_parameters}}');
            }
            catch(Exception $e)
            {
                // do nothing
            }
            $oDB->createCommand()->createTable('{{survey_url_parameters}}',array(
                'id' => 'pk',
                'sid' => 'integer NOT NULL',
                'parameter' => 'string(50) NOT NULL',
                'targetqid' => 'integer',
                'targetsqid' => 'integer'
            ));

            try
            {
                $oDB->createCommand()->dropTable('{{sessions}}');
            }
            catch(Exception $e)
            {
                // do nothing
            }
            if (Yii::app()->db->driverName=='mysql')
            {
                $oDB->createCommand()->createTable('{{sessions}}',array(
                    'id' => 'string(32) NOT NULL',
                    'expire' => 'integer',
                    'data' => 'longtext'
                ));
            }
            else
            {
                $oDB->createCommand()->createTable('{{sessions}}',array(
                    'id' => 'string(32) NOT NULL',
                    'expire' => 'integer',
                    'data' => 'text'
                ));
            }

            addPrimaryKey('sessions', array('id'));
            addColumn('{{surveys_languagesettings}}','surveyls_attributecaptions',"text");
            addColumn('{{surveys}}','sendconfirmation',"string(1) default 'Y'");

            upgradeSurveys156();

            // If a survey has an deleted owner, re-own the survey to the superadmin
            $sSurveyQuery = "SELECT sid, uid  from {{surveys}} LEFT JOIN {{users}} ON uid=owner_id WHERE uid IS null";
            $oSurveyResult = $oDB->createCommand($sSurveyQuery)->queryAll();
            foreach ( $oSurveyResult as $row ) {
                    $oDB->createCommand("UPDATE {{surveys}} SET owner_id=1 WHERE sid={$row['sid']}")->execute();
            }
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>156),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 157)
        {
            $oTransaction = $oDB->beginTransaction();
            // MySQL DB corrections
            try { setTransactionBookmark(); $oDB->createCommand()->dropIndex('questions_idx4','{{questions}}'); } catch(Exception $e) { rollBackToTransactionBookmark();}

            alterColumn('{{answers}}','assessment_value','integer',false , '0');
            dropPrimaryKey('answers');
            alterColumn('{{answers}}','scale_id','integer',false , '0');
            addPrimaryKey('answers', array('qid','code','language','scale_id'));
            alterColumn('{{conditions}}','method',"string(5)",false , '');
            alterColumn('{{participants}}','owner_uid','integer',false);
            alterColumn('{{participant_attribute_names}}','visible','string(5)',false);
            alterColumn('{{questions}}','type',"string(1)",false , 'T');
            alterColumn('{{questions}}','other',"string(1)",false , 'N');
            alterColumn('{{questions}}','mandatory',"string(1)");
            alterColumn('{{questions}}','scale_id','integer',false , '0');
            alterColumn('{{questions}}','parent_qid','integer',false ,'0');

            alterColumn('{{questions}}','same_default','integer',false , '0');
            alterColumn('{{quota}}','qlimit','integer');
            alterColumn('{{quota}}','action','integer');
            alterColumn('{{quota}}','active','integer',false , '1');
            alterColumn('{{quota}}','autoload_url','integer',false , '0');
            alterColumn('{{saved_control}}','status',"string(1)",false , '');
            try { setTransactionBookmark(); alterColumn('{{sessions}}','id',"string(32)",false); } catch(Exception $e) { rollBackToTransactionBookmark();}
            alterColumn('{{surveys}}','active',"string(1)",false , 'N');
            alterColumn('{{surveys}}','anonymized',"string(1)",false,'N');
            alterColumn('{{surveys}}','format',"string(1)");
            alterColumn('{{surveys}}','savetimings',"string(1)",false , 'N');
            alterColumn('{{surveys}}','datestamp',"string(1)",false , 'N');
            alterColumn('{{surveys}}','usecookie',"string(1)",false , 'N');
            alterColumn('{{surveys}}','allowregister',"string(1)",false , 'N');
            alterColumn('{{surveys}}','allowsave',"string(1)",false , 'Y');
            alterColumn('{{surveys}}','autonumber_start','integer' ,false, '0');
            alterColumn('{{surveys}}','autoredirect',"string(1)",false , 'N');
            alterColumn('{{surveys}}','allowprev',"string(1)",false , 'N');
            alterColumn('{{surveys}}','printanswers',"string(1)",false , 'N');
            alterColumn('{{surveys}}','ipaddr',"string(1)",false , 'N');
            alterColumn('{{surveys}}','refurl',"string(1)",false , 'N');
            alterColumn('{{surveys}}','publicstatistics',"string(1)",false , 'N');
            alterColumn('{{surveys}}','publicgraphs',"string(1)",false , 'N');
            alterColumn('{{surveys}}','listpublic',"string(1)",false , 'N');
            alterColumn('{{surveys}}','htmlemail',"string(1)",false , 'N');
            alterColumn('{{surveys}}','sendconfirmation',"string(1)",false , 'Y');
            alterColumn('{{surveys}}','tokenanswerspersistence',"string(1)",false , 'N');
            alterColumn('{{surveys}}','assessments',"string(1)",false , 'N');
            alterColumn('{{surveys}}','usecaptcha',"string(1)",false , 'N');
            alterColumn('{{surveys}}','usetokens',"string(1)",false , 'N');
            alterColumn('{{surveys}}','tokenlength','integer',false, '15');
            alterColumn('{{surveys}}','showxquestions',"string(1)", true , 'Y');
            alterColumn('{{surveys}}','showgroupinfo',"string(1) ", true , 'B');
            alterColumn('{{surveys}}','shownoanswer',"string(1) ", true , 'Y');
            alterColumn('{{surveys}}','showqnumcode',"string(1) ", true , 'X');
            alterColumn('{{surveys}}','bouncetime','integer');
            alterColumn('{{surveys}}','showwelcome',"string(1)", true , 'Y');
            alterColumn('{{surveys}}','showprogress',"string(1)", true , 'Y');
            alterColumn('{{surveys}}','allowjumps',"string(1)", true , 'N');
            alterColumn('{{surveys}}','navigationdelay','integer', false , '0');
            alterColumn('{{surveys}}','nokeyboard',"string(1)", true , 'N');
            alterColumn('{{surveys}}','alloweditaftercompletion',"string(1)", true , 'N');
            alterColumn('{{surveys}}','googleanalyticsstyle',"string(1)");

            alterColumn('{{surveys_languagesettings}}','surveyls_dateformat','integer',false , 1);
            try { setTransactionBookmark(); alterColumn('{{survey_permissions}}','sid',"integer",false); } catch(Exception $e) { rollBackToTransactionBookmark();}
            try { setTransactionBookmark(); alterColumn('{{survey_permissions}}','uid',"integer",false); } catch(Exception $e) { rollBackToTransactionBookmark();}
            alterColumn('{{survey_permissions}}','create_p', 'integer',false , '0');
            alterColumn('{{survey_permissions}}','read_p', 'integer',false , '0');
            alterColumn('{{survey_permissions}}','update_p','integer',false , '0');
            alterColumn('{{survey_permissions}}','delete_p' ,'integer',false , '0');
            alterColumn('{{survey_permissions}}','import_p','integer',false , '0');
            alterColumn('{{survey_permissions}}','export_p' ,'integer',false , '0');

            alterColumn('{{survey_url_parameters}}','targetqid' ,'integer');
            alterColumn('{{survey_url_parameters}}','targetsqid' ,'integer');

            alterColumn('{{templates_rights}}','use','integer',false );

            alterColumn('{{users}}','create_survey','integer',false, '0');
            alterColumn('{{users}}','create_user','integer',false, '0');
            alterColumn('{{users}}','participant_panel','integer',false, '0');
            alterColumn('{{users}}','delete_user','integer',false, '0');
            alterColumn('{{users}}','superadmin','integer',false, '0');
            alterColumn('{{users}}','configurator','integer',false, '0');
            alterColumn('{{users}}','manage_template','integer',false, '0');
            alterColumn('{{users}}','manage_label','integer',false, '0');
            alterColumn('{{users}}','dateformat','integer',false, 1);
            alterColumn('{{users}}','participant_panel','integer',false , '0');
            alterColumn('{{users}}','parent_id','integer',false);
            try { setTransactionBookmark(); alterColumn('{{surveys_languagesettings}}','surveyls_survey_id',"integer",false); } catch(Exception $e) { rollBackToTransactionBookmark(); }
            alterColumn('{{user_groups}}','owner_id',"integer",false);
            dropPrimaryKey('user_in_groups');
            alterColumn('{{user_in_groups}}','ugid',"integer",false);
            alterColumn('{{user_in_groups}}','uid',"integer",false);

            // Additional corrections for Postgres
            try{ setTransactionBookmark(); $oDB->createCommand()->createIndex('questions_idx3','{{questions}}','gid');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); $oDB->createCommand()->createIndex('conditions_idx3','{{conditions}}','cqid');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); $oDB->createCommand()->createIndex('questions_idx4','{{questions}}','type');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); $oDB->createCommand()->dropIndex('user_in_groups_idx1','{{user_in_groups}}');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); $oDB->createCommand()->dropIndex('{{user_name_key}}','{{users}}');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); $oDB->createCommand()->createIndex('users_name','{{users}}','users_name',true);} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); addPrimaryKey('user_in_groups', array('ugid','uid'));} catch(Exception $e) { rollBackToTransactionBookmark(); };

            alterColumn('{{participant_attribute}}','value',"string(50)", false);
            try{ setTransactionBookmark(); alterColumn('{{participant_attribute_names}}','attribute_type',"string(4)", false);} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); dropColumn('{{participant_attribute_names_lang}}','id');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); addPrimaryKey('participant_attribute_names_lang',array('attribute_id','lang'));} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); $oDB->createCommand()->renameColumn('{{participant_shares}}','shared_uid','share_uid');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            alterColumn('{{participant_shares}}','date_added',"datetime", false);
            alterColumn('{{participants}}','firstname',"string(40)");
            alterColumn('{{participants}}','lastname',"string(40)");
            alterColumn('{{participants}}','email',"string(80)");
            alterColumn('{{participants}}','language',"string(40)");
            alterColumn('{{quota_languagesettings}}','quotals_name',"string");
            try{ setTransactionBookmark(); alterColumn('{{survey_permissions}}','sid','integer',false); } catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); alterColumn('{{survey_permissions}}','uid','integer',false); } catch(Exception $e) { rollBackToTransactionBookmark(); };
            alterColumn('{{users}}','htmleditormode',"string(7)",true,'default');

            // Sometimes the survey_links table was deleted before this step, if so
            // we recreate it (copied from line 663)
            if (!tableExists('{survey_links}')) {
                $oDB->createCommand()->createTable('{{survey_links}}',array(
                    'participant_id' => 'string(50) NOT NULL',
                    'token_id' => 'integer NOT NULL',
                    'survey_id' => 'integer NOT NULL',
                    'date_created' => 'datetime NOT NULL'
                ));
                addPrimaryKey('survey_links', array('participant_id','token_id','survey_id'));
            }
            alterColumn('{{survey_links}}','date_created',"datetime",true);
            alterColumn('{{saved_control}}','identifier',"text",false);
            alterColumn('{{saved_control}}','email',"string(320)");
            alterColumn('{{surveys}}','adminemail',"string(320)");
            alterColumn('{{surveys}}','bounce_email',"string(320)");
            alterColumn('{{users}}','email',"string(320)");

            try{ setTransactionBookmark(); $oDB->createCommand()->dropIndex('assessments_idx','{{assessments}}');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); $oDB->createCommand()->createIndex('assessments_idx3','{{assessments}}','gid');} catch(Exception $e) { rollBackToTransactionBookmark(); };

            try{ setTransactionBookmark(); $oDB->createCommand()->dropIndex('ixcode','{{labels}}');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); $oDB->createCommand()->dropIndex('{{labels_ixcode_idx}}','{{labels}}');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); $oDB->createCommand()->createIndex('labels_code_idx','{{labels}}','code');} catch(Exception $e) { rollBackToTransactionBookmark(); };



            if (Yii::app()->db->driverName=='pgsql')
            {
                try{ setTransactionBookmark(); $oDB->createCommand("ALTER TABLE ONLY {{user_groups}} ADD PRIMARY KEY (ugid); ")->execute;} catch(Exception $e) { rollBackToTransactionBookmark(); };
                try{ setTransactionBookmark(); $oDB->createCommand("ALTER TABLE ONLY {{users}} ADD PRIMARY KEY (uid); ")->execute;} catch(Exception $e) { rollBackToTransactionBookmark(); };
            }

            // Additional corrections for MSSQL
            alterColumn('{{answers}}','answer',"text",false);
            alterColumn('{{assessments}}','name',"text",false);
            alterColumn('{{assessments}}','message',"text",false);
            alterColumn('{{defaultvalues}}','defaultvalue',"text");
            alterColumn('{{expression_errors}}','eqn',"text");
            alterColumn('{{expression_errors}}','prettyprint',"text");
            alterColumn('{{groups}}','description',"text");
            alterColumn('{{groups}}','grelevance',"text");
            alterColumn('{{labels}}','title',"text");
            alterColumn('{{question_attributes}}','value',"text");
            alterColumn('{{questions}}','preg',"text");
            alterColumn('{{questions}}','help',"text");
            alterColumn('{{questions}}','relevance',"text");
            alterColumn('{{questions}}','question',"text",false);
            alterColumn('{{quota_languagesettings}}','quotals_quota_id',"integer",false);
            alterColumn('{{quota_languagesettings}}','quotals_message',"text",false);
            alterColumn('{{saved_control}}','refurl',"text");
            alterColumn('{{saved_control}}','access_code',"text",false);
            alterColumn('{{saved_control}}','ip',"text",false);
            alterColumn('{{saved_control}}','saved_thisstep',"text",false);
            alterColumn('{{saved_control}}','saved_date',"datetime",false);
            alterColumn('{{surveys}}','attributedescriptions',"text");
            alterColumn('{{surveys}}','emailresponseto',"text");
            alterColumn('{{surveys}}','emailnotificationto',"text");

            alterColumn('{{surveys_languagesettings}}','surveyls_description',"text");
            alterColumn('{{surveys_languagesettings}}','surveyls_welcometext',"text");
            alterColumn('{{surveys_languagesettings}}','surveyls_email_invite',"text");
            alterColumn('{{surveys_languagesettings}}','surveyls_email_remind',"text");
            alterColumn('{{surveys_languagesettings}}','surveyls_email_register',"text");
            alterColumn('{{surveys_languagesettings}}','surveyls_email_confirm',"text");
            alterColumn('{{surveys_languagesettings}}','surveyls_attributecaptions',"text");
            alterColumn('{{surveys_languagesettings}}','email_admin_notification',"text");
            alterColumn('{{surveys_languagesettings}}','email_admin_responses',"text");
            alterColumn('{{surveys_languagesettings}}','surveyls_endtext',"text");
            alterColumn('{{user_groups}}','description',"text",false);



            alterColumn('{{conditions}}','value','string',false,'');
            alterColumn('{{participant_shares}}','can_edit',"string(5)",false);

             alterColumn('{{users}}','password',"binary",false);
            dropColumn('{{users}}','one_time_pw');
            addColumn('{{users}}','one_time_pw','binary');


            $oDB->createCommand()->update('{{question_attributes}}',array('value'=>'1'),"attribute = 'random_order' and value = '2'");

            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>157),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 158)
        {
            $oTransaction = $oDB->beginTransaction();
            LimeExpressionManager::UpgradeConditionsToRelevance();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>158),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 159)
        {
            $oTransaction = $oDB->beginTransaction();
            alterColumn('{{failed_login_attempts}}', 'ip', "string(40)",false);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>159),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 160)
        {
            $oTransaction = $oDB->beginTransaction();
            alterLanguageCode('it','it-informal');
            alterLanguageCode('it-formal','it');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>160),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 161)
        {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{survey_links}}','date_invited','datetime NULL default NULL');
            addColumn('{{survey_links}}','date_completed','datetime NULL default NULL');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>161),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 162)
        {
            $oTransaction = $oDB->beginTransaction();
            // Fix participant db types
            alterColumn('{{participant_attribute}}', 'value', "text", false);
            alterColumn('{{participant_attribute_names_lang}}', 'attribute_name', "string(255)", false);
            alterColumn('{{participant_attribute_values}}', 'value', "text", false);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>162),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 163) {
            // Removed because it was obsolete template changes
        }

        if ($iOldDBVersion < 164)
        {
            $oTransaction = $oDB->beginTransaction();
            upgradeTokens148(); // this should have bee done in 148 - that's why it is named this way
            // fix survey tables for missing or incorrect token field
            upgradeSurveyTables164();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>164),"stg_name='DBVersion'");
            $oTransaction->commit();

            // Not updating settings table as upgrade process takes care of that step now
        }

        if ($iOldDBVersion < 165)
        {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->createTable('{{plugins}}', array(
                'id' => 'pk',
                'name' => 'string NOT NULL',
                'active' => 'boolean'
            ));
            $oDB->createCommand()->createTable('{{plugin_settings}}', array(
                'id' => 'pk',
                'plugin_id' => 'integer NOT NULL',
                'model' => 'string',
                'model_id' => 'integer',
                'key' => 'string',
                'value' => 'text'
            ));
            alterColumn('{{surveys_languagesettings}}','surveyls_url',"text");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>165),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 166)
        {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->renameTable('{{survey_permissions}}', '{{permissions}}');
            dropPrimaryKey('permissions');
            alterColumn('{{permissions}}', 'permission', "string(100)", false);
            $oDB->createCommand()->renameColumn('{{permissions}}','sid','entity_id');
            alterColumn('{{permissions}}', 'entity_id', "string(100)", false);
            addColumn('{{permissions}}','entity',"string(50)");
            $oDB->createCommand("update {{permissions}} set entity='survey'")->query();
            addColumn('{{permissions}}','id','pk');
            try { setTransactionBookmark(); $oDB->createCommand()->createIndex('idxPermissions','{{permissions}}','entity_id,entity,permission,uid',true); } catch(Exception $e) { rollBackToTransactionBookmark();}
            upgradePermissions166();
            dropColumn('{{users}}','create_survey');
            dropColumn('{{users}}','create_user');
            dropColumn('{{users}}','delete_user');
            dropColumn('{{users}}','superadmin');
            dropColumn('{{users}}','configurator');
            dropColumn('{{users}}','manage_template');
            dropColumn('{{users}}','manage_label');
            dropColumn('{{users}}','participant_panel');
            $oDB->createCommand()->dropTable('{{templates_rights}}');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>166),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 167)
        {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{surveys_languagesettings}}', 'attachments', 'text');
            addColumn('{{users}}', 'created', 'datetime');
            addColumn('{{users}}', 'modified', 'datetime');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>167),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 168)
        {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{participants}}', 'created', 'datetime');
            addColumn('{{participants}}', 'modified', 'datetime');
            addColumn('{{participants}}', 'created_by', 'integer');
            $oDB->createCommand('update {{participants}} set created_by=owner_uid')->query();
            alterColumn('{{participants}}', 'created_by', "integer", false);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>168),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 169)
        {
            $oTransaction = $oDB->beginTransaction();
            // Add new column for question index options.
            addColumn('{{surveys}}', 'questionindex', 'integer not null default 0');
            // Set values for existing surveys.
            $oDB->createCommand("update {{surveys}} set questionindex = 0 where allowjumps <> 'Y'")->query();
            $oDB->createCommand("update {{surveys}} set questionindex = 1 where allowjumps = 'Y'")->query();

            // Remove old column.
            dropColumn('{{surveys}}', 'allowjumps');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>169),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 170)
        {
            $oTransaction = $oDB->beginTransaction();
            // renamed advanced attributes fields dropdown_dates_year_min/max
            $oDB->createCommand()->update('{{question_attributes}}',array('attribute'=>'date_min'),"attribute='dropdown_dates_year_min'");
            $oDB->createCommand()->update('{{question_attributes}}',array('attribute'=>'date_max'),"attribute='dropdown_dates_year_max'");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>170),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 171)
        {
            $oTransaction = $oDB->beginTransaction();
            try {
                dropColumn('{{sessions}}','data');
            }
            catch (Exception $e) {

            }
            switch (Yii::app()->db->driverName){
                case 'mysql':
                case 'mysqli':
                    addColumn('{{sessions}}', 'data', 'longbinary');
                    break;
                case 'sqlsrv':
                case 'dblib':
                case 'mssql':
                    addColumn('{{sessions}}', 'data', 'VARBINARY(MAX)');
                    break;
                case 'pgsql':
                    addColumn('{{sessions}}', 'data', 'BYTEA');
                    break;
            }
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>171),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 172)
        {
            $oTransaction = $oDB->beginTransaction();
            switch (Yii::app()->db->driverName){
                case 'pgsql':
                    // Special treatment for Postgres as it is too dumb to convert a string to a number without explicit being told to do so ... seriously?
                    alterColumn('{{permissions}}', 'entity_id', "INTEGER USING (entity_id::integer)", false);
                    break;
                case 'sqlsrv':
                case 'dblib':
                case 'mssql':
                    try{ setTransactionBookmark(); $oDB->createCommand()->dropIndex('permissions_idx2','{{permissions}}');} catch(Exception $e) { rollBackToTransactionBookmark(); };
                    try{ setTransactionBookmark(); $oDB->createCommand()->dropIndex('idxPermissions','{{permissions}}');} catch(Exception $e) { rollBackToTransactionBookmark(); };
                    alterColumn('{{permissions}}', 'entity_id', "INTEGER", false);
                    $oDB->createCommand()->createIndex('permissions_idx2','{{permissions}}','entity_id,entity,permission,uid',true);
                    break;
                default:
                    alterColumn('{{permissions}}', 'entity_id', "INTEGER", false);
            }
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>172),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 173)
        {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{participant_attribute_names}}','defaultname',"string(50) NOT NULL default ''");
            upgradeCPDBAttributeDefaultNames173();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>173),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 174)
        {
            $oTransaction = $oDB->beginTransaction();
            alterColumn('{{participants}}', 'email', "string(254)");
            alterColumn('{{saved_control}}', 'email', "string(254)");
            alterColumn('{{surveys}}', 'adminemail', "string(254)");
            alterColumn('{{surveys}}', 'bounce_email', "string(254)");
            switch (Yii::app()->db->driverName){
                case 'sqlsrv':
                case 'dblib':
                case 'mssql': dropUniqueKeyMSSQL('email','{{users}}');
            }
            alterColumn('{{users}}', 'email', "string(254)");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>174),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 175)
        {
            $oTransaction = $oDB->beginTransaction();
            switch (Yii::app()->db->driverName){
                case 'pgsql':
                    // Special treatment for Postgres as it is too dumb to convert a boolean to a number without explicit being told to do so
                    alterColumn('{{plugins}}', 'active', "INTEGER USING (active::integer)", false);
                    break;
                default:
                    alterColumn('{{plugins}}', 'active', "integer",false,'0');
            }
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>175),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 176)
        {
            $oTransaction = $oDB->beginTransaction();
            upgradeTokens176();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>176),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 177)
        {
            $oTransaction = $oDB->beginTransaction();
            if ( Yii::app()->getConfig('auth_webserver') === true ) {
                // using auth webserver, now activate the plugin with default settings.
                if (!class_exists('Authwebserver', false)) {
                    $plugin = Plugin::model()->findByAttributes(array('name'=>'Authwebserver'));
                    if (!$plugin) {
                        $plugin = new Plugin();
                        $plugin->name = 'Authwebserver';
                        $plugin->active = 1;
                        $plugin->save();
                        $plugin = App()->getPluginManager()->loadPlugin('Authwebserver', $plugin->id);
                        $aPluginSettings = $plugin->getPluginSettings(true);
                        $aDefaultSettings = array();
                        foreach ($aPluginSettings as $key => $settings) {
                            if (is_array($settings) && array_key_exists('current', $settings) ) {
                                $aDefaultSettings[$key] = $settings['current'];
                            }
                        }
                        $plugin->saveSettings($aDefaultSettings);
                    } else {
                        $plugin->active = 1;
                        $plugin->save();
                    }
                }
            }
            upgradeSurveys177();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>177),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 178)
        {
            $oTransaction = $oDB->beginTransaction();
            if (Yii::app()->db->driverName=='mysql' || Yii::app()->db->driverName=='mysqli')
            {
                modifyPrimaryKey('questions', array('qid','language'));
            }
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>178),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 179)
        {
            $oTransaction = $oDB->beginTransaction();
            upgradeSurveys177(); // Needs to be run again to make sure
            upgradeTokenTables179();
            alterColumn('{{participants}}', 'email', "string(254)", false);
            alterColumn('{{participants}}', 'firstname', "string(150)", false);
            alterColumn('{{participants}}', 'lastname', "string(150)", false);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>179),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 180)
        {
            $oTransaction = $oDB->beginTransaction();
            $aUsers = User::model()->findAll();
            $aPerm = array(
                'entity_id' => 0,
                'entity' => 'global',
                'uid' => 0,
                'permission' => 'auth_db',
                'create_p' => 0,
                'read_p' => 1,
                'update_p' => 0,
                'delete_p' => 0,
                'import_p' => 0,
                'export_p' => 0
            );

            foreach ($aUsers as $oUser)
            {
                if (!Permission::model()->hasGlobalPermission('auth_db','read',$oUser->uid))
                {
                    $oPermission = new Permission;
                    foreach ($aPerm as $k => $v)
                    {
                        $oPermission->$k = $v;
                    }
                    $oPermission->uid = $oUser->uid;
                    $oPermission->save();
                }
            }
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>180),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 181)
        {
            $oTransaction = $oDB->beginTransaction();
            upgradeTokenTables181('utf8_bin');
            upgradeSurveyTables181('utf8_bin');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>181),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 183)
        {
            $oTransaction = $oDB->beginTransaction();
            upgradeSurveyTables183();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>183),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 184)
        {
            $oTransaction = $oDB->beginTransaction();
            fixKCFinder184();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>184),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        // LS 2.5 table start at 250
        if ($iOldDBVersion < 250) {
            $oTransaction = $oDB->beginTransaction();
            createBoxes250();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>250), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 251) {
            $oTransaction = $oDB->beginTransaction();
            upgradeBoxesTable251();

            // Update DBVersion
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>251), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 252) {
            $oTransaction = $oDB->beginTransaction();
            Yii::app()->db->createCommand()->addColumn('{{questions}}', 'modulename', 'string');
            // Update DBVersion
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>252), "stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 253) {
            $oTransaction = $oDB->beginTransaction();
            upgradeSurveyTables253();

            // Update DBVersion
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>253), "stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 254) {
            $oTransaction = $oDB->beginTransaction();
            upgradeSurveyTables254();
            // Update DBVersion
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>254), "stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 255) {
            $oTransaction = $oDB->beginTransaction();
            upgradeSurveyTables255();
            // Update DBVersion
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>255), "stg_name='DBVersion'");
            $oTransaction->commit();
        }
        if ($iOldDBVersion < 256) {
            $oTransaction = $oDB->beginTransaction();
            upgradeTokenTables256();
            alterColumn('{{participants}}', 'email', "text", false);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>256), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 257) {
            $oTransaction = $oDB->beginTransaction();
            switch (Yii::app()->db->driverName) {
                case 'pgsql':
                    $sSubstringCommand = 'substr';
                    break;
                default:
                    $sSubstringCommand = 'substring';
            }
            $oDB->createCommand("UPDATE {{templates}} set folder={$sSubstringCommand}(folder,1,50)")->execute();
            try { dropPrimaryKey('templates'); } catch(Exception $e){};
            alterColumn('{{templates}}', 'folder', "string(50)", false);
            addPrimaryKey('templates', 'folder');
            dropPrimaryKey('participant_attribute_names_lang');
            alterColumn('{{participant_attribute_names_lang}}', 'lang', "string(20)", false);
            addPrimaryKey('participant_attribute_names_lang', array('attribute_id', 'lang'));
            //Fixes the collation for the complete DB, tables and columns
            if (Yii::app()->db->driverName == 'mysql') {
                fixMySQLCollations('utf8mb4', 'utf8mb4_unicode_ci');
                // Also apply again fixes from DBVersion 181 again for case sensitive token fields
                upgradeSurveyTables181('utf8mb4_bin');
                upgradeTokenTables181('utf8mb4_bin');
            }
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>257), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Remove adminimageurl from global settings
         */
        if ($iOldDBVersion < 258) {
            $oTransaction = $oDB->beginTransaction();
            Yii::app()->getDb()->createCommand(
                "DELETE FROM {{settings_global}} WHERE stg_name='adminimageurl'"
            )->execute();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>258), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Add table for notifications
         * @since 2016-08-04
         * @author Olle Haerstedt
         */
        if ($iOldDBVersion < 259) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->createTable('{{notifications}}', array(
                'id' => 'pk',
                'entity' => 'string(15) not null',
                'entity_id' => 'integer not null',
                'title' => 'string not null', // varchar(255) in postgres
                'message' => 'text not null',
                'status' => "string(15) not null default 'new' ",
                'importance' => 'integer not null default 1',
                'display_class' => "string(31) default 'default'",
                'created' => 'datetime',
                'first_read' => 'datetime'
            ));
            $oDB->createCommand()->createIndex('{{notif_index}}', '{{notifications}}', 'entity, entity_id, status', false);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>259), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 260) {
            $oTransaction = $oDB->beginTransaction();
            alterColumn('{{participant_attribute_names}}', 'defaultname', "string(255)", false);
            alterColumn('{{participant_attribute_names_lang}}', 'attribute_name', "string(255)", false);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>260), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 261) {
            $oTransaction = $oDB->beginTransaction();
            /*
            * The hash value of a notification is used to calculate uniqueness.
            * @since 2016-08-10
            * @author Olle Haerstedt
            */
            addColumn('{{notifications}}', 'hash', 'string(64)');
            $oDB->createCommand()->createIndex('{{notif_hash_index}}', '{{notifications}}', 'hash', false);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>261), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 262) {
            $oTransaction = $oDB->beginTransaction();
            alterColumn('{{settings_global}}', 'stg_value', "text", false);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>262), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 263) {
            $oTransaction = $oDB->beginTransaction();
            // Dummy version update for hash column in installation SQL.
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>263), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Add seed column in all active survey tables
         * Might take time to execute
         * @since 2016-09-01
         */
        if ($iOldDBVersion < 290) {
            $oTransaction = $oDB->beginTransaction();
            $aTables = dbGetTablesLike("survey\_%");
            $oSchema = Yii::app()->db->schema;
            foreach ($aTables as $sTableName) {
                $oTableSchema = $oSchema->getTable($sTableName);
                // Only update the table if it really is a survey response table - there are other tables that start the same
                if (!in_array('lastpage', $oTableSchema->columnNames)) {
                    continue;
                }
                //If seed already exists, due to whatsoever
                if (in_array('seed', $oTableSchema->columnNames)) {
                    continue;
                }
                // If survey has active table, create seed column
                Yii::app()->db->createCommand()->addColumn($sTableName, 'seed', 'string(31)');

                // RAND is RANDOM in Postgres
                switch (Yii::app()->db->driverName) {
                    case 'pgsql':
                        Yii::app()->db->createCommand("UPDATE {$sTableName} SET seed = ROUND(RANDOM() * 10000000)")->execute();
                        break;
                    default:
                        Yii::app()->db->createCommand("UPDATE {$sTableName} SET seed = ROUND(RAND() * 10000000, 0)")->execute();
                        break;
                }
            }
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>290), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Plugin JSON config file
         * @since 2016-08-22
         */
        if ($iOldDBVersion < 291) {
            $oTransaction = $oDB->beginTransaction();

            addColumn('{{plugins}}', 'version', 'string(32)');

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>291), "stg_name='DBVersion'");
            $oTransaction->commit();
        }


        /**
         * Survey menue table
         * @since 2017-07-03
         */
        if ($iOldDBVersion < 293) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>293), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Survey menue table update
         * @since 2017-07-03
         */
        if ($iOldDBVersion < 294) {
            $oTransaction = $oDB->beginTransaction();


            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>294), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Survey menue table update
         * @since 2017-07-12
         */
        if ($iOldDBVersion < 296) {
            $oTransaction = $oDB->beginTransaction();


            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>296), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Template tables
         * @since 2017-07-12
         */
        if ($iOldDBVersion < 298) {
            $oTransaction = $oDB->beginTransaction();
            upgradeTemplateTables298($oDB);
            $oTransaction->commit();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>298), "stg_name='DBVersion'");
        }

        /**
         * Template tables
         * @since 2017-07-12
         */
        if ($iOldDBVersion < 304) {
            $oTransaction = $oDB->beginTransaction();
            upgradeTemplateTables304($oDB);
            $oTransaction->commit();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>304), "stg_name='DBVersion'");
        }

        /**
         * Update to sidemenu rendering
         */
        if ($iOldDBVersion < 305) {
            $oTransaction = $oDB->beginTransaction();
            $oTransaction->commit();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>305), "stg_name='DBVersion'");
        }

        /**
         * Template tables
         * @since 2017-07-12
         */
        if ($iOldDBVersion < 306) {
            $oTransaction = $oDB->beginTransaction();
            createSurveyGroupTables306($oDB);
            $oTransaction->commit();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>306), "stg_name='DBVersion'");
        }

        /**
         * User settings table
         * @since 2016-08-29
         */
        if ($iOldDBVersion < 307) {
            $oTransaction = $oDB->beginTransaction();
            if (tableExists('{settings_user}')) {
                $oDB->createCommand()->dropTable('{{settings_user}}');
            }
            $oDB->createCommand()->createTable('{{settings_user}}', array(
                'id' => 'pk',
                'uid' => 'integer NOT NULL',
                'entity' => 'string(15)',
                'entity_id' => 'string(31)',
                'stg_name' => 'string(63) not null',
                'stg_value' => 'text',

            ));
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>307), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /*
        * Change dbfieldnames to be more functional
        */
        if ($iOldDBVersion < 308) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>308), "stg_name='DBVersion'");
            $oTransaction->commit();
        }
        /*
        * Add survey template editing to menu
        */
        if ($iOldDBVersion < 309) {
            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>309), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /*
        * Reset all surveymenu tables, because there were too many errors
        */
        if ($iOldDBVersion < 310) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>310), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /*
        * Add template settings to survey groups
        */
        if ($iOldDBVersion < 311) {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{surveys_groups}}', 'template', "string(128) DEFAULT 'default'");
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>311), "stg_name='DBVersion'");
            $oTransaction->commit();
        }


        /*
        * Add ltr/rtl capability to template configuration
        */
        if ($iOldDBVersion < 312) {
            $oTransaction = $oDB->beginTransaction();
            // Already added in beta 2 but with wrong type
            try { setTransactionBookmark(); $oDB->createCommand()->dropColumn('{{template_configuration}}', 'packages_ltr'); } catch (Exception $e) { rollBackToTransactionBookmark(); }
            try { setTransactionBookmark(); $oDB->createCommand()->dropColumn('{{template_configuration}}', 'packages_rtl'); } catch (Exception $e) { rollBackToTransactionBookmark(); }

            addColumn('{{template_configuration}}', 'packages_ltr', "text");
            addColumn('{{template_configuration}}', 'packages_rtl', "text");
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>312), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /*
        * Add ltr/rtl capability to template configuration
        */
        if ($iOldDBVersion < 313) {
            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>313), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /*
        * Add ltr/rtl capability to template configuration
        */
        if ($iOldDBVersion < 314) {
            $oTransaction = $oDB->beginTransaction();


            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>314), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 315) {
            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->update('{{template_configuration}}',
                array('packages_to_load'=>'["pjax"]'),
                "templates_name='default' OR templates_name='material'"
            );

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>315), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 316) {
            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->renameColumn('{{template_configuration}}', 'templates_name', 'template_name');

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>316), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        //Transition of the password field to a TEXT type

        if ($iOldDBVersion < 317) {
            $oTransaction = $oDB->beginTransaction();

            transferPasswordFieldToText($oDB);

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>317), "stg_name='DBVersion'");
            $oTransaction->commit();
        }



        //Rename order to sortorder

        if ($iOldDBVersion < 318) {
            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>318), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        //force panelintegration to a full reload

        if ($iOldDBVersion < 319) {
            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>319), "stg_name='DBVersion'");

            $table = Yii::app()->db->schema->getTable('{{surveys_groups}}');
            if (isset($table->columns['order'])) {
                $oDB->createCommand()->renameColumn('{{surveys_groups}}', 'order', 'sortorder');
            }

            $table = Yii::app()->db->schema->getTable('{{templates}}');
            if (isset($table->columns['extends_template_name'])) {
                $oDB->createCommand()->renameColumn('{{templates}}', 'extends_template_name', 'extends');
            }

            $oTransaction->commit();
        }

        if ($iOldDBVersion < 320) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>320), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 321) {
            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>321), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 322) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->createTable(
                '{{tutorials}}', [
                    'tid' =>  'pk',
                    'name' =>  'string(128)',
                    'description' =>  'text',
                    'active' =>  'integer DEFAULT 0',
                    'settings' => 'text',
                    'permission' =>  'string(128) NOT NULL',
                    'permission_grade' =>  'string(128) NOT NULL'
                ]
            );
            $oDB->createCommand()->createTable(
                '{{tutorial_entries}}', [
                    'teid' =>  'pk',
                    'tid' =>  'integer NOT NULL',
                    'title' =>  'text',
                    'content' =>  'text',
                    'settings' => 'text'
                ]
            );
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>322), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 323) {
            $oTransaction = $oDB->beginTransaction();
            dropPrimaryKey('labels', 'lid');
            $oDB->createCommand()->addColumn('{{labels}}', 'id', 'pk');
            $oDB->createCommand()->createIndex('{{idx4_labels}}', '{{labels}}', ['lid', 'sortorder', 'language'], false);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>323), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 324) {
            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>324), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 325) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->dropTable('{{templates}}');
            $oDB->createCommand()->dropTable('{{template_configuration}}');

            // templates
            $oDB->createCommand()->createTable('{{templates}}', array(
                'id' =>  "pk",
                'name' =>  "string(150) NOT NULL",
                'folder' =>  "string(45) NULL",
                'title' =>  "string(100) NOT NULL",
                'creation_date' =>  "datetime NULL",
                'author' =>  "string(150) NULL",
                'author_email' =>  "string(255) NULL",
                'author_url' =>  "string(255) NULL",
                'copyright' =>  "text ",
                'license' =>  "text ",
                'version' =>  "string(45) NULL",
                'api_version' =>  "string(45) NOT NULL",
                'view_folder' =>  "string(45) NOT NULL",
                'files_folder' =>  "string(45) NOT NULL",
                'description' =>  "text ",
                'last_update' =>  "datetime NULL",
                'owner_id' =>  "integer NULL",
                'extends' =>  "string(150)  NULL",
            ));

            $oDB->createCommand()->createIndex('{{idx1_templates}}', '{{templates}}', 'name', false);
            $oDB->createCommand()->createIndex('{{idx2_templates}}', '{{templates}}', 'title', false);
            $oDB->createCommand()->createIndex('{{idx3_templates}}', '{{templates}}', 'owner_id', false);
            $oDB->createCommand()->createIndex('{{idx4_templates}}', '{{templates}}', 'extends', false);

            $headerArray = ['name', 'folder', 'title', 'creation_date', 'author', 'author_email', 'author_url', 'copyright', 'license', 'version', 'api_version', 'view_folder', 'files_folder', 'description', 'last_update', 'owner_id', 'extends'];
            $oDB->createCommand()->insert("{{templates}}", array_combine($headerArray, ['default', 'default', 'Advanced Template', date('Y-m-d H:i:s'), 'Louis Gac', 'louis.gac@limesurvey.org', 'https://www.limesurvey.org/', 'Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.', 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.', '1.0', '3.0', 'views', 'files', "<strong>LimeSurvey Advanced Template</strong><br>A template with custom options to show what it's possible to do with the new engines. Each template provider will be able to offer its own option page (loaded from template)", null, 1, '']));

            $oDB->createCommand()->insert("{{templates}}", array_combine($headerArray, ['material', 'material', 'Material Template', date('Y-m-d H:i:s'), 'Louis Gac', 'louis.gac@limesurvey.org', 'https://www.limesurvey.org/', 'Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.', 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.', '1.0', '3.0', 'views', 'files', '<strong>LimeSurvey Advanced Template</strong><br> A template extending default, to show the inheritance concept. Notice the options, differents from Default.<br><small>uses FezVrasta\'s Material design theme for Bootstrap 3</small>', null, 1, 'default']));

            $oDB->createCommand()->insert("{{templates}}", array_combine($headerArray, ['monochrome', 'monochrome', 'Monochrome Templates', date('Y-m-d H:i:s'), 'Louis Gac', 'louis.gac@limesurvey.org', 'https://www.limesurvey.org/', 'Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.', 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.', '1.0', '3.0', 'views', 'files', '<strong>LimeSurvey Monochrome Templates</strong><br>A template with monochrome colors for easy customization.', null, 1, '']));


            // template_configuration
            $oDB->createCommand()->createTable('{{template_configuration}}', array(
                'id' => "pk",
                'template_name' => "string(150)  NOT NULL",
                'sid' => "integer NULL",
                'gsid' => "integer NULL",
                'uid' => "integer NULL",
                'files_css' => "text",
                'files_js' => "text",
                'files_print_css' => "text",
                'options' => "text ",
                'cssframework_name' => "string(45) NULL",
                'cssframework_css' => "text",
                'cssframework_js' => "text",
                'packages_to_load' => "text",
                'packages_ltr' => "text",
                'packages_rtl' => "text",
            ));

            $oDB->createCommand()->createIndex('{{idx1_template_configuration}}', '{{template_configuration}}', 'template_name', false);
            $oDB->createCommand()->createIndex('{{idx2_template_configuration}}', '{{template_configuration}}', 'sid', false);
            $oDB->createCommand()->createIndex('{{idx3_template_configuration}}', '{{template_configuration}}', 'gsid', false);
            $oDB->createCommand()->createIndex('{{idx4_template_configuration}}', '{{template_configuration}}', 'uid', false);

            $headerArray = ['template_name', 'sid', 'gsid', 'uid', 'files_css', 'files_js', 'files_print_css', 'options', 'cssframework_name', 'cssframework_css', 'cssframework_js', 'packages_to_load', 'packages_ltr', 'packages_rtl'];
            $oDB->createCommand()->insert("{{template_configuration}}", array_combine($headerArray, ['default', null, null, null, '{"add": ["css/animate.css","css/template.css"]}', '{"add": ["scripts/template.js", "scripts/ajaxify.js"]}', '{"add":"css/print_template.css"}', '{"ajaxmode":"off","brandlogo":"on", "brandlogofile": "./files/logo.png", "boxcontainer":"on", "backgroundimage":"off","animatebody":"off","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}', 'bootstrap', '{"replace": [["css/bootstrap.css","css/flatly.css"]]}', '', '["pjax"]', '', '']));

            $oDB->createCommand()->insert("{{template_configuration}}", array_combine($headerArray, ['material', null, null, null, '{"add": ["css/bootstrap-material-design.css", "css/ripples.min.css", "css/template.css"]}', '{"add": ["scripts/template.js", "scripts/material.js", "scripts/ripples.min.js", "scripts/ajaxify.js"]}', '{"add":"css/print_template.css"}', '{"ajaxmode":"off","brandlogo":"on", "brandlogofile": "./files/logo.png", "animatebody":"off","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}', 'bootstrap', '{"replace": [["css/bootstrap.css","css/bootstrap.css"]]}', '', '["pjax"]', '', '']));

            $oDB->createCommand()->insert("{{template_configuration}}", array_combine($headerArray, ['monochrome', null, null, null, '{"add":["css/animate.css","css/ajaxify.css","css/sea_green.css", "css/template.css"]}', '{"add":["scripts/template.js","scripts/ajaxify.js"]}', '{"add":"css/print_template.css"}', '{"ajaxmode":"off","brandlogo":"on","brandlogofile":".\/files\/logo.png","boxcontainer":"on","backgroundimage":"off","animatebody":"off","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}', 'bootstrap', '{}', '', '["pjax"]', '', '']));

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>325), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 326) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->alterColumn('{{surveys}}', 'datecreated', 'datetime');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>326), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 327) {
            $oTransaction = $oDB->beginTransaction();
            upgrade327($oDB);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>327), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 328) {
            $oTransaction = $oDB->beginTransaction();
            upgrade328($oDB);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>328), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 329) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>329), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 330) {
            $oTransaction = $oDB->beginTransaction();
            upgrade330($oDB);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>330), "stg_name='DBVersion'");
            $oTransaction->commit();
        }


        if ($iOldDBVersion < 331) {
            $oTransaction = $oDB->beginTransaction();
            upgrade331($oDB);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>331), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 332) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>332), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 333) {
            $oTransaction = $oDB->beginTransaction();
            upgrade333($oDB);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>333), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 334) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->addColumn('{{tutorials}}', 'title', 'string(192)');
            $oDB->createCommand()->addColumn('{{tutorials}}', 'icon', 'string(64)');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>334), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 335) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>335), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 336) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>336), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 337) {
            $oTransaction = $oDB->beginTransaction();
            resetTutorials337($oDB);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>337), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 338) {
            $oTransaction = $oDB->beginTransaction();
            $rowToRemove = $oDB->createCommand()->select("position, id")->from("{{boxes}}")->where('ico=:ico', [':ico' => 'templates'])->queryRow();
            $position = 6;
            if ($rowToRemove !== false) {
                $oDB->createCommand()->delete("{{boxes}}", 'id=:id', [':id' => $rowToRemove['id']]);
                $position = $rowToRemove['position'];
            }
            $oDB->createCommand()->insert(
                "{{boxes}}",
                [
                    'position' => $position,
                    'url' => 'admin/themeoptions',
                    'title' => 'Themes',
                    'ico' => 'templates',
                    'desc' => 'Themes',
                    'page' => 'welcome',
                    'usergroup' => '-2'
                ]
            );

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>338), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 339) {
            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>339), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Rename 'First start tour' to 'Take beginner tour'.
         */
        If ($iOldDBVersion < 340) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>340), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Recreate basic tour again from DefaultDataSet
         */
        If ($iOldDBVersion < 341) {
            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->truncateTable('{{tutorials}}');
            $oDB->createCommand()->truncateTable('{{tutorial_entries}}');
            $oDB->createCommand()->truncateTable('{{tutorial_entry_relation}}');


            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>341), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Url parameter "surveyid" should be "sid" for this link.
         */
        If ($iOldDBVersion < 342) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>342), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Column assessment_value not null but default to 0.
         */
        if ($iOldDBVersion < 343) {
            $oTransaction = $oDB->beginTransaction();
            alterColumn('{{answers}}', 'assessment_value', 'integer', false, '0');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>343), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Fix missing database values for templates after updating
         * from 2.7x.
         */
        if ($iOldDBVersion < 344) {
            $oTransaction = $oDB->beginTransaction();

            // All templates should inherit from vanilla as default (if extends is empty).
            $oDB->createCommand()->update(
                '{{templates}}',
                [
                    'extends' => 'vanilla',
                ],
                "extends = '' AND name != 'vanilla'"
            );

            // If vanilla template is missing, install it.
            $vanilla = $oDB
                ->createCommand()
                ->select('*')
                ->from('{{templates}}')
                ->where('name=:name', ['name'=>'vanilla'])
                ->queryRow();
            if (empty($vanilla)) {
                $vanillaData = [
                    'name'          => 'vanilla',
                    'folder'        => 'vanilla',
                    'title'         => 'Vanilla Theme',
                    'creation_date' => date('Y-m-d H:i:s'),
                    'author'        =>'Louis Gac',
                    'author_email'  => 'louis.gac@limesurvey.org',
                    'author_url'    => 'https://www.limesurvey.org/',
                    'copyright'     => 'Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.',
                    'license'       => 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.',
                    'version'       => '3.0',
                    'api_version'   => '3.0',
                    'view_folder'   => 'views',
                    'files_folder'  => 'files',
                    'description'   => '<strong>LimeSurvey Bootstrap Vanilla Survey Theme</strong><br>A clean and simple base that can be used by developers to create their own Bootstrap based theme.',
                    'last_update'   => null,
                    'owner_id'      => 1,
                    'extends'       => '',
                ];
                $oDB->createCommand()->insert('{{templates}}', $vanillaData);
            }
            $vanillaConf = $oDB
                ->createCommand()
                ->select('*')
                ->from('{{template_configuration}}')
                ->where('template_name=:template_name', ['template_name'=>'vanilla'])
                ->queryRow();
            if (empty($vanillaConf)) {
                $vanillaConfData = [
                    'template_name'     =>  'vanilla',
                    'sid'               =>  null,
                    'gsid'              =>  null,
                    'uid'               =>  null,
                    'files_css'         => '{"add":["css/ajaxify.css","css/theme.css","css/custom.css"]}',
                    'files_js'          =>  '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
                    'files_print_css'   => '{"add":["css/print_theme.css"]}',
                    'options'           => '{"ajaxmode":"off","brandlogo":"on","container":"on","brandlogofile":"./files/logo.png","font":"noto"}',
                    'cssframework_name' => 'bootstrap',
                    'cssframework_css'  => '{}',
                    'cssframework_js'   => '',
                    'packages_to_load'  => '{"add":["pjax","font-noto"]}',
                    'packages_ltr'      => null,
                    'packages_rtl'      => null
                ];
                $oDB->createCommand()->insert('{{template_configuration}}', $vanillaConfData);
            }

            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>344], "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Fruit template configuration might be faulty when updating
         * from 2.7x, as well as bootswatch.
         */
        if ($iOldDBVersion < 345) {
            $oTransaction = $oDB->beginTransaction();
            $fruityConf = $oDB
                ->createCommand()
                ->select('*')
                ->from('{{template_configuration}}')
                ->where('template_name=:template_name', ['template_name'=>'fruity'])
                ->queryRow();
            if ($fruityConf) {
                // Brute force way. Just have to hope noone changed the default
                // config yet.
                $oDB->createCommand()->update(
                    '{{template_configuration}}',
                    [
                        'files_css'         => '{"add":["css/ajaxify.css","css/animate.css","css/variations/sea_green.css","css/theme.css","css/custom.css"]}',
                        'files_js'          => '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
                        'files_print_css'   => '{"add":["css/print_theme.css"]}',
                        'options'           => '{"ajaxmode":"off","brandlogo":"on","brandlogofile":"./files/logo.png","container":"on","backgroundimage":"off","backgroundimagefile":"./files/pattern.png","animatebody":"off","bodyanimation":"fadeInRight","bodyanimationduration":"1.0","animatequestion":"off","questionanimation":"flipInX","questionanimationduration":"1.0","animatealert":"off","alertanimation":"shake","alertanimationduration":"1.0","font":"noto","bodybackgroundcolor":"#ffffff","fontcolor":"#444444","questionbackgroundcolor":"#ffffff","questionborder":"on","questioncontainershadow":"on","checkicon":"f00c","animatecheckbox":"on","checkboxanimation":"rubberBand","checkboxanimationduration":"0.5","animateradio":"on","radioanimation":"zoomIn","radioanimationduration":"0.3","showpopups":"1"}',
                        'cssframework_name' => 'bootstrap',
                        'cssframework_css'  => '{}',
                        'cssframework_js'   => '',
                        'packages_to_load'  => '{"add":["pjax","font-noto","moment"]}',
                    ],
                    "template_name = 'fruity'"
                );
            } else {
                $fruityConfData = [
                    'template_name'     =>  'fruity',
                    'sid'               =>  null,
                    'gsid'              =>  null,
                    'uid'               =>  null,
                    'files_css'         => '{"add":["css/ajaxify.css","css/animate.css","css/variations/sea_green.css","css/theme.css","css/custom.css"]}',
                    'files_js'          => '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
                    'files_print_css'   => '{"add":["css/print_theme.css"]}',
                    'options'           => '{"ajaxmode":"off","brandlogo":"on","brandlogofile":"./files/logo.png","container":"on","backgroundimage":"off","backgroundimagefile":"./files/pattern.png","animatebody":"off","bodyanimation":"fadeInRight","bodyanimationduration":"1.0","animatequestion":"off","questionanimation":"flipInX","questionanimationduration":"1.0","animatealert":"off","alertanimation":"shake","alertanimationduration":"1.0","font":"noto","bodybackgroundcolor":"#ffffff","fontcolor":"#444444","questionbackgroundcolor":"#ffffff","questionborder":"on","questioncontainershadow":"on","checkicon":"f00c","animatecheckbox":"on","checkboxanimation":"rubberBand","checkboxanimationduration":"0.5","animateradio":"on","radioanimation":"zoomIn","radioanimationduration":"0.3","showpopups":"1"}',
                    'cssframework_name' => 'bootstrap',
                    'cssframework_css'  => '{}',
                    'cssframework_js'   => '',
                    'packages_to_load'  => '{"add":["pjax","font-noto","moment"]}',
                    'packages_ltr'      => null,
                    'packages_rtl'      => null
                ];
                $oDB->createCommand()->insert('{{template_configuration}}', $fruityConfData);
            }
            $bootswatchConf = $oDB
                ->createCommand()
                ->select('*')
                ->from('{{template_configuration}}')
                ->where('template_name=:template_name', ['template_name'=>'bootswatch'])
                ->queryRow();
            if ($bootswatchConf) {
                $oDB->createCommand()->update(
                    '{{template_configuration}}',
                    [
                        'files_css'         => '{"add":["css/ajaxify.css","css/theme.css","css/custom.css"]}',
                        'files_js'          =>  '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
                        'files_print_css'   => '{"add":["css/print_theme.css"]}',
                        'options'           => '{"ajaxmode":"off","brandlogo":"on","container":"on","brandlogofile":"./files/logo.png"}',
                        'cssframework_name' => 'bootstrap',
                        'cssframework_css'  => '{"replace":[["css/bootstrap.css","css/variations/flatly.min.css"]]}',
                        'cssframework_js'   => '',
                        'packages_to_load'  => '{"add":["pjax","font-noto"]}',
                    ],
                    "template_name = 'bootswatch'"
                );
            } else {
                $bootswatchConfData = [
                    'template_name'     =>  'bootswatch',
                    'sid'               =>  null,
                    'gsid'              =>  null,
                    'uid'               =>  null,
                    'files_css'         => '{"add":["css/ajaxify.css","css/theme.css","css/custom.css"]}',
                    'files_js'          =>  '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
                    'files_print_css'   => '{"add":["css/print_theme.css"]}',
                    'options'           => '{"ajaxmode":"off","brandlogo":"on","container":"on","brandlogofile":"./files/logo.png"}',
                    'cssframework_name' => 'bootstrap',
                    'cssframework_css'  => '{"replace":[["css/bootstrap.css","css/variations/flatly.min.css"]]}',
                    'cssframework_js'   => '',
                    'packages_to_load'  => '{"add":["pjax","font-noto"]}',
                    'packages_ltr'      => null,
                    'packages_rtl'      => null
                ];
                $oDB->createCommand()->insert('{{template_configuration}}', $bootswatchConfData);
            }
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>345], "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        //Reset Surveymenues and tutorials to fix translation issues
        if ($iOldDBVersion < 346) {
            $oTransaction = $oDB->beginTransaction();
            createSurveyMenuTable($oDB);
            $oDB->createCommand()->truncateTable('{{tutorials}}');
            $oDB->createCommand()->truncateTable('{{tutorial_entries}}');
            $oDB->createCommand()->truncateTable('{{tutorial_entry_relation}}');
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>346], "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Correct permission for survey menu email template (surveylocale, not assessments).
         */
        if ($iOldDBVersion < 347) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                [
                    'permission' => 'surveylocale',
                ],
                'name=\'emailtemplates\''
            );
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>347], "stg_name='DBVersion'");
            $oTransaction->commit();
        }
        /**
         * Adding security message and settings
         */
        if ($iOldDBVersion < 348) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->addColumn('{{surveys_languagesettings}}', 'surveyls_policy_notice', 'text');
            $oDB->createCommand()->addColumn('{{surveys_languagesettings}}', 'surveyls_policy_error', 'text');
            $oDB->createCommand()->addColumn('{{surveys_languagesettings}}', 'surveyls_policy_notice_label', 'string(192)');
            $oDB->createCommand()->addColumn('{{surveys}}', 'showsurveypolicynotice', 'integer DEFAULT 0');

            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>348], "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 349) {
            $oTransaction = $oDB->beginTransaction();
            dropColumn('{{users}}','one_time_pw');
            addColumn('{{users}}','one_time_pw','text');
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>349], "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Adding asset version to allow to reset asset without write inside
         */
        if ($iOldDBVersion < 350) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->createTable('{{asset_version}}',array(
                'id' => 'pk',
                'path' => 'text NOT NULL',
                'version' => 'integer NOT NULL',
            ));
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>350], "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Turning on ajax mode at global level for all themes (survey level not affected)
         */
        if ($iOldDBVersion < 351) {
            $oTransaction = $oDB->beginTransaction();

            $aTHemes = TemplateConfiguration::model()->findAll();

            foreach ($aTHemes as $oTheme){
                $oTheme->setGlobalOption("ajaxmode", "on");
            }

            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>351], "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 352) {
            $oTransaction = $oDB->beginTransaction();
            dropColumn('{{sessions}}','data');
            addColumn('{{sessions}}','data','binary');

            $aTHemes = TemplateConfiguration::model()->findAll();

            foreach ($aTHemes as $oTheme){
                $oTheme->setGlobalOption("ajaxmode", "off");
            }

            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>352], "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 353) {
            $oTransaction = $oDB->beginTransaction();

            $aTHemes = TemplateConfiguration::model()->findAll();

            foreach ($aTHemes as $oTheme){
                $oTheme->addOptionFromXMLToLiveTheme();
            }

            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>353], "stg_name='DBVersion'");
            $oTransaction->commit();
        }


        if ($iOldDBVersion < 354) {
            $oTransaction = $oDB->beginTransaction();
            $surveymenuTable = Yii::app()->db->schema->getTable('{{surveymenu}}');

            if (!isset($surveymenuTable->columns['showincollapse'])) {
                $oDB->createCommand()->addColumn('{{surveymenu}}', 'showincollapse', 'integer DEFAULT 0');
            }

            $surveymenuEntryTable = Yii::app()->db->schema->getTable('{{surveymenu}}');
            if (!isset($surveymenuEntryTable->columns['showincollapse'])) {
                $oDB->createCommand()->addColumn('{{surveymenu_entries}}', 'showincollapse', 'integer DEFAULT 0');
            }

            $aIdMap = [];
            $aDefaultSurveyMenus = LsDefaultDataSets::getSurveyMenuData();
            switchMSSQLIdentityInsert('surveymenu', true);
            foreach ($aDefaultSurveyMenus as $i => $aSurveymenu) {
                $oDB->createCommand()->delete('{{surveymenu}}', 'name=:name', [':name' => $aSurveymenu['name']]);
                $oDB->createCommand()->delete('{{surveymenu}}', 'id=:id', [':id' => $aSurveymenu['id']]);
                $oDB->createCommand()->insert('{{surveymenu}}', $aSurveymenu);
                $aIdMap[$aSurveymenu['name']] = $aSurveymenu['id'];
            }
            switchMSSQLIdentityInsert('surveymenu', false);

            $aDefaultSurveyMenuEntries = LsDefaultDataSets::getSurveyMenuEntryData();
            foreach($aDefaultSurveyMenuEntries as $i => $aSurveymenuentry) {
                $oDB->createCommand()->delete('{{surveymenu_entries}}', 'name=:name', [':name' => $aSurveymenuentry['name']]);
                switch($aSurveymenuentry['menu_id']) {
                    case 1: $aSurveymenuentry['menu_id'] = $aIdMap['settings']; break;
                    case 2: $aSurveymenuentry['menu_id'] = $aIdMap['mainmenu']; break;
                    case 3: $aSurveymenuentry['menu_id'] = $aIdMap['quickmenu']; break;
                    case 4: $aSurveymenuentry['menu_id'] = $aIdMap['pluginmenu']; break;
                }
                $oDB->createCommand()->insert('{{surveymenu_entries}}', $aSurveymenuentry);
            }

            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>354], "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 355) {
            $oTransaction = $oDB->beginTransaction();

            $aIdMap = [];
            $aDefaultSurveyMenus = LsDefaultDataSets::getSurveyMenuData();
            foreach($aDefaultSurveyMenus as $i => $aSurveymenu) {
                $aIdMap[$aSurveymenu['name']] = $oDB->createCommand()
                ->select(['id'])
                ->from('{{surveymenu}}')
                ->where('name=:name', [':name' => $aSurveymenu['name']])
                ->queryScalar();
            }

            $aDefaultSurveyMenuEntries = LsDefaultDataSets::getSurveyMenuEntryData();
            foreach($aDefaultSurveyMenuEntries as $i => $aSurveymenuentry) {
                $oDB->createCommand()->delete('{{surveymenu_entries}}', 'name=:name', [':name' => $aSurveymenuentry['name']]);
                switch($aSurveymenuentry['menu_id']) {
                    case 1: $aSurveymenuentry['menu_id'] = $aIdMap['settings']; break;
                    case 2: $aSurveymenuentry['menu_id'] = $aIdMap['mainmenu']; break;
                    case 3: $aSurveymenuentry['menu_id'] = $aIdMap['quickmenu']; break;
                    case 4: $aSurveymenuentry['menu_id'] = $aIdMap['pluginmenu']; break;
                }
                $oDB->createCommand()->insert('{{surveymenu_entries}}', $aSurveymenuentry);
            }


            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>355], "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        // Replace "Label sets" box with "LimeStore" box.
        if ($iOldDBVersion < 356) {
            $oTransaction = $oDB->beginTransaction();
            switch (Yii::app()->db->driverName) {
                case 'sqlsrv':
                case 'dblib':
                case 'mssql':
                    $oDB->createCommand("UPDATE {{boxes}} SET ico = 'icon-' + ico")->execute();
                    break;
                default:
                    $oDB->createCommand("UPDATE {{boxes}} SET ico = CONCAT('icon-', ico)")->execute();
                    break;
            }
            // Only change label box if it's there.
            $labelBox = $oDB->createCommand("SELECT * FROM {{boxes}} WHERE id = 5 AND position = 5 AND title = 'Label sets'")->queryRow();
            if ($labelBox) {
                $oDB
                    ->createCommand()
                    ->update(
                        '{{boxes}}',
                        [
                            'title' => 'LimeStore',
                            'ico'   => 'fa fa-cart-plus',
                            'desc'  => 'LimeSurvey extension marketplace',
                            'url'   => 'https://www.limesurvey.org/limestore'
                        ],
                        'id = 5'
                    );
            }

            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>356], "stg_name='DBVersion'");
            $oTransaction->commit();
        }




        if ($iOldDBVersion < 357) {
            $oTransaction = $oDB->beginTransaction();
            //// IKI
            $oDB->createCommand()->renameColumn('{{surveys_groups}}','owner_uid','owner_id');
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>357], "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 358) {
            $oTransaction = $oDB->beginTransaction();
            dropColumn('{{sessions}}','data');
            addColumn('{{sessions}}','data','longbinary');
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>358], "stg_name='DBVersion'");
            $oTransaction->commit();
        }
        
        if ($iOldDBVersion < 359) {
            $oTransaction = $oDB->beginTransaction();
            alterColumn('{{notifications}}','message',"text",false);
            alterColumn('{{settings_user}}','stg_value',"text",true);
            alterColumn('{{surveys_languagesettings}}','surveyls_description',"text",true);
            alterColumn('{{surveys_languagesettings}}','surveyls_welcometext',"text",true);
            alterColumn('{{surveys_languagesettings}}','surveyls_endtext',"text",true);
            alterColumn('{{surveys_languagesettings}}','surveyls_policy_notice',"text",true);
            alterColumn('{{surveys_languagesettings}}','surveyls_policy_error',"text",true);
            alterColumn('{{surveys_languagesettings}}','surveyls_url',"text",true);
            alterColumn('{{surveys_languagesettings}}','surveyls_email_invite',"text",true);
            alterColumn('{{surveys_languagesettings}}','surveyls_email_remind',"text",true);
            alterColumn('{{surveys_languagesettings}}','surveyls_email_register',"text",true);
            alterColumn('{{surveys_languagesettings}}','surveyls_email_confirm',"text",true);
            alterColumn('{{surveys_languagesettings}}','surveyls_attributecaptions',"text",true);
            alterColumn('{{surveys_languagesettings}}','email_admin_notification',"text",true);
            alterColumn('{{surveys_languagesettings}}','email_admin_responses',"text",true);
            alterColumn('{{surveys_languagesettings}}','surveyls_numberformat',"integer",false,'0');
            alterColumn('{{user_groups}}','description',"text",false);
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>359], "stg_name='DBVersion'");
            $oTransaction->commit();
        }        

    } catch (Exception $e) {
        Yii::app()->setConfig('Updating', false);
        $oTransaction->rollback();
        // Activate schema caching
        $oDB->schemaCachingDuration = 3600;
        // Load all tables of the application in the schema
        $oDB->schema->getTables();
        // clear the cache of all loaded tables
        $oDB->schema->refresh();
        $trace = $e->getTrace();
        $fileInfo = explode('/', $trace[1]['file']);
        $file = end($fileInfo);
        Yii::app()->user->setFlash(
            'error',
            gT('An non-recoverable error happened during the update. Error details:')
            .'<p>'
            .htmlspecialchars($e->getMessage())
            .'</p><br />'
            . sprintf(gT('File %s, line %s.'), $file, $trace[1]['line'])
        );
        // If we're debugging, re-throw the exception.
        if (defined('YII_DEBUG') && YII_DEBUG) {
            throw $e;
        }
        return false;
    }

    // Activate schema cache first - otherwise it won't be refreshed!
    $oDB->schemaCachingDuration = 3600;
    // Load all tables of the application in the schema
    $oDB->schema->getTables();
    // clear the cache of all loaded tables
    $oDB->schema->refresh();
    $oDB->active = false;
    $oDB->active = true;

    // Force User model to refresh meta data (for updates from very old versions)
    User::model()->refreshMetaData();
    Yii::app()->db->schema->getTable('{{surveys}}', true);
    Yii::app()->db->schema->getTable('{{templates}}', true);
    Survey::model()->refreshMetaData();
    Notification::model()->refreshMetaData();

    // Try to clear tmp/runtime (database cache files).
    // Related to problems like https://bugs.limesurvey.org/view.php?id=13699.
    // Some cache implementations may not have 'flush' method. Only call flush if method exists.
    if (method_exists(Yii::app()->cache, 'flush')) {
        Yii::app()->cache->flush();
    }
    // Some cache implementations (ex: CRedisCache, CDummyCache) may not have 'gc' method. Only call gc if method exists.
    if (method_exists(Yii::app()->cache, 'gc')) {
        Yii::app()->cache->gc();
    }

    // Inform  superadmin about update
    $superadmins = User::model()->getSuperAdmins();
    $currentDbVersion = $oDB->createCommand()->select('stg_value')->from('{{settings_global}}')->where("stg_name=:stg_name", array('stg_name'=>'DBVersion'))->queryRow();
    // Update the global config object because it is static and set at start of App
    Yii::app()->setConfig('DBVersion', $currentDbVersion['stg_value']);

    Notification::broadcast(array(
        'title' => gT('Database update'),
        'message' => sprintf(gT('The database has been updated from version %s to version %s.'), $iOldDBVersion, $currentDbVersion['stg_value'])
        ), $superadmins);

    fixLanguageConsistencyAllSurveys();

    Yii::app()->setConfig('Updating', false);
    return true;
}

/**
 * @param CDbConnection $oDB
 *
 * @return void
 */
function resetTutorials337($oDB)
{
    $oDB->createCommand()->truncateTable('{{tutorials}}');
    $oDB->createCommand()->truncateTable('{{tutorial_entries}}');
    $oDB->createCommand()->truncateTable('{{tutorial_entry_relation}}');
}

/**
* @param CDbConnection $oDB
* @return void
*/
function upgrade333($oDB)
{
    $oDB->createCommand()->createTable('{{map_tutorial_users}}', array(
        'tid' => 'integer NOT NULL',
        'uid' => 'integer NOT NULL',
        'taken' => 'integer DEFAULT 1',
    ));

    $oDB->createCommand()->addPrimaryKey('{{map_tutorial_users_pk}}', '{{map_tutorial_users}}', ['uid', 'tid']);

    $oDB->createCommand()->createTable('{{tutorial_entry_relation}}', array(
        'teid' => 'integer NOT NULL',
        'tid' => 'integer NOT NULL',
        'uid' => 'integer DEFAULT NULL',
        'sid' => 'integer DEFAULT NULL',
    ));

    $oDB->createCommand()->addPrimaryKey('{{tutorial_entry_relation_pk}}', '{{tutorial_entry_relation}}', ['teid', 'tid']);
    $oDB->createCommand()->createIndex('{{idx1_tutorial_entry_relation}}', '{{tutorial_entry_relation}}', 'uid', false);
    $oDB->createCommand()->createIndex('{{idx2_tutorial_entry_relation}}', '{{tutorial_entry_relation}}', 'sid', false);
    $oDB->createCommand()->createIndex('{{idx1_tutorials}}', '{{tutorials}}', 'name', true);

    $oDB->createCommand()->dropColumn('{{tutorial_entries}}', 'tid');
    $oDB->createCommand()->addColumn('{{tutorial_entries}}', 'ordering', 'integer');

}

/**
* @param CDbConnection $oDB
* @return void
*/
function upgrade331($oDB)
{
    $oDB->createCommand()->update('{{templates}}', array(
        'name'        => 'bootswatch',
        'folder'      => 'bootswatch',
        'title'       => 'Bootswatch Theme',
        'description' => '<strong>LimeSurvey Bootwatch Theme</strong><br>Based on BootsWatch Themes: <a href=\'https://bootswatch.com/3/\'>Visit BootsWatch page</a>',
    ), "name='default'");

    $oDB->createCommand()->update('{{templates}}', array(
        'extends' => 'bootswatch',
    ), "extends='default'");

    $oDB->createCommand()->update('{{template_configuration}}', array(
            'template_name'   => 'bootswatch',
    ), "template_name='default'");

    $oDB->createCommand()->update('{{templates}}', array(
        'description' => '<strong>LimeSurvey Material Design Theme</strong><br> A theme based on FezVrasta\'s Material design for Bootstrap 3 <a href=\'https://cdn.rawgit.com/FezVrasta/bootstrap-material-design/gh-pages-v3/index.html\'></a>',
    ), "name='material'");

    $oDB->createCommand()->update('{{templates}}', array(
        'name'        => 'fruity',
        'folder'      => 'fruity',
        'title'       => 'Fruity Theme',
        'description' => '<strong>LimeSurvey Fruity Theme</strong><br>Some color themes for a flexible use. This theme offers many options.',
    ), "name='monochrome'");

    $oDB->createCommand()->update('{{templates}}', array(
        'extends' => 'fruity',
    ), "extends='monochrome'");

    $oDB->createCommand()->update('{{template_configuration}}', array(
            'template_name'   => 'fruity',
    ), "template_name='monochrome'");

    $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>'fruity'), "stg_name='defaulttheme'");

}

/**
* @param CDbConnection $oDB
* @return void
*/
function upgrade330($oDB)
{
    $oDB->createCommand()->update('{{template_configuration}}', array(
            'files_css'       => '{"add": ["css/animate.css","css/theme.css"]}',
            'files_js'        => '{"add": ["scripts/theme.js", "scripts/ajaxify.js"]}',
            'files_print_css' => '{"add":"css/print_theme.css"}',
    ), "template_name='default' AND  files_css != 'inherit' ");

    $oDB->createCommand()->update('{{template_configuration}}', array(
            'files_css'       => '{"add": ["css/bootstrap-material-design.css", "css/ripples.min.css", "css/theme.css"]}',
            'files_js'        => '{"add": ["scripts/theme.js", "scripts/material.js", "scripts/ripples.min.js", "scripts/ajaxify.js"]}',
            'files_print_css' => '{"add":"css/print_theme.css"}',
    ), "template_name='material' AND  files_css != 'inherit'");

    $oDB->createCommand()->update('{{template_configuration}}', array(
            'files_css'       => '{"add":["css/animate.css","css/ajaxify.css","css/sea_green.css", "css/theme.css"]}',
            'files_js'        => '{"add":["scripts/theme.js","scripts/ajaxify.js"]}',
            'files_print_css' => '{"add":"css/print_theme.css"}',
    ), "template_name='monochrome' AND  files_css != 'inherit'");

    $oDB->createCommand()->update('{{template_configuration}}', array(
            'files_css'         => '{"add":["css/ajaxify.css","css/theme.css","css/custom.css"]}',
            'files_js'          =>  '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
            'files_print_css'   => '{"add":["css/print_theme.css"]}',
    ), "template_name='vanilla' AND  files_css != 'inherit'");
}

/**
* @param CDbConnection $oDB
* @return void
*/
function upgrade328($oDB)
{
    $oDB->createCommand()->update('{{templates}}', array(
            'description' =>  "<strong>LimeSurvey Advanced Theme</strong><br>A theme with custom options to show what it's possible to do with the new engines. Each theme provider will be able to offer its own option page (loaded from theme)",
    ), "name='default'");
}

/**
* @param CDbConnection $oDB
* @return void
*/
function upgrade327($oDB)
{
    // Update the box value so it uses to the the themeoptions controler
    $oDB->createCommand()->update('{{boxes}}', array(
        'position' =>  '6',
        'url'      =>  'admin/themeoptions',
        'title'    =>  'Themes',
        'ico'      =>  'templates',
        'desc'     =>  'Edit LimeSurvey Themes',
        'page'     =>  'welcome',
        'usergroup' => '-2',
    ), "url='admin/templateoptions'");

}

/**
 * @param CDbConnection $oDB
 */
function transferPasswordFieldToText($oDB)
{
    switch ($oDB->getDriverName()) {
        case 'mysql':
        case 'mysqli':
            $oDB->createCommand()->alterColumn('{{users}}', 'password', 'text NOT NULL');
            break;
        case 'pgsql':

            $userPasswords = $oDB->createCommand()->select(['uid', "encode(password::bytea, 'escape') as password"])->from('{{users}}')->queryAll();

            $oDB->createCommand()->renameColumn('{{users}}', 'password', 'password_blob');
            $oDB->createCommand()->addColumn('{{users}}', 'password', "text NOT NULL DEFAULT 'nopw'");

            foreach ($userPasswords as $userArray) {
                $oDB->createCommand()->update('{{users}}', ['password' => $userArray['password']], 'uid=:uid', [':uid'=> $userArray['uid']]);
            }

            $oDB->createCommand()->dropColumn('{{users}}', 'password_blob');
            break;
        case 'sqlsrv':
        case 'dblib':
        case 'mssql':
        default:
            break;
    }
}

/**
* @param CDbConnection $oDB
* @return void
*/
function createSurveyMenuTable(CDbConnection $oDB)
{
    // NB: Need to refresh here, since surveymenu table is
    // created in earlier version in same script.
    $oDB->schema->getTables();
    $oDB->schema->refresh();

    // Drop the old surveymenu_entries table.
    if (tableExists('{surveymenu_entries}')) {
        $oDB->createCommand()->dropTable('{{surveymenu_entries}}');
    }

    // Drop the old surveymenu table.
    if (tableExists('{surveymenu}')) {
        $oDB->createCommand()->dropTable('{{surveymenu}}');
    }

    $oDB->createCommand()->createTable('{{surveymenu}}', array(
        'id' => "pk",
        'parent_id' => "integer NULL",
        'survey_id' => "integer NULL",
        'user_id' => "integer NULL",
        'name' => "string(128)",
        'ordering' => "integer NULL DEFAULT '0'",
        'level' => "integer NULL DEFAULT '0'",
        'title' => "string(168)  NOT NULL DEFAULT ''",
        'position' => "string(192)  NOT NULL DEFAULT 'side'",
        'description' => "text ",
        'active' => "integer NOT NULL DEFAULT '0'",
        'showincollapse' =>  "integer DEFAULT 0",
        'changed_at' => "datetime",
        'changed_by' => "integer NOT NULL DEFAULT '0'",
        'created_at' => "datetime",
        'created_by' => "integer NOT NULL DEFAULT '0'",
    ));

    $oDB->createCommand()->createIndex('{{surveymenu_name}}', '{{surveymenu}}', 'name', true);
    $oDB->createCommand()->createIndex('{{idx2_surveymenu}}', '{{surveymenu}}', 'title', false);

    $surveyMenuRowData = LsDefaultDataSets::getSurveyMenuData();
    switchMSSQLIdentityInsert('surveymenu', true);
    foreach ($surveyMenuRowData as $surveyMenuRow) {
        $oDB->createCommand()->insert("{{surveymenu}}", $surveyMenuRow);
    }
    switchMSSQLIdentityInsert('surveymenu', false);

    $oDB->createCommand()->createTable('{{surveymenu_entries}}', array(
        'id' =>  "pk",
        'menu_id' =>  "integer NULL",
        'user_id' =>  "integer NULL",
        'ordering' =>  "integer DEFAULT '0'",
        'name' =>  "string(168)  DEFAULT ''",
        'title' =>  "string(168)  NOT NULL DEFAULT ''",
        'menu_title' =>  "string(168)  NOT NULL DEFAULT ''",
        'menu_description' =>  "text ",
        'menu_icon' =>  "string(192)  NOT NULL DEFAULT ''",
        'menu_icon_type' =>  "string(192)  NOT NULL DEFAULT ''",
        'menu_class' =>  "string(192)  NOT NULL DEFAULT ''",
        'menu_link' =>  "string(192)  NOT NULL DEFAULT ''",
        'action' =>  "string(192)  NOT NULL DEFAULT ''",
        'template' =>  "string(192)  NOT NULL DEFAULT ''",
        'partial' =>  "string(192)  NOT NULL DEFAULT ''",
        'classes' =>  "string(192)  NOT NULL DEFAULT ''",
        'permission' =>  "string(192)  NOT NULL DEFAULT ''",
        'permission_grade' =>  "string(192)  NULL",
        'data' =>  "text ",
        'getdatamethod' =>  "string(192)  NOT NULL DEFAULT ''",
        'language' =>  "string(32)  NOT NULL DEFAULT 'en-GB'",
        'active' =>  "integer NOT NULL DEFAULT '0'",
        'showincollapse' =>  "integer DEFAULT 0",
        'changed_at' =>  "datetime NULL",
        'changed_by' =>  "integer NOT NULL DEFAULT '0'",
        'created_at' =>  "datetime NULL",
        'created_by' =>  "integer NOT NULL DEFAULT '0'",
    ));

    $oDB->createCommand()->createIndex('{{idx1_surveymenu_entries}}', '{{surveymenu_entries}}', 'menu_id', false);
    $oDB->createCommand()->createIndex('{{idx5_surveymenu_entries}}', '{{surveymenu_entries}}', 'menu_title', false);
    $oDB->createCommand()->createIndex('{{surveymenu_entries_name}}', '{{surveymenu_entries}}', 'name', true);

    foreach ($surveyMenuEntryRowData = LsDefaultDataSets::getSurveyMenuEntryData() as $surveyMenuEntryRow) {
        $oDB->createCommand()->insert("{{surveymenu_entries}}", $surveyMenuEntryRow);
    }

}
/**
* @param CDbConnection $oDB
* @return void
*/
function createSurveyGroupTables306($oDB)
{
    // Drop the old survey groups table.
    if (tableExists('{surveys_groups}')) {
        $oDB->createCommand()->dropTable('{{surveys_groups}}');
    }


    // Create templates table
    $oDB->createCommand()->createTable('{{surveys_groups}}', array(
        'gsid'        => 'pk',
        'name'        => 'string(45) NOT NULL',
        'title'       => 'string(100) DEFAULT NULL',
        'description' => 'text DEFAULT NULL',
        'sortorder'   => 'integer NOT NULL',
        'owner_uid'   => 'integer DEFAULT NULL',
        'parent_id'   => 'integer DEFAULT NULL',
        'created'     => 'datetime',
        'modified'    => 'datetime',
        'created_by'  => 'integer NOT NULL'
    ));

    // Add default template
    $date = date("Y-m-d H:i:s");
    $oDB->createCommand()->insert('{{surveys_groups}}', array(
        'name'        => 'default',
        'title'       => 'Default Survey Group',
        'description' => 'LimeSurvey core default survey group',
        'sortorder'   => '0',
        'owner_uid'   => '1',
        'created'     => $date,
        'modified'    => $date,
        'created_by'  => '1'
    ));

    $oDB->createCommand()->addColumn('{{surveys}}', 'gsid', "integer DEFAULT 1");


}



/**
* @param CDbConnection $oDB
* @return void
*/
function upgradeTemplateTables304($oDB)
{
    // Drop the old survey rights table.
    if (tableExists('{{templates}}')) {
        $oDB->createCommand()->dropTable('{{templates}}');
    }

    if (tableExists('{{template_configuration}}')) {
        $oDB->createCommand()->dropTable('{{template_configuration}}');
    }

    // Create templates table
    $oDB->createCommand()->createTable('{{templates}}', array(
        'name'                   => 'string(150) NOT NULL',
        'folder'                 => 'string(45) DEFAULT NULL',
        'title'                  => 'string(100) NOT NULL',
        'creation_date'          => 'datetime',
        'author'                 => 'string(150) DEFAULT NULL',
        'author_email'           => 'string DEFAULT NULL',
        'author_url'             => 'string DEFAULT NULL',
        'copyright'              => 'text',
        'license'                => 'text',
        'version'                => 'string(45) DEFAULT NULL',
        'api_version'            => 'string(45) NOT NULL',
        'view_folder'            => 'string(45) NOT NULL',
        'files_folder'           => 'string(45) NOT NULL',
        'description'            => 'text',
        'last_update'            => 'datetime DEFAULT NULL',
        'owner_id'               => 'integer DEFAULT NULL',
        'extends_template_name' => 'string(150) DEFAULT NULL',
        'PRIMARY KEY (name)'
    ));

    // Add default template
    $oDB->createCommand()->insert('{{templates}}', array(
        'name'                   => 'default',
        'folder'                 => 'default',
        'title'                  => 'Advanced Template',
        'creation_date'          => '2017-07-12 12:00:00',
        'author'                 => 'Louis Gac',
        'author_email'           => 'louis.gac@limesurvey.org',
        'author_url'             => 'https://www.limesurvey.org/',
        'copyright'              => 'Copyright (C) 2007-2017 The LimeSurvey Project Team\r\nAll rights reserved.',
        'license'                => 'License: GNU/GPL License v2 or later, see LICENSE.php\r\n\r\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.',
        'version'                => '1.0',
        'api_version'            => '3.0',
        'view_folder'            => 'views',
        'files_folder'           => 'files',
        'description'            => "<strong>LimeSurvey Advanced Template</strong><br>A template with custom options to show what it's possible to do with the new engines. Each template provider will be able to offer its own option page (loaded from template)",
        'owner_id'               => '1',
        'extends_template_name' => '',
    ));

    // Add minimal template
    $oDB->createCommand()->insert('{{templates}}', array(
        'name'                   => 'minimal',
        'folder'                 => 'minimal',
        'title'                  => 'Minimal Template',
        'creation_date'          => '2017-07-12 12:00:00',
        'author'                 => 'Louis Gac',
        'author_email'           => 'louis.gac@limesurvey.org',
        'author_url'             => 'https://www.limesurvey.org/',
        'copyright'              => 'Copyright (C) 2007-2017 The LimeSurvey Project Team\r\nAll rights reserved.',
        'license'                => 'License: GNU/GPL License v2 or later, see LICENSE.php\r\n\r\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.',
        'version'                => '1.0',
        'api_version'            => '3.0',
        'view_folder'            => 'views',
        'files_folder'           => 'files',
        'description'            => '<strong>LimeSurvey Minimal Template</strong><br>A clean and simple base that can be used by developers to create their own solution.',
        'owner_id'               => '1',
        'extends_template_name' => '',
    ));



    // Add material template
    $oDB->createCommand()->insert('{{templates}}', array(
        'name'                   => 'material',
        'folder'                 => 'material',
        'title'                  => 'Material Template',
        'creation_date'          => '2017-07-12 12:00:00',
        'author'                 => 'Louis Gac',
        'author_email'           => 'louis.gac@limesurvey.org',
        'author_url'             => 'https://www.limesurvey.org/',
        'copyright'              => 'Copyright (C) 2007-2017 The LimeSurvey Project Team\r\nAll rights reserved.',
        'license'                => 'License: GNU/GPL License v2 or later, see LICENSE.php\r\n\r\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.',
        'version'                => '1.0',
        'api_version'            => '3.0',
        'view_folder'            => 'views',
        'files_folder'           => 'files',
        'description'            => "<strong>LimeSurvey Advanced Template</strong><br> A template extending default, to show the inheritance concept. Notice the options, differents from Default.<br><small>uses FezVrasta's Material design theme for Bootstrap 3</small>",
        'owner_id'               => '1',
        'extends_template_name' => 'default',
    ));


    // Add template configuration table
    $oDB->createCommand()->createTable('{{template_configuration}}', array(
        'id'                => 'pk',
        'templates_name'    => 'string(150) NOT NULL',
        'sid'               => 'integer DEFAULT NULL',
        'gsid'              => 'integer DEFAULT NULL',
        'uid'               => 'integer DEFAULT NULL',
        'files_css'         => 'text',
        'files_js'          => 'text',
        'files_print_css'   => 'text',
        'options'           => 'text',
        'cssframework_name' => 'string(45) DEFAULT NULL',
        'cssframework_css'  => 'text',
        'cssframework_js'   => 'text',
        'packages_to_load'  => 'text',
    ));

    // Add global configuration for Advanced Template
    $oDB->createCommand()->insert('{{template_configuration}}', array(
        'templates_name'    => 'default',
        'files_css'         => '{"add": ["css/template.css", "css/animate.css"]}',
        'files_js'          => '{"add": ["scripts/template.js"]}',
        'files_print_css'   => '{"add":"css/print_template.css"}',
        'options'           => '{"ajaxmode":"off","brandlogo":"on", "brandlogofile":"./files/logo.png", "boxcontainer":"on", "backgroundimage":"off","animatebody":"off","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}',
        'cssframework_name' => 'bootstrap',
        'cssframework_css'  => '{"replace": [["css/bootstrap.css","css/flatly.css"]]}',
        'cssframework_js'   => '',
        'packages_to_load'  => '["pjax"]',
    ));


    // Add global configuration for Minimal Template
    $oDB->createCommand()->insert('{{template_configuration}}', array(
        'templates_name'    => 'minimal',
        'files_css'         => '{"add": ["css/template.css"]}',
        'files_js'          => '{"add": ["scripts/template.js"]}',
        'files_print_css'   => '{"add":"css/print_template.css"}',
        'options'           => '{}',
        'cssframework_name' => 'bootstrap',
        'cssframework_css'  => '{}',
        'cssframework_js'   => '',
        'packages_to_load'  => '["pjax"]',
    ));

    // Add global configuration for Material Template
    $oDB->createCommand()->insert('{{template_configuration}}', array(
        'templates_name'    => 'material',
        'files_css'         => '{"add": ["css/template.css", "css/bootstrap-material-design.css", "css/ripples.min.css"]}',
        'files_js'          => '{"add": ["scripts/template.js", "scripts/material.js", "scripts/ripples.min.js"]}',
        'files_print_css'   => '{"add":"css/print_template.css"}',
        'options'           => '{"ajaxmode":"off","brandlogo":"on", "brandlogofile":"./files/logo.png", "animatebody":"off","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}',
        'cssframework_name' => 'bootstrap',
        'cssframework_css'  => '{"replace": [["css/bootstrap.css","css/bootstrap.css"]]}',
        'cssframework_js'   => '',
        'packages_to_load'  => '["pjax"]',
    ));

}


/**
* @param CDbConnection $oDB
* @return void
*/
function upgradeTemplateTables298($oDB)
{
    // Add global configuration for Advanced Template
    $oDB->createCommand()->update('{{boxes}}', array(
        'url'=>'admin/templateoptions',
        'title'=>'Templates',
        'desc'=>'View templates list',
        ), "id=6");
}

function upgradeTokenTables256()
{
    $aTableNames = dbGetTablesLike("tokens%");
    $oDB = Yii::app()->getDb();
    foreach ($aTableNames as $sTableName) {
        try { setTransactionBookmark(); $oDB->createCommand()->dropIndex("idx_lime_{$sTableName}_efl", $sTableName); } catch (Exception $e) { rollBackToTransactionBookmark(); }
        alterColumn($sTableName, 'email', "text");
        alterColumn($sTableName, 'firstname', "string(150)");
        alterColumn($sTableName, 'lastname', "string(150)");
    }
}


function upgradeSurveyTables255()
{
    // We delete all the old boxes, and reinsert new ones
    Yii::app()->getDb()->createCommand(
        "DELETE FROM {{boxes}}"
    )->execute();

    // Then we recreate them
    $oDB = Yii::app()->db;
    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '1',
        'url'      => 'admin/survey/sa/newsurvey',
        'title'    => 'Create survey',
        'ico'      => 'add',
        'desc'     => 'Create a new survey',
        'page'     => 'welcome',
        'usergroup' => '-2',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '2',
        'url'      =>  'admin/survey/sa/listsurveys',
        'title'    =>  'List surveys',
        'ico'      =>  'list',
        'desc'     =>  'List available surveys',
        'page'     =>  'welcome',
        'usergroup' => '-1',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '3',
        'url'      =>  'admin/globalsettings',
        'title'    =>  'Global settings',
        'ico'      =>  'global',
        'desc'     =>  'Edit global settings',
        'page'     =>  'welcome',
        'usergroup' => '-2',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '4',
        'url'      =>  'admin/update',
        'title'    =>  'ComfortUpdate',
        'ico'      =>  'shield',
        'desc'     =>  'Stay safe and up to date',
        'page'     =>  'welcome',
        'usergroup' => '-2',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '5',
        'url'      =>  'admin/labels/sa/view',
        'title'    =>  'Label sets',
        'ico'      =>  'labels',
        'desc'     =>  'Edit label sets',
        'page'     =>  'welcome',
        'usergroup' => '-2',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '6',
        'url'      =>  'admin/themes/sa/view',
        'title'    =>  'Template editor',
        'ico'      =>  'templates',
        'desc'     =>  'Edit LimeSurvey templates',
        'page'     =>  'welcome',
        'usergroup' => '-2',
    ));

}

function upgradeSurveyTables254()
{
    Yii::app()->db->createCommand()->dropColumn('{{boxes}}', 'img');
    Yii::app()->db->createCommand()->addColumn('{{boxes}}', 'usergroup', 'integer');
}

function upgradeSurveyTables253()
{
    $oSchema = Yii::app()->db->schema;
    $aTables = dbGetTablesLike("survey\_%");
    foreach ($aTables as $sTable) {
        $oTableSchema = $oSchema->getTable($sTable);
        if (in_array('refurl', $oTableSchema->columnNames)) {
            alterColumn($sTable, 'refurl', "text");
        }
        if (in_array('ipaddr', $oTableSchema->columnNames)) {
            alterColumn($sTable, 'ipaddr', "text");
        }
    }
}


function upgradeBoxesTable251()
{
    Yii::app()->db->createCommand()->addColumn('{{boxes}}', 'ico', 'string');
    Yii::app()->db->createCommand()->update('{{boxes}}', array('ico'=>'add',
        'title'=>'Create survey')
        ,"id=1");
    Yii::app()->db->createCommand()->update('{{boxes}}', array('ico'=>'list')
        ,"id=2");
    Yii::app()->db->createCommand()->update('{{boxes}}', array('ico'=>'settings')
        ,"id=3");
    Yii::app()->db->createCommand()->update('{{boxes}}', array('ico'=>'shield')
        ,"id=4");
    Yii::app()->db->createCommand()->update('{{boxes}}', array('ico'=>'label')
        ,"id=5");
    Yii::app()->db->createCommand()->update('{{boxes}}', array('ico'=>'templates')
        ,"id=6");
}

/**
* Create boxes table
*/
function createBoxes250()
{
    $oDB = Yii::app()->db;
    $oDB->createCommand()->createTable('{{boxes}}', array(
        'id' => 'pk',
        'position' => 'integer',
        'url' => 'text',
        'title' => 'text',
        'img' => 'text',
        'desc' => 'text',
        'page'=>'text',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '1',
        'url'      => 'admin/survey/sa/newsurvey',
        'title'    => 'Create survey',
        'img'      => 'add.png',
        'desc'     => 'Create a new survey',
        'page'     => 'welcome',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '2',
        'url'      =>  'admin/survey/sa/listsurveys',
        'title'    =>  'List surveys',
        'img'      =>  'surveylist.png',
        'desc'     =>  'List available surveys',
        'page'     =>  'welcome',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '3',
        'url'      =>  'admin/globalsettings',
        'title'    =>  'Global settings',
        'img'      =>  'global.png',
        'desc'     =>  'Edit global settings',
        'page'     =>  'welcome',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '4',
        'url'      =>  'admin/update',
        'title'    =>  'ComfortUpdate',
        'img'      =>  'shield&#45;update.png',
        'desc'     =>  'Stay safe and up to date',
        'page'     =>  'welcome',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '5',
        'url'      =>  'admin/labels/sa/view',
        'title'    =>  'Label sets',
        'img'      =>  'labels.png',
        'desc'     =>  'Edit label sets',
        'page'     =>  'welcome',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '6',
        'url'      =>  'admin/themes/sa/view',
        'title'    =>  'Template editor',
        'img'      =>  'templates.png',
        'desc'     =>  'Edit LimeSurvey templates',
        'page'     =>  'welcome',
    ));
}

function fixKCFinder184()
{
    $sThirdPartyDir=Yii::app()->getConfig('homedir').DIRECTORY_SEPARATOR.'third_party'.DIRECTORY_SEPARATOR;
    rmdirr($sThirdPartyDir.'ckeditor/plugins/toolbar');
    rmdirr($sThirdPartyDir.'ckeditor/plugins/toolbar/ls-office2003');
    $aUnlink = glob($sThirdPartyDir.'kcfinder/cache/*.js');
    if ($aUnlink !== false) {
        array_map('unlink', $aUnlink);
    }
    $aUnlink = glob($sThirdPartyDir.'kcfinder/cache/*.css');
    if ($aUnlink !== false) {
        array_map('unlink', $aUnlink);
    }
    rmdirr($sThirdPartyDir.'kcfinder/upload/files');
    rmdirr($sThirdPartyDir.'kcfinder/upload/.thumbs');
}

function upgradeSurveyTables183()
{
    $oSchema = Yii::app()->db->schema;
    $aTables = dbGetTablesLike("survey\_%");
    if (!empty($aTables))
    {
        foreach ( $aTables as $sTableName )
        {
            $oTableSchema=$oSchema->getTable($sTableName);
            if (empty($oTableSchema->primaryKey))
            {
                addPrimaryKey(substr($sTableName,strlen(Yii::app()->getDb()->tablePrefix)), 'id');
            }
        }
    }
}

/**
* @param string $sMySQLCollation
*/
function upgradeSurveyTables181($sMySQLCollation)
{
    $oDB = Yii::app()->db;
    $oSchema = Yii::app()->db->schema;
    if (Yii::app()->db->driverName != 'pgsql') {
        $aTables = dbGetTablesLike("survey\_%");
        foreach ($aTables as $sTableName) {
            $oTableSchema = $oSchema->getTable($sTableName);
            if (!in_array('token', $oTableSchema->columnNames)) {
                continue;
            }
            // No token field in this table
            switch (Yii::app()->db->driverName) {
                case 'sqlsrv':
                case 'dblib':
                case 'mssql': dropSecondaryKeyMSSQL('token', $sTableName);
                    alterColumn($sTableName, 'token', "string(35) COLLATE SQL_Latin1_General_CP1_CS_AS");
                    $oDB->createCommand()->createIndex("{{idx_{$sTableName}_".rand(1, 40000).'}}', $sTableName, 'token');
                    break;
                case 'mysql':
                case 'mysqli':
                    alterColumn($sTableName, 'token', "string(35) COLLATE '{$sMySQLCollation}'");
                    break;
                default: die('Unknown database driver');
            }
        }

    }
}

/**
* @param string $sMySQLCollation
*/
function upgradeTokenTables181($sMySQLCollation)
{
    $oDB = Yii::app()->db;
    if (Yii::app()->db->driverName != 'pgsql') {
        $aTables = dbGetTablesLike("tokens%");
        if (!empty($aTables)) {
            foreach ($aTables as $sTableName) {
                switch (Yii::app()->db->driverName) {
                    case 'sqlsrv':
                    case 'dblib':
                    case 'mssql': dropSecondaryKeyMSSQL('token', $sTableName);
                        alterColumn($sTableName, 'token', "string(35) COLLATE SQL_Latin1_General_CP1_CS_AS");
                        $oDB->createCommand()->createIndex("{{idx_{$sTableName}_".rand(1, 50000).'}}', $sTableName, 'token');
                        break;
                    case 'mysql':
                    case 'mysqli':
                        alterColumn($sTableName, 'token', "string(35) COLLATE '{$sMySQLCollation}'");
                        break;
                    default: die('Unknown database driver');
                }
            }
        }
    }
}

function upgradeTokenTables179()
{
    $oDB = Yii::app()->db;
    $oSchema = Yii::app()->db->schema;
    switch (Yii::app()->db->driverName){
        case 'pgsql':
            $sSubstringCommand='substr';
            break;
        default:
            $sSubstringCommand='substring';
    }
    $surveyidresult = dbGetTablesLike("tokens%");
    if ($surveyidresult)
    {
        foreach ( $surveyidresult as $sTableName )
        {
            $oTableSchema=$oSchema->getTable($sTableName);
            foreach ($oTableSchema->columnNames as $sColumnName)
            {
                if (strpos($sColumnName,'attribute_')===0)
                {
                    alterColumn($sTableName, $sColumnName, "text");
                }
            }
            $oDB->createCommand("UPDATE {$sTableName} set email={$sSubstringCommand}(email,1,254)")->execute();
            try { setTransactionBookmark(); $oDB->createCommand()->dropIndex("idx_{$sTableName}_efl",$sTableName); } catch(Exception $e) { rollBackToTransactionBookmark();}
            try { setTransactionBookmark(); alterColumn($sTableName, 'email', "string(254)"); } catch(Exception $e) { rollBackToTransactionBookmark();}
            try { setTransactionBookmark(); alterColumn($sTableName, 'firstname', "string(150)"); } catch(Exception $e) { rollBackToTransactionBookmark();}
            try { setTransactionBookmark(); alterColumn($sTableName, 'lastname', "string(150)"); } catch(Exception $e) { rollBackToTransactionBookmark();}
        }
    }
}


function upgradeSurveys177()
{
    $oDB = Yii::app()->db;
    $sSurveyQuery = "SELECT surveyls_attributecaptions,surveyls_survey_id,surveyls_language FROM {{surveys_languagesettings}}";
    $oSurveyResult = $oDB->createCommand($sSurveyQuery)->queryAll();
    $sSurveyLSUpdateQuery= "update {{surveys_languagesettings}} set surveyls_attributecaptions=:attributecaptions where surveyls_survey_id=:surveyid and surveyls_language=:language";
    foreach ( $oSurveyResult as $aSurveyRow )
    {
        $aAttributeDescriptions=decodeTokenAttributes($aSurveyRow['surveyls_attributecaptions']);
        if (!$aAttributeDescriptions) $aAttributeDescriptions=array();
        $oDB->createCommand($sSurveyLSUpdateQuery)->execute(
            array(':language'=>$aSurveyRow['surveyls_language'],
                ':surveyid'=>$aSurveyRow['surveyls_survey_id'],
                ':attributecaptions'=>json_encode($aAttributeDescriptions)));
    }
    $sSurveyQuery = "SELECT sid,attributedescriptions FROM {{surveys}}";
    $oSurveyResult = $oDB->createCommand($sSurveyQuery)->queryAll();
    $sSurveyUpdateQuery= "update {{surveys}} set attributedescriptions=:attributedescriptions where sid=:surveyid";
    foreach ( $oSurveyResult as $aSurveyRow )
    {
        $aAttributeDescriptions=decodeTokenAttributes($aSurveyRow['attributedescriptions']);
        if (!$aAttributeDescriptions) $aAttributeDescriptions=array();
        $oDB->createCommand($sSurveyUpdateQuery)->execute(array(':attributedescriptions'=>json_encode($aAttributeDescriptions),':surveyid'=>$aSurveyRow['sid']));
    }
}



/**
* This function removes the old CPDB fields in token tables
* replaces them with standard attribute fields
* and records the mapping information in the attributedescription field in the survey table instead
*/
function upgradeTokens176()
{
    $oDB = Yii::app()->db;
    $arSurveys = $oDB
    ->createCommand()
    ->select('*')
    ->from('{{surveys}}')
    ->queryAll();
    // Fix any active token tables
    foreach ( $arSurveys as $arSurvey )
    {
        $sTokenTableName='tokens_'.$arSurvey['sid'];
        if (tableExists($sTokenTableName))
        {
            $aColumnNames=$aColumnNamesIterator=$oDB->schema->getTable('{{'.$sTokenTableName.'}}')->columnNames;
            $aAttributes = $arSurvey['attributedescriptions'];
            foreach($aColumnNamesIterator as $sColumnName)
            {
                // Check if an old atttribute_cpdb column exists in that token table
                if (strpos($sColumnName,'attribute_cpdb')!==false)
                {
                    $i=1;
                    // Look for a an attribute ID that is available
                    while (in_array('attribute_'.$i,$aColumnNames)) $i++;
                    $sNewName='attribute_'.$i;
                    $aColumnNames[]=$sNewName;
                    $oDB->createCommand()->renameColumn('{{'.$sTokenTableName.'}}',$sColumnName,$sNewName);
                    // Update attribute descriptions with the new mapping
                    if (isset($aAttributes[$sColumnName]))
                    {
                        $aAttributes[$sNewName]['cpdbmap']=substr($sColumnName,15);
                        unset($aAttributes[$sColumnName]);
                    }
                }
            }
            $oDB->createCommand()->update('{{surveys}}',array('attributedescriptions'=>serialize($aAttributes)),"sid=".$arSurvey['sid']);
        }
    }
    unset($arSurveys);
    // Now fix all 'old' token tables
    $aTables = dbGetTablesLike("%old_tokens%");
    foreach ( $aTables as $sTable )
    {
        $aColumnNames=$aColumnNamesIterator=$oDB->schema->getTable($sTable)->columnNames;
        foreach($aColumnNamesIterator as $sColumnName)
        {
            // Check if an old atttribute_cpdb column exists in that token table
            if (strpos($sColumnName,'attribute_cpdb')!==false)
            {
                $i=1;
                // Look for a an attribute ID that is available
                while (in_array('attribute_'.$i,$aColumnNames)) $i++;
                $sNewName='attribute_'.$i;
                $aColumnNames[]=$sNewName;
                $oDB->createCommand()->renameColumn($sTable,$sColumnName,$sNewName);
            }
        }
    }
}

function upgradeCPDBAttributeDefaultNames173()
{
    $sQuery = "SELECT attribute_id,attribute_name,COALESCE(lang,NULL)
    FROM {{participant_attribute_names_lang}}
    group by attribute_id, attribute_name, lang
    order by attribute_id";
    $oResult = Yii::app()->db->createCommand($sQuery)->queryAll();
    foreach ( $oResult as $aAttribute )
    {
        Yii::app()->getDb()->createCommand()->update('{{participant_attribute_names}}',array('defaultname'=>substr($aAttribute['attribute_name'],0,50)),"attribute_id={$aAttribute['attribute_id']}");
    }
}

/**
* Converts global permissions from users table to the new permission system,
* and converts template permissions from template_rights to new permission table
*/
function upgradePermissions166()
{
    Permission::model()->refreshMetaData();  // Needed because otherwise Yii tries to use the outdate permission schema for the permission table
    $oUsers=User::model()->findAll();
    foreach($oUsers as $oUser)
    {
        if ($oUser->create_survey==1)
        {
            $oPermission=new Permission;
            $oPermission->entity_id=0;
            $oPermission->entity='global';
            $oPermission->uid=$oUser->uid;
            $oPermission->permission='surveys';
            $oPermission->create_p=1;
            $oPermission->save();
        }
        if ($oUser->create_user==1 || $oUser->delete_user==1)
        {
            $oPermission=new Permission;
            $oPermission->entity_id=0;
            $oPermission->entity='global';
            $oPermission->uid=$oUser->uid;
            $oPermission->permission='users';
            $oPermission->create_p=$oUser->create_user;
            $oPermission->delete_p=$oUser->delete_user;
            $oPermission->update_p=1;
            $oPermission->read_p=1;
            $oPermission->save();
        }
        if ($oUser->superadmin==1)
        {
            $oPermission=new Permission;
            $oPermission->entity_id=0;
            $oPermission->entity='global';
            $oPermission->uid=$oUser->uid;
            $oPermission->permission='superadmin';
            $oPermission->read_p=1;
            $oPermission->save();
        }
        if ($oUser->configurator==1)
        {
            $oPermission=new Permission;
            $oPermission->entity_id=0;
            $oPermission->entity='global';
            $oPermission->uid=$oUser->uid;
            $oPermission->permission='settings';
            $oPermission->update_p=1;
            $oPermission->read_p=1;
            $oPermission->save();
        }
        if ($oUser->manage_template==1)
        {
            $oPermission=new Permission;
            $oPermission->entity_id=0;
            $oPermission->entity='global';
            $oPermission->uid=$oUser->uid;
            $oPermission->permission='templates';
            $oPermission->create_p=1;
            $oPermission->read_p=1;
            $oPermission->update_p=1;
            $oPermission->delete_p=1;
            $oPermission->import_p=1;
            $oPermission->export_p=1;
            $oPermission->save();
        }
        if ($oUser->manage_label==1)
        {
            $oPermission=new Permission;
            $oPermission->entity_id=0;
            $oPermission->entity='global';
            $oPermission->uid=$oUser->uid;
            $oPermission->permission='labelsets';
            $oPermission->create_p=1;
            $oPermission->read_p=1;
            $oPermission->update_p=1;
            $oPermission->delete_p=1;
            $oPermission->import_p=1;
            $oPermission->export_p=1;
            $oPermission->save();
        }
        if ($oUser->participant_panel==1)
        {
            $oPermission=new Permission;
            $oPermission->entity_id=0;
            $oPermission->entity='global';
            $oPermission->uid=$oUser->uid;
            $oPermission->permission='participantpanel';
            $oPermission->create_p=1;
            $oPermission->save();
        }
    }
    $sQuery = "SELECT * FROM {{templates_rights}}";
    $oResult = Yii::app()->getDb()->createCommand($sQuery)->queryAll();
    foreach ( $oResult as $aRow )
    {
        $oPermission=new Permission;
        $oPermission->entity_id=0;
        $oPermission->entity='template';
        $oPermission->uid=$aRow['uid'];
        $oPermission->permission=$aRow['folder'];
        $oPermission->read_p=1;
        $oPermission->save();
    }
}

/**
*  Make sure all active tables have the right sized token field
*
*  During a small period in the 2.0 cycle some survey tables got no
*  token field or a token field that was too small. This patch makes
*  sure all surveys that are not anonymous have a token field with the
*  right size
*
* @return string|null
*/
function upgradeSurveyTables164()
{
    $sQuery = "SELECT sid FROM {{surveys}} WHERE active='Y' and anonymized='N'";
    $aResult = Yii::app()->getDb()->createCommand($sQuery)->queryAll();
    if (!$aResult) {
        return "Database Error";
    } else {
        foreach ( $aResult as $sv )
        {
            $sSurveyTableName='survey_'.$sv['sid'];
            $aColumnNames=$aColumnNamesIterator=Yii::app()->db->schema->getTable('{{'.$sSurveyTableName.'}}')->columnNames;
            if (!in_array('token',$aColumnNames)) {
                addColumn('{{survey_'.$sv['sid'].'}}','token','string(36)');
            } else {
                alterColumn('{{survey_'.$sv['sid'].'}}','token','string(36)');
            }
        }
    }
}


function upgradeSurveys156()
{
    $sSurveyQuery = "SELECT * FROM {{surveys_languagesettings}}";
    $oSurveyResult = Yii::app()->getDb()->createCommand($sSurveyQuery)->queryAll();
    foreach ( $oSurveyResult as $aSurveyRow )
    {
        $aDefaultTexts=templateDefaultTexts($aSurveyRow['surveyls_language'],'unescaped');
        if (trim(strip_tags($aSurveyRow['surveyls_email_confirm'])) == '')
        {
            $sSurveyUpdateQuery= "update {{surveys}} set sendconfirmation='N' where sid=".$aSurveyRow['surveyls_survey_id'];
            Yii::app()->getDb()->createCommand($sSurveyUpdateQuery)->execute();

            $aValues=array('surveyls_email_confirm_subj'=>$aDefaultTexts['confirmation_subject'],
                'surveyls_email_confirm'=>$aDefaultTexts['confirmation']);
            SurveyLanguageSetting::model()->updateAll($aValues,'surveyls_survey_id=:sid',array(':sid'=>$aSurveyRow['surveyls_survey_id']));
        }
    }
}

// Add the usesleft field to all existing token tables
function upgradeTokens148()
{
    $aTables = dbGetTablesLike("tokens%");
    foreach ( $aTables as $sTable )
    {
        addColumn($sTable, 'participant_id', "string(50)");
        addColumn($sTable, 'blacklisted', "string(17)");
    }
}



function upgradeQuestionAttributes148()
{
    $sSurveyQuery = "SELECT sid,language,additional_languages FROM {{surveys}}";
    $oSurveyResult = dbExecuteAssoc($sSurveyQuery);
    $aAllAttributes=\LimeSurvey\Helpers\questionHelper::getAttributesDefinitions();
    foreach ( $oSurveyResult->readAll()  as $aSurveyRow)
    {
        $iSurveyID=$aSurveyRow['sid'];
        $aLanguages=array_merge(array($aSurveyRow['language']), explode(' ',$aSurveyRow['additional_languages']));
        $sAttributeQuery = "select q.qid,attribute,value from {{question_attributes}} qa , {{questions}} q where q.qid=qa.qid and sid={$iSurveyID}";
        $oAttributeResult = dbExecuteAssoc($sAttributeQuery);
        foreach ( $oAttributeResult->readAll() as $aAttributeRow)
        {
            if (isset($aAllAttributes[$aAttributeRow['attribute']]['i18n']) && $aAllAttributes[$aAttributeRow['attribute']]['i18n'])
            {
                Yii::app()->getDb()->createCommand("delete from {{question_attributes}} where qid={$aAttributeRow['qid']} and attribute='{$aAttributeRow['attribute']}'")->execute();
                foreach ($aLanguages as $sLanguage)
                {
                    $sAttributeInsertQuery="insert into {{question_attributes}} (qid,attribute,value,language) VALUES({$aAttributeRow['qid']},'{$aAttributeRow['attribute']}','{$aAttributeRow['value']}','{$sLanguage}' )";
                    modifyDatabase("",$sAttributeInsertQuery);
                }
            }
        }
    }
}


function upgradeSurveyTimings146()
{
    $aTables = dbGetTablesLike("%timings");
    foreach ($aTables as $sTable) {
        Yii::app()->getDb()->createCommand()->renameColumn($sTable,'interviewTime','interviewtime');
    }
}


// Add the usesleft field to all existing token tables
function upgradeTokens145()
{
    $aTables = dbGetTablesLike("tokens%");
    foreach ( $aTables as $sTable )
    {
        addColumn($sTable,'usesleft',"integer NOT NULL default 1");
        Yii::app()->getDb()->createCommand()->update($sTable,array('usesleft'=>'0'),"completed<>'N'");
    }
}


function upgradeSurveys145()
{
    $sSurveyQuery = "SELECT * FROM {{surveys}} where notification<>'0'";
    $oSurveyResult = dbExecuteAssoc($sSurveyQuery);
    foreach ( $oSurveyResult->readAll() as $aSurveyRow )
    {
        if ($aSurveyRow['notification']=='1' && trim($aSurveyRow['adminemail'])!='')
        {
            $aEmailAddresses=explode(';',$aSurveyRow['adminemail']);
            $sAdminEmailAddress=$aEmailAddresses[0];
            $sEmailnNotificationAddresses=implode(';',$aEmailAddresses);
            $sSurveyUpdateQuery= "update {{surveys}} set adminemail='{$sAdminEmailAddress}', emailnotificationto='{$sEmailnNotificationAddresses}' where sid=".$aSurveyRow['sid'];
            Yii::app()->getDb()->createCommand($sSurveyUpdateQuery)->execute();
        }
        else
        {
            $aEmailAddresses=explode(';',$aSurveyRow['adminemail']);
            $sAdminEmailAddress=$aEmailAddresses[0];
            $sEmailDetailedNotificationAddresses=implode(';',$aEmailAddresses);
            if (trim($aSurveyRow['emailresponseto'])!='')
            {
                $sEmailDetailedNotificationAddresses=$sEmailDetailedNotificationAddresses.';'.trim($aSurveyRow['emailresponseto']);
            }
            $sSurveyUpdateQuery= "update {{surveys}} set adminemail='{$sAdminEmailAddress}', emailnotificationto='{$sEmailDetailedNotificationAddresses}' where sid=".$aSurveyRow['sid'];
            Yii::app()->getDb()->createCommand($sSurveyUpdateQuery)->execute();
        }
    }
    $sSurveyQuery = "SELECT * FROM {{surveys_languagesettings}}";
    $oSurveyResult = Yii::app()->getDb()->createCommand($sSurveyQuery)->queryAll();
    foreach ( $oSurveyResult as $aSurveyRow )
    {
        $sLanguage = App()->language;
        $aDefaultTexts=templateDefaultTexts($sLanguage,'unescaped');
        unset($sLanguage);
        $aDefaultTexts['admin_detailed_notification']=$aDefaultTexts['admin_detailed_notification'].$aDefaultTexts['admin_detailed_notification_css'];
        $sSurveyUpdateQuery = "update {{surveys_languagesettings}} set
        email_admin_responses_subj=".$aDefaultTexts['admin_detailed_notification_subject'].",
        email_admin_responses=".$aDefaultTexts['admin_detailed_notification'].",
        email_admin_notification_subj=".$aDefaultTexts['admin_notification_subject'].",
        email_admin_notification=".$aDefaultTexts['admin_notification']."
        where surveyls_survey_id=".$aSurveyRow['surveyls_survey_id'];
        Yii::app()->getDb()->createCommand()->update('{{surveys_languagesettings}}',array('email_admin_responses_subj'=>$aDefaultTexts['admin_detailed_notification_subject'],
            'email_admin_responses'=>$aDefaultTexts['admin_detailed_notification'],
            'email_admin_notification_subj'=>$aDefaultTexts['admin_notification_subject'],
            'email_admin_notification'=>$aDefaultTexts['admin_notification']
            ),"surveyls_survey_id={$aSurveyRow['surveyls_survey_id']}");
    }

}


function upgradeSurveyPermissions145()
{
    $sPermissionQuery = "SELECT * FROM {{surveys_rights}}";
    $oPermissionResult = Yii::app()->getDb()->createCommand($sPermissionQuery)->queryAll();
    if (empty($oPermissionResult)) {return "Database Error";}
    else
    {
        $sTableName = '{{survey_permissions}}';
        foreach ( $oPermissionResult as $aPermissionRow )
        {

            $sPermissionInsertQuery=Yii::app()->getDb()->createCommand()->insert($sTableName, array('permission'=>'assessments',
                'create_p'=>$aPermissionRow['define_questions'],
                'read_p'=>$aPermissionRow['define_questions'],
                'update_p'=>$aPermissionRow['define_questions'],
                'delete_p'=>$aPermissionRow['define_questions'],
                'sid'=>$aPermissionRow['sid'],
                'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->getDb()->createCommand()->insert($sTableName,array('permission'=>'quotas',
                'create_p'=>$aPermissionRow['define_questions'],
                'read_p'=>$aPermissionRow['define_questions'],
                'update_p'=>$aPermissionRow['define_questions'],
                'delete_p'=>$aPermissionRow['define_questions'],
                'sid'=>$aPermissionRow['sid'],
                'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->getDb()->createCommand()->insert($sTableName,array('permission'=>'responses',
                'create_p'=>$aPermissionRow['browse_response'],
                'read_p'=>$aPermissionRow['browse_response'],
                'update_p'=>$aPermissionRow['browse_response'],
                'delete_p'=>$aPermissionRow['delete_survey'],
                'export_p'=>$aPermissionRow['export'],
                'import_p'=>$aPermissionRow['browse_response'],
                'sid'=>$aPermissionRow['sid'],
                'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->getDb()->createCommand()->insert($sTableName,array('permission'=>'statistics',
                'read_p'=>$aPermissionRow['browse_response'],
                'sid'=>$aPermissionRow['sid'],
                'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->getDb()->createCommand()->insert($sTableName,array('permission'=>'survey',
                'read_p'=>1,
                'delete_p'=>$aPermissionRow['delete_survey'],
                'sid'=>$aPermissionRow['sid'],
                'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->getDb()->createCommand()->insert($sTableName,array('permission'=>'surveyactivation',
                'update_p'=>$aPermissionRow['activate_survey'],
                'sid'=>$aPermissionRow['sid'],
                'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->getDb()->createCommand()->insert($sTableName,array('permission'=>'surveycontent',
                'create_p'=>$aPermissionRow['define_questions'],
                'read_p'=>$aPermissionRow['define_questions'],
                'update_p'=>$aPermissionRow['define_questions'],
                'delete_p'=>$aPermissionRow['define_questions'],
                'export_p'=>$aPermissionRow['export'],
                'import_p'=>$aPermissionRow['define_questions'],
                'sid'=>$aPermissionRow['sid'],
                'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->getDb()->createCommand()->insert($sTableName,array('permission'=>'surveylocale',
                'read_p'=>$aPermissionRow['edit_survey_property'],
                'update_p'=>$aPermissionRow['edit_survey_property'],
                'sid'=>$aPermissionRow['sid'],
                'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->getDb()->createCommand()->insert($sTableName,array('permission'=>'surveysettings',
                'read_p'=>$aPermissionRow['edit_survey_property'],
                'update_p'=>$aPermissionRow['edit_survey_property'],
                'sid'=>$aPermissionRow['sid'],
                'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->getDb()->createCommand()->insert($sTableName,array('permission'=>'tokens',
                'create_p'=>$aPermissionRow['activate_survey'],
                'read_p'=>$aPermissionRow['activate_survey'],
                'update_p'=>$aPermissionRow['activate_survey'],
                'delete_p'=>$aPermissionRow['activate_survey'],
                'export_p'=>$aPermissionRow['export'],
                'import_p'=>$aPermissionRow['activate_survey'],
                'sid'=>$aPermissionRow['sid'],
                'uid'=>$aPermissionRow['uid']));
        }
    }
}

function upgradeTables143()
{

    $aQIDReplacements=array();
    $answerquery = "select a.*, q.sid, q.gid from {{answers}} a,{{questions}} q where a.qid=q.qid and q.type in ('L','O','!') and a.default_value='Y'";
    $answerresult = Yii::app()->getDb()->createCommand($answerquery)->queryAll();
    foreach ( $answerresult as $row )
    {
        modifyDatabase("","INSERT INTO {{defaultvalues}} (qid, scale_id,language,specialtype,defaultvalue) VALUES ({$row['qid']},0,".dbQuoteAll($row['language']).",'',".dbQuoteAll($row['code']).")");
    }

    // Convert answers to subquestions

    $answerquery = "select a.*, q.sid, q.gid, q.type from {{answers}} a,{{questions}} q where a.qid=q.qid and a.language=q.language and q.type in ('1','A','B','C','E','F','H','K',';',':','M','P','Q')";
    $answerresult = Yii::app()->getDb()->createCommand($answerquery)->queryAll();
    foreach ( $answerresult as $row )
    {

        $aInsert=array();
        if (isset($aQIDReplacements[$row['qid'].'_'.$row['code']]))
        {
            $aInsert['qid']=$aQIDReplacements[$row['qid'].'_'.$row['code']];
        }
        $aInsert['sid']=$row['sid'];
        $aInsert['gid']=$row['gid'];
        $aInsert['parent_qid']=$row['qid'];
        $aInsert['type']=$row['type'];
        $aInsert['title']=$row['code'];
        $aInsert['question']=$row['answer'];
        $aInsert['question_order']=$row['sortorder'];
        $aInsert['language']=$row['language'];

        $iLastInsertID=Question::model()->insertRecords($aInsert);
        if (!isset($aInsert['qid']))
        {
            $aQIDReplacements[$row['qid'].'_'.$row['code']]=$iLastInsertID;
            $iSaveSQID=$aQIDReplacements[$row['qid'].'_'.$row['code']];
        }
        else
        {
            $iSaveSQID=$aInsert['qid'];
        }
        if (($row['type']=='M' || $row['type']=='P') && $row['default_value']=='Y')
        {
            modifyDatabase("","INSERT INTO {{defaultvalues}} (qid, sqid, scale_id,language,specialtype,defaultvalue) VALUES ({$row['qid']},{$iSaveSQID},0,".dbQuoteAll($row['language']).",'','Y')");
        }
    }
    // Sanitize data
    if (Yii::app()->db->driverName=='pgsql')
    {
        modifyDatabase("","delete from {{answers}} USING {{questions}} WHERE {{answers}}.qid={{questions}}.qid AND {{questions}}.type in ('1','F','H','M','P','W','Z')");
    }
    else
    {
        modifyDatabase("","delete {{answers}} from {{answers}} LEFT join {{questions}} ON {{answers}}.qid={{questions}}.qid where {{questions}}.type in ('1','F','H','M','P','W','Z')");
    }

    // Convert labels to answers
    $answerquery = "select qid ,type ,lid ,lid1, language from {{questions}} where parent_qid=0 and type in ('1','F','H','M','P','W','Z')";
    $answerresult = Yii::app()->getDb()->createCommand($answerquery)->queryAll();
    foreach ( $answerresult as $row )
    {
        $labelquery="Select * from {{labels}} where lid={$row['lid']} and language=".dbQuoteAll($row['language']);
        $labelresult = Yii::app()->getDb()->createCommand($labelquery)->queryAll();
        foreach ( $labelresult as $lrow )
        {
            modifyDatabase("","INSERT INTO {{answers}} (qid, code, answer, sortorder, language, assessment_value) VALUES ({$row['qid']},".dbQuoteAll($lrow['code']).",".dbQuoteAll($lrow['title']).",{$lrow['sortorder']},".dbQuoteAll($lrow['language']).",{$lrow['assessment_value']})");
            //$labelids[]
        }
        if ($row['type']=='1')
        {
            $labelquery="Select * from {{labels}} where lid={$row['lid1']} and language=".dbQuoteAll($row['language']);
            $labelresult = Yii::app()->getDb()->createCommand($labelquery)->queryAll();
            foreach ( $labelresult as $lrow )
            {
                modifyDatabase("","INSERT INTO {{answers}} (qid, code, answer, sortorder, language, scale_id, assessment_value) VALUES ({$row['qid']},".dbQuoteAll($lrow['code']).",".dbQuoteAll($lrow['title']).",{$lrow['sortorder']},".dbQuoteAll($lrow['language']).",1,{$lrow['assessment_value']})");
            }
        }
    }

    // Convert labels to subquestions
    $answerquery = "select * from {{questions}} where parent_qid=0 and type in (';',':')";
    $answerresult = Yii::app()->getDb()->createCommand($answerquery)->queryAll();
    foreach ( $answerresult as $row )
    {
        $labelquery="Select * from {{labels}} where lid={$row['lid']} and language=".dbQuoteAll($row['language']);
        $labelresult = Yii::app()->getDb()->createCommand($labelquery)->queryAll();
        foreach ( $labelresult as $lrow )
        {
            $aInsert=array();
            if (isset($aQIDReplacements[$row['qid'].'_'.$lrow['code'].'_1']))
            {
                $aInsert['qid']=$aQIDReplacements[$row['qid'].'_'.$lrow['code'].'_1'];
            }
            $aInsert['sid']=$row['sid'];
            $aInsert['gid']=$row['gid'];
            $aInsert['parent_qid']=$row['qid'];
            $aInsert['type']=$row['type'];
            $aInsert['title']=$lrow['code'];
            $aInsert['question']=$lrow['title'];
            $aInsert['question_order']=$lrow['sortorder'];
            $aInsert['language']=$lrow['language'];
            $aInsert['scale_id']=1;
            $iLastInsertID=Question::model()->insertRecords($aInsert);

            if (isset($aInsert['qid']))
            {
                $aQIDReplacements[$row['qid'].'_'.$lrow['code'].'_1']=$iLastInsertID;
            }
        }
    }



    $updatequery = "update {{questions}} set type='!' where type='W'";
    modifyDatabase("",$updatequery);
    $updatequery = "update {{questions}} set type='L' where type='Z'";
    modifyDatabase("",$updatequery);
}


function upgradeQuestionAttributes142()
{
    $attributequery="Select qid from {{question_attributes}} where attribute='exclude_all_other'  group by qid having count(qid)>1 ";
    $questionids = Yii::app()->getDb()->createCommand($attributequery)->queryRow();
    if(!is_array($questionids)) { return "Database Error"; }
    else
    {
        foreach ($questionids as $questionid)
        {
            //Select all affected question attributes
            $attributevalues=Yii::app()->getDb()->createCommand("SELECT value from {{question_attributes}} where attribute='exclude_all_other' and qid=".$questionid)->queryColumn();
            modifyDatabase("","delete from {{question_attributes}} where attribute='exclude_all_other' and qid=".$questionid);
            $record['value']=implode(';',$attributevalues);
            $record['attribute']='exclude_all_other';
            $record['qid']=$questionid;
            Yii::app()->getDb()->createCommand()->insert('{{question_attributes}}', $record)->execute();
        }
    }
}

function upgradeSurveyTables139()
{
    $aTables = dbGetTablesLike("survey\_%");
    foreach ( $aTables as $sTable )
    {
        addColumn($sTable,'lastpage','integer');
    }
}


// Add the reminders tracking fields
function upgradeTokenTables134()
{
    $aTables = dbGetTablesLike("tokens%");
    foreach ( $aTables as $sTable )
    {
        addColumn($sTable,'validfrom',"datetime");
        addColumn($sTable,'validuntil',"datetime");
    }
}

/**
* @param string $sFieldType
* @param string $sColumn
*/
function alterColumn($sTable, $sColumn, $sFieldType, $bAllowNull = true, $sDefault = 'NULL')
{
    $oDB = Yii::app()->db;
    switch (Yii::app()->db->driverName) {
        case 'mysql':
        case 'mysqli':
            $sType = $sFieldType;
            if ($bAllowNull !== true) {
                $sType .= ' NOT NULL';
            }
            if ($sDefault != 'NULL') {
                $sType .= " DEFAULT '{$sDefault}'";
            }
            $oDB->createCommand()->alterColumn($sTable, $sColumn, $sType);
            break;
        case 'dblib':
        case 'sqlsrv':
        case 'mssql':
            dropDefaultValueMSSQL($sColumn, $sTable);
            $sType = $sFieldType;
            if ($bAllowNull != true && $sDefault != 'NULL') {
                $oDB->createCommand("UPDATE {$sTable} SET [{$sColumn}]='{$sDefault}' where [{$sColumn}] is NULL;")->execute();
            }
            if ($bAllowNull != true) {
                $sType .= ' NOT NULL';
            } else {
                $sType .= ' NULL';
            }
            $oDB->createCommand()->alterColumn($sTable, $sColumn, $sType);
            if ($sDefault != 'NULL') {
                $oDB->createCommand("ALTER TABLE {$sTable} ADD default '{$sDefault}' FOR [{$sColumn}];")->execute();
            }
            break;
        case 'pgsql':
            $sType = $sFieldType;
            $oDB->createCommand()->alterColumn($sTable, $sColumn, $sType);
            try { $oDB->createCommand("ALTER TABLE {$sTable} ALTER COLUMN {$sColumn} DROP DEFAULT")->execute(); } catch (Exception $e) {};
            try { $oDB->createCommand("ALTER TABLE {$sTable} ALTER COLUMN {$sColumn} DROP NOT NULL")->execute(); } catch (Exception $e) {};

            if ($bAllowNull != true) {
                $oDB->createCommand("ALTER TABLE {$sTable} ALTER COLUMN {$sColumn} SET NOT NULL")->execute();
            }
            if ($sDefault != 'NULL') {
                $oDB->createCommand("ALTER TABLE {$sTable} ALTER COLUMN {$sColumn} SET DEFAULT '{$sDefault}'")->execute();
            }
            $oDB->createCommand()->alterColumn($sTable, $sColumn, $sType);
            break;
        default: die('Unknown database type');
    }
}

/**
* @param string $sType
*/
function addColumn($sTableName, $sColumn, $sType)
{
    Yii::app()->db->createCommand()->addColumn($sTableName, $sColumn, $sType);
}

/**
* Set a transaction bookmark - this is critical for Postgres because a transaction in Postgres cannot be continued unless you roll back to the transaction bookmark first
*
* @param mixed $sBookmark  Name of the bookmark
*/
function setTransactionBookmark($sBookmark = 'limesurvey')
{
    if (Yii::app()->db->driverName == 'pgsql') {
        Yii::app()->db->createCommand("SAVEPOINT {$sBookmark};")->execute();
    }
}

/**
* Roll back to a transaction bookmark
*
* @param mixed $sBookmark   Name of the bookmark
*/
function rollBackToTransactionBookmark($sBookmark = 'limesurvey')
{
    if (Yii::app()->db->driverName == 'pgsql') {
        Yii::app()->db->createCommand("ROLLBACK TO SAVEPOINT {$sBookmark};")->execute();
    }
}

/**
* Drop a default value in MSSQL
*
* @param string $fieldname
* @param mixed $tablename
*/
function dropDefaultValueMSSQL($fieldname, $tablename)
{
    // find out the name of the default constraint
    // Did I already mention that this is the most suckiest thing I have ever seen in MSSQL database?
    $dfquery = "SELECT c_obj.name AS constraint_name
    FROM sys.sysobjects AS c_obj INNER JOIN
    sys.sysobjects AS t_obj ON c_obj.parent_obj = t_obj.id INNER JOIN
    sys.sysconstraints AS con ON c_obj.id = con.constid INNER JOIN
    sys.syscolumns AS col ON t_obj.id = col.id AND con.colid = col.colid
    WHERE (c_obj.xtype = 'D') AND (col.name = '$fieldname') AND (t_obj.name='{$tablename}')";
    $defaultname = Yii::app()->getDb()->createCommand($dfquery)->queryRow();
    if ($defaultname != false) {
        Yii::app()->db->createCommand("ALTER TABLE {$tablename} DROP CONSTRAINT {$defaultname['constraint_name']}")->execute();
    }
}

/**
* This function drops a unique Key of an MSSQL database field by using the field name and the table name
*
* @param string $sFieldName
* @param string $sTableName
*/
function dropUniqueKeyMSSQL($sFieldName, $sTableName)
{
    $sQuery = "select TC.Constraint_Name, CC.Column_Name from information_schema.table_constraints TC
    inner join information_schema.constraint_column_usage CC on TC.Constraint_Name = CC.Constraint_Name
    where TC.constraint_type = 'Unique' and Column_name='{$sFieldName}' and TC.TABLE_NAME='{$sTableName}'";
    $aUniqueKeyName = Yii::app()->getDb()->createCommand($sQuery)->queryRow();
    if ($aUniqueKeyName != false) {
        Yii::app()->getDb()->createCommand("ALTER TABLE {$sTableName} DROP CONSTRAINT {$aUniqueKeyName['Constraint_Name']}")->execute();
    }
}

/**
* This function drops a secondary key of an MSSQL database field by using the field name and the table name
*
* @param string $sFieldName
* @param mixed $sTableName
*/
function dropSecondaryKeyMSSQL($sFieldName, $sTableName)
{
    $oDB = Yii::app()->getDb();
    $sQuery = "select
    i.name as IndexName
    from sys.indexes i
    join sys.objects o on i.object_id = o.object_id
    join sys.index_columns ic on ic.object_id = i.object_id
    and ic.index_id = i.index_id
    join sys.columns co on co.object_id = i.object_id
    and co.column_id = ic.column_id
    where i.[type] = 2
    and i.is_unique = 0
    and i.is_primary_key = 0
    and o.[type] = 'U'
    and ic.is_included_column = 0
    and o.name='{$sTableName}' and co.name='{$sFieldName}'";
    $aKeyName = Yii::app()->getDb()->createCommand($sQuery)->queryScalar();
    if ($aKeyName != false) {
        try { $oDB->createCommand()->dropIndex($aKeyName, $sTableName); } catch (Exception $e) { }
    }
}

/**
* Drops the primary key of a table
*
* @param string $sTablename
* @param string $oldPrimaryKeyColumn
*/
function dropPrimaryKey($sTablename, $oldPrimaryKeyColumn = null)
{
    switch (Yii::app()->db->driverName) {
        case 'mysql':
        if ($oldPrimaryKeyColumn !== null) {
            $sQuery = "ALTER TABLE {{".$sTablename."}} MODIFY {$oldPrimaryKeyColumn} INT NOT NULL";
            Yii::app()->db->createCommand($sQuery)->execute();
        }
            $sQuery = "ALTER TABLE {{".$sTablename."}} DROP PRIMARY KEY";
            Yii::app()->db->createCommand($sQuery)->execute();
            break;
        case 'pgsql':
        case 'sqlsrv':
        case 'dblib':
        case 'mssql':
            $pkquery = "SELECT CONSTRAINT_NAME "
            ."FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS "
            ."WHERE (TABLE_NAME = '{{{$sTablename}}}') AND (CONSTRAINT_TYPE = 'PRIMARY KEY')";

            $primarykey = Yii::app()->db->createCommand($pkquery)->queryRow(false);
            if ($primarykey !== false) {
                $sQuery = "ALTER TABLE {{".$sTablename."}} DROP CONSTRAINT ".$primarykey[0];
                Yii::app()->db->createCommand($sQuery)->execute();
            }
            break;
        default: die('Unknown database type');
    }

}

/**
* @param string $sTablename
*/
function addPrimaryKey($sTablename, $aColumns)
{
    return Yii::app()->db->createCommand()->addPrimaryKey('PK_'.$sTablename.'_'.randomChars(12, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'), '{{'.$sTablename.'}}', $aColumns);
}

/**
* Modifies a primary key in one command  - this is only tested on MySQL
*
* @param string $sTablename The table name
* @param string[] $aColumns Column names to be in the new key
*/
function modifyPrimaryKey($sTablename, $aColumns)
{
    Yii::app()->db->createCommand("ALTER TABLE {{".$sTablename."}} DROP PRIMARY KEY, ADD PRIMARY KEY (".implode(',', $aColumns).")")->execute();
}



/**
* @param string $sEncoding
* @param string $sCollation
*/
function fixMySQLCollations($sEncoding, $sCollation)
{
    $surveyidresult = dbGetTablesLike("%");
    foreach ($surveyidresult as $sTableName) {
        try {
            Yii::app()->getDb()->createCommand("ALTER TABLE {$sTableName} CONVERT TO CHARACTER SET {$sEncoding} COLLATE {$sCollation};")->execute();
        } catch (Exception $e) {
            // There are some big survey response tables that cannot be converted because the new charset probably uses
            // more bytes per character than the old one - we just leave them as they are for now.
        };
    }
    $sDatabaseName = getDBConnectionStringProperty('dbname');
    Yii::app()->getDb()->createCommand("ALTER DATABASE `$sDatabaseName` DEFAULT CHARACTER SET {$sEncoding} COLLATE {$sCollation};");
}

/**
*  Drops a column, automatically removes blocking default value on MSSQL
 * @param string $sTableName
 * @param string $sColumnName
 */
function dropColumn($sTableName, $sColumnName)
{
    if (Yii::app()->db->getDriverName()=='mssql' || Yii::app()->db->getDriverName()=='sqlsrv' || Yii::app()->db->getDriverName()=='dblib')
    {
        dropDefaultValueMSSQL($sColumnName,$sTableName);
    }
    try {
        Yii::app()->db->createCommand()->dropColumn($sTableName,$sColumnName);
    } catch (Exception $e) {
       // If it cannot be dropped we assume it is already gone
    };

}


/**
*  Renames a language code in the whole LimeSurvey database
 * @param string $sOldLanguageCode
 * @param string $sNewLanguageCode
 */
function alterLanguageCode($sOldLanguageCode,$sNewLanguageCode)
{
    $oDB = Yii::app()->db;
    $oDB->createCommand()->update('{{answers}}',array('language'=>$sNewLanguageCode),'language=:lang',array(':lang'=>$sOldLanguageCode));
    $oDB->createCommand()->update('{{questions}}',array('language'=>$sNewLanguageCode),'language=:lang',array(':lang'=>$sOldLanguageCode));
    $oDB->createCommand()->update('{{groups}}',array('language'=>$sNewLanguageCode),'language=:lang',array(':lang'=>$sOldLanguageCode));
    $oDB->createCommand()->update('{{labels}}',array('language'=>$sNewLanguageCode),'language=:lang',array(':lang'=>$sOldLanguageCode));
    $oDB->createCommand()->update('{{surveys}}',array('language'=>$sNewLanguageCode),'language=:lang',array(':lang'=>$sOldLanguageCode));
    $oDB->createCommand()->update('{{surveys_languagesettings}}',array('surveyls_language'=>$sNewLanguageCode),'surveyls_language=:lang',array(':lang'=>$sOldLanguageCode));
    $oDB->createCommand()->update('{{users}}',array('lang'=>$sNewLanguageCode),'lang=:language',array(':language'=>$sOldLanguageCode));

    $resultdata=$oDB->createCommand("select * from {{labelsets}}");
    foreach ($resultdata->queryAll() as $datarow){
        $aLanguages=explode(' ',$datarow['languages']);
        foreach ($aLanguages as &$sLanguage)
        {
            if ($sLanguage==$sOldLanguageCode) $sLanguage=$sNewLanguageCode;
        }
        $toreplace=implode(' ',$aLanguages);
        $oDB->createCommand()->update('{{labelsets}}',array('languages'=>$toreplace),'lid=:lid',array(':lid'=>$datarow['lid']));
    }

    $resultdata=$oDB->createCommand("select * from {{surveys}}");
    foreach ($resultdata->queryAll() as $datarow){
        $aLanguages=explode(' ',$datarow['additional_languages']);
        foreach ($aLanguages as &$sLanguage)
        {
            if ($sLanguage==$sOldLanguageCode) $sLanguage=$sNewLanguageCode;
        }
        $toreplace=implode(' ',$aLanguages);
        $oDB->createCommand()->update('{{surveys}}',array('additional_languages'=>$toreplace),'sid=:sid',array(':sid'=>$datarow['sid']));
    }
}


function fixLanguageConsistencyAllSurveys()
{
    $surveyidquery = "SELECT sid,additional_languages FROM ".App()->db->quoteColumnName('{{surveys}}');
    $surveyidresult = Yii::app()->db->createCommand($surveyidquery)->queryAll();
    foreach ($surveyidresult as $sv) {
        fixLanguageConsistency($sv['sid'], $sv['additional_languages']);
    }
}
