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

// There will be a file for each database (accordingly named to the dbADO scheme)
// where based on the current database version the database is upgraded
// For this there will be a settings table which holds the last time the database was upgraded

/**
 * @param integer $iOldDBVersion The previous database version
 * @param boolean $bSilent Run update silently with no output - this checks if the update can be run silently at all. If not it will not run any updates at all.
 */
function db_upgrade_all($iOldDBVersion, $bSilent=false) {

    /**
    * If you add a new database version add any critical database version numbers to this array. See link
    * @link https://manual.limesurvey.org/Database_versioning for explanations
    * @var array $aCriticalDBVersions An array of cricital database version.
    */
    $aCriticalDBVersions=array();
    $aAllUpdates=range($iOldDBVersion+1,Yii::app()->getConfig('dbversionnumber'));
    // If trying to update silenty check if it is really possible
    if ($bSilent && ($iOldDBVersion<258 || count(array_intersect($aCriticalDBVersions,$aAllUpdates))>0))
    {
        return false;
    }

    /// This function does anything necessary to upgrade
    /// older versions to match current functionality
    global $modifyoutput;
    Yii::app()->loadHelper('database');

    $sUserTemplateRootDir = Yii::app()->getConfig('usertemplaterootdir');
    $sStandardTemplateRootDir = Yii::app()->getConfig('standardtemplaterootdir');

    $oDB = Yii::app()->getDb();
    Yii::app()->setConfig('Updating',true);
    $oDB->schemaCachingDuration=0; // Deactivate schema caching
    $oTransaction = $oDB->beginTransaction();
    try
    {
        if ($iOldDBVersion < 111)
        {
            // Language upgrades from version 110 to 111 because the language names did change

            $aOldNewLanguages=array('german_informal'=>'german-informal',
                'cns'=>'cn-Hans',
                'cnt'=>'cn-Hant',
                'pt_br'=>'pt-BR',
                'gr'=>'el',
                'jp'=>'ja',
                'si'=>'sl',
                'se'=>'sv',
                'vn'=>'vi');
            foreach  ($aOldNewLanguages as $sOldLanguageCode=>$sNewLanguageCode)
            {
                alterLanguageCode($sOldLanguageCode,$sNewLanguageCode);
            }
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>111),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 112) {
            // New size of the username field (it was previously 20 chars wide)
            $oDB->createCommand()->alterColumn('{{users}}','users_name',"string(64) NOT NULL");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>112),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 113) {
            //Fixes the collation for the complete DB, tables and columns

            if (Yii::app()->db->driverName=='mysql')
            {
                fixMySQLCollations('utf8','utf8_unicode_ci');
            }
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>113),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 114) {
            $oDB->createCommand()->alterColumn('{{saved_control}}','email',"string(320) NOT NULL");
            $oDB->createCommand()->alterColumn('{{surveys}}','adminemail',"string(320) NOT NULL");
            $oDB->createCommand()->alterColumn('{{users}}','email',"string(320) NOT NULL");
            $oDB->createCommand()->insert('{{settings_global}}',array('stg_name'=>'SessionName','stg_value'=>randomChars(64,'ABCDEFGHIJKLMNOPQRSTUVWXYZ!"$%&/()=?`+*~#",;.:abcdefghijklmnopqrstuvwxyz123456789')));
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>114),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 126) {

            addColumn('{{surveys}}','printanswers',"string(1) default 'N'");
            addColumn('{{surveys}}','listpublic',"string(1) default 'N'");

            upgradeSurveyTables126();
            upgradeTokenTables126();

            // Create quota table
            $oDB->createCommand()->createTable('{{quota}}',array(
                'id' => 'pk',
                'sid' => 'integer',
                'qlimit' => 'integer',
                'name' => 'string',
                'action' => 'integer',
                'active' => 'integer NOT NULL DEFAULT 1'
            ));

            // Create quota_members table
            $oDB->createCommand()->createTable('{{quota_members}}',array(
                'id' => 'pk',
                'sid' => 'integer',
                'qid' => 'integer',
                'quota_id' => 'integer',
                'code' => 'string(5)'
            ));
            $oDB->createCommand()->createIndex('sid','{{quota_members}}','sid,qid,quota_id,code',true);


            // Create templates_rights table
            $oDB->createCommand()->createTable('{{templates_rights}}',array(
                'uid' => 'integer NOT NULL',
                'folder' => 'string NOT NULL',
                'use' => 'integer',
                'PRIMARY KEY (uid, folder)'
            ));

            // Create templates table
            $oDB->createCommand()->createTable('{{templates}}',array(
                'folder' => 'string NOT NULL',
                'creator' => 'integer NOT NULL',
                'PRIMARY KEY (folder)'
            ));

            // Rename Norwegian language codes
            alterLanguageCode('no','nb');

            addColumn('{{surveys}}','htmlemail',"string(1) default 'N'");
            addColumn('{{surveys}}','tokenanswerspersistence',"string(1) default 'N'");
            addColumn('{{surveys}}','usecaptcha',"string(1) default 'N'");
            addColumn('{{surveys}}','bounce_email','text');
            addColumn('{{users}}','htmleditormode',"string(7) default 'default'");
            addColumn('{{users}}','superadmin',"integer NOT NULL default '0'");
            addColumn('{{questions}}','lid1',"integer NOT NULL default '0'");

            alterColumn('{{conditions}}','value',"string",false,'');
            alterColumn('{{labels}}','title',"text");

            $oDB->createCommand()->update('{{users}}',array('superadmin'=>1),"create_survey=1 AND create_user=1 AND move_user=1 AND delete_user=1 AND configurator=1");
            $oDB->createCommand()->update('{{conditions}}',array('method'=>'=='),"(method is null) or method='' or method='0'");

            dropColumn('{{users}}','move_user');

            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>126),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 127) {
            modifyDatabase("","create index answers_idx2 on {{answers}} (sortorder)"); echo $modifyoutput;
            modifyDatabase("","create index assessments_idx2 on {{assessments}} (sid)"); echo $modifyoutput;
            modifyDatabase("","create index assessments_idx3 on {{assessments}} (gid)"); echo $modifyoutput;
            modifyDatabase("","create index conditions_idx2 on {{conditions}} (qid)"); echo $modifyoutput;
            modifyDatabase("","create index conditions_idx3 on {{conditions}} (cqid)"); echo $modifyoutput;
            modifyDatabase("","create index groups_idx2 on {{groups}} (sid)"); echo $modifyoutput;
            modifyDatabase("","create index question_attributes_idx2 on {{question_attributes}} (qid)"); echo $modifyoutput;
            modifyDatabase("","create index questions_idx2 on {{questions}} (sid)"); echo $modifyoutput;
            modifyDatabase("","create index questions_idx3 on {{questions}} (gid)"); echo $modifyoutput;
            modifyDatabase("","create index questions_idx4 on {{questions}} (type)"); echo $modifyoutput;
            modifyDatabase("","create index quota_idx2 on {{quota}} (sid)"); echo $modifyoutput;
            modifyDatabase("","create index saved_control_idx2 on {{saved_control}} (sid)"); echo $modifyoutput;
            modifyDatabase("","create index user_in_groups_idx1 on {{user_in_groups}} (ugid, uid)"); echo $modifyoutput;
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>127),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 128) {
            upgradeTokens128();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>128),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 129) {
            addColumn('{{surveys}}','startdate',"datetime");
            addColumn('{{surveys}}','usestartdate',"string(1) NOT NULL default 'N'");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>129),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 130)
        {
            addColumn('{{conditions}}','scenario',"integer NOT NULL default '1'");
            $oDB->createCommand()->update('{{conditions}}',array('scenario'=>'1'),"(scenario is null) or scenario=0");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>130),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 131)
        {
            addColumn('{{surveys}}','publicstatistics',"string(1) NOT NULL default 'N'");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>131),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 132)
        {
            addColumn('{{surveys}}','publicgraphs',"string(1) NOT NULL default 'N'");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>132),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 133)
        {
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
        }

        if ($iOldDBVersion < 134)
        {
            // Add new tokens setting
            addColumn('{{surveys}}','usetokens',"string(1) NOT NULL default 'N'");
            addColumn('{{surveys}}','attributedescriptions','text');
            dropColumn('{{surveys}}','attribute1');
            dropColumn('{{surveys}}','attribute2');
            upgradeTokenTables134();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>134),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 135)
        {
            alterColumn('{{question_attributes}}','value','text');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>135),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 136) //New Quota Functions
        {
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
        }

        if ($iOldDBVersion < 137) //New Quota Functions
        {
            addColumn('{{surveys_languagesettings}}','surveyls_dateformat',"integer NOT NULL default 1");
            addColumn('{{users}}','dateformat',"integer NOT NULL default 1");
            $oDB->createCommand()->update('{{surveys}}',array('startdate'=>NULL),"usestartdate='N'");
            $oDB->createCommand()->update('{{surveys}}',array('expires'=>NULL),"useexpiry='N'");
            dropColumn('{{surveys}}','useexpiry');
            dropColumn('{{surveys}}','usestartdate');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>137),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 138) //Modify quota field
        {
            alterColumn('{{quota_members}}','code',"string(11)");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>138),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 139) //Modify quota field
        {
            upgradeSurveyTables139();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>139),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 140) //Modify surveys table
        {
            addColumn('{{surveys}}','emailresponseto','text');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>140),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 141) //Modify surveys table
        {
            addColumn('{{surveys}}','tokenlength','integer NOT NULL default 15');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>141),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 142) //Modify surveys table
        {
            upgradeQuestionAttributes142();
            $oDB->createCommand()->alterColumn('{{surveys}}','expires',"datetime");
            $oDB->createCommand()->alterColumn('{{surveys}}','startdate',"datetime");
            $oDB->createCommand()->update('{{question_attributes}}',array('value'=>0),"value='false'");
            $oDB->createCommand()->update('{{question_attributes}}',array('value'=>1),"value='true'");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>142),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 143)
        {
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
        }

        if ($iOldDBVersion < 145)
        {
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
        }

        if ($iOldDBVersion < 146) //Modify surveys table
        {
            upgradeSurveyTimings146();
            // Fix permissions for new feature quick-translation
            try { setTransactionBookmark(); $oDB->createCommand("INSERT into {{survey_permissions}} (sid,uid,permission,read_p,update_p) SELECT sid,owner_id,'translations','1','1' from {{surveys}}")->execute(); echo $modifyoutput; flush();@ob_flush();} catch(Exception $e) { rollBackToTransactionBookmark();}
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>146),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 147)
        {
            addColumn('{{users}}','templateeditormode',"string(7) NOT NULL default 'default'");
            addColumn('{{users}}','questionselectormode',"string(7) NOT NULL default 'default'");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>147),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 148)
        {
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
        }

        if ($iOldDBVersion < 149)
        {
            $aFields = array(
                'id' => 'integer',
                'sid' => 'integer',
                'parameter' => 'string(50)',
                'targetqid' => 'integer',
                'targetsqid' => 'integer'
            );
            $oDB->createCommand()->createTable('{{survey_url_parameters}}',$aFields);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>149),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 150)
        {
            addColumn('{{questions}}','relevance','TEXT');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>150),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 151)
        {
            addColumn('{{groups}}','randomization_group',"string(20) NOT NULL default ''");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>151),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 152)
        {
            $oDB->createCommand()->createIndex('question_attributes_idx3','{{question_attributes}}','attribute');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>152),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 153)
        {
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
        }

        if ($iOldDBVersion < 154)
        {
            $oDB->createCommand()->addColumn('{{groups}}','grelevance',"text");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>154),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 155)
        {
            addColumn('{{surveys}}','googleanalyticsstyle',"string(1)");
            addColumn('{{surveys}}','googleanalyticsapikey',"string(25)");
            try { setTransactionBookmark(); $oDB->createCommand()->renameColumn('{{surveys}}','showXquestions','showxquestions');} catch(Exception $e) { rollBackToTransactionBookmark();}
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>155),"stg_name='DBVersion'");
        }


        if ($iOldDBVersion < 156)
        {
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

            $oDB->createCommand()->dropTable('{{sessions}}');
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
            addColumn('{{surveys_languagesettings}}','surveyls_attributecaptions',"TEXT");
            addColumn('{{surveys}}','sendconfirmation',"string(1) default 'Y'");

            upgradeSurveys156();

            // If a survey has an deleted owner, re-own the survey to the superadmin
            $oDB->schema->refresh();
            Survey::model()->refreshMetaData();
            $surveys = Survey::model();
            $surveys = $surveys->with(array('owner'))->findAll();
            foreach ($surveys as $row)
            {
                if (!isset($row->owner->attributes))
                {
                    Survey::model()->updateByPk($row->sid,array('owner_id'=>1));
                }
            }
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>156),"stg_name='DBVersion'");
            $oTransaction->commit();
            $oTransaction=$oDB->beginTransaction();
        }

        if ($iOldDBVersion < 157)
        {
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
        }

        if ($iOldDBVersion < 158)
        {
            LimeExpressionManager::UpgradeConditionsToRelevance();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>158),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 159)
        {
            alterColumn('{{failed_login_attempts}}', 'ip', "string(40)",false);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>159),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 160)
        {
            alterLanguageCode('it','it-informal');
            alterLanguageCode('it-formal','it');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>160),"stg_name='DBVersion'");
        }
        if ($iOldDBVersion < 161)
        {
            addColumn('{{survey_links}}','date_invited','datetime NULL default NULL');
            addColumn('{{survey_links}}','date_completed','datetime NULL default NULL');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>161),"stg_name='DBVersion'");
        }
        if ($iOldDBVersion < 162)
        {
            /* Fix participant db types */
            alterColumn('{{participant_attribute}}', 'value', "text", false);
            alterColumn('{{participant_attribute_names_lang}}', 'attribute_name', "string(255)", false);
            alterColumn('{{participant_attribute_values}}', 'value', "text", false);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>162),"stg_name='DBVersion'");
        }
        if ($iOldDBVersion < 163)
        {
            //Replace  by <script type="text/javascript" src="{TEMPLATEURL}template.js"></script> by {TEMPLATEJS}

            $replacedTemplate=replaceTemplateJS();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>163),"stg_name='DBVersion'");

        }

        if ($iOldDBVersion < 164)
        {
            upgradeTokens148(); // this should have bee done in 148 - that's why it is named this way
            // fix survey tables for missing or incorrect token field
            upgradeSurveyTables164();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>164),"stg_name='DBVersion'");

            // Not updating settings table as upgrade process takes care of that step now
        }

        if ($iOldDBVersion < 165)
        {
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
        }

        if ($iOldDBVersion < 166)
        {
            $oDB->createCommand()->renameTable('{{survey_permissions}}', '{{permissions}}');
            dropPrimaryKey('permissions');
            alterColumn('{{permissions}}', 'permission', "string(100)", false);
            $oDB->createCommand()->renameColumn('{{permissions}}','sid','entity_id');
            alterColumn('{{permissions}}', 'entity_id', "string(100)", false);
            addColumn('{{permissions}}','entity',"string(50)");
            $oDB->createCommand("update {{permissions}} set entity='survey'")->query();
            addColumn('{{permissions}}','id','pk');
            $oDB->createCommand()->createIndex('idxPermissions','{{permissions}}','entity_id,entity,permission,uid',true);

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
        }

        if ($iOldDBVersion < 167)
        {
            addColumn('{{surveys_languagesettings}}', 'attachments', 'text');
            addColumn('{{users}}', 'created', 'datetime');
            addColumn('{{users}}', 'modified', 'datetime');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>167),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 168)
        {
            addColumn('{{participants}}', 'created', 'datetime');
            addColumn('{{participants}}', 'modified', 'datetime');
            addColumn('{{participants}}', 'created_by', 'integer');
            $oDB->createCommand('update {{participants}} set created_by=owner_uid')->query();
            alterColumn('{{participants}}', 'created_by', "integer", false);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>168),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 169)
        {
            // Add new column for question index options.
            addColumn('{{surveys}}', 'questionindex', 'integer not null default 0');
            // Set values for existing surveys.
            $oDB->createCommand("update {{surveys}} set questionindex = 0 where allowjumps <> 'Y'")->query();
            $oDB->createCommand("update {{surveys}} set questionindex = 1 where allowjumps = 'Y'")->query();

            // Remove old column.
            dropColumn('{{surveys}}', 'allowjumps');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>169),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 170)
        {
            // renamed advanced attributes fields dropdown_dates_year_min/max
            $oDB->createCommand()->update('{{question_attributes}}',array('attribute'=>'date_min'),"attribute='dropdown_dates_year_min'");
            $oDB->createCommand()->update('{{question_attributes}}',array('attribute'=>'date_max'),"attribute='dropdown_dates_year_max'");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>170),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 171)
        {
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
        }
        if ($iOldDBVersion < 172)
        {
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
        }
        if ($iOldDBVersion < 173)
        {
            addColumn('{{participant_attribute_names}}','defaultname',"string(50) NOT NULL default ''");
            upgradeCPDBAttributeDefaultNames173();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>173),"stg_name='DBVersion'");
        }
        if ($iOldDBVersion < 174)
        {
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
        }
        if ($iOldDBVersion < 175)
        {
            switch (Yii::app()->db->driverName){
                case 'pgsql':
                    // Special treatment for Postgres as it is too dumb to convert a boolean to a number without explicit being told to do so
                    alterColumn('{{plugins}}', 'active', "INTEGER USING (active::integer)", false);
                    break;
                default:
                    alterColumn('{{plugins}}', 'active', "integer",false,'0');
            }
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>175),"stg_name='DBVersion'");
        }
        if ($iOldDBVersion < 176)
        {
            upgradeTokens176();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>176),"stg_name='DBVersion'");
        }
        if ($iOldDBVersion < 177)
        {
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
        }
        if ($iOldDBVersion < 178)
        {
            if (Yii::app()->db->driverName=='mysql' || Yii::app()->db->driverName=='mysqli')
            {
                modifyPrimaryKey('questions', array('qid','language'));
            }
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>178),"stg_name='DBVersion'");
        }
        if ($iOldDBVersion < 179)
        {
            upgradeSurveys177(); // Needs to be run again to make sure
            upgradeTokenTables179();
            alterColumn('{{participants}}', 'email', "string(254)", false);
            alterColumn('{{participants}}', 'firstname', "string(150)", false);
            alterColumn('{{participants}}', 'lastname', "string(150)", false);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>179),"stg_name='DBVersion'");
        }
        if ($iOldDBVersion < 180)
        {
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

        }
        if ($iOldDBVersion < 181)
        {
            upgradeTokenTables181('utf8_bin');
            upgradeSurveyTables181('utf8_bin');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>181),"stg_name='DBVersion'");
        }
        if ($iOldDBVersion < 183)
        {
            upgradeSurveyTables183();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>183),"stg_name='DBVersion'");
        }
        if ($iOldDBVersion < 184)
        {
            fixKCFinder184();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>184),"stg_name='DBVersion'");
        }

        // LS 2.5 table start at 250
        if ($iOldDBVersion < 250)
        {
            createBoxes250();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>250),"stg_name='DBVersion'");
        }

        if ( $iOldDBVersion < 251 )
        {
            upgradeBoxesTable251();

            // Update DBVersion
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>251),"stg_name='DBVersion'");
        }

        if ( $iOldDBVersion < 252 )
        {
            Yii::app()->db->createCommand()->addColumn('{{questions}}','modulename','string');
            // Update DBVersion
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>252),"stg_name='DBVersion'");
        }
        if ( $iOldDBVersion < 253 )
        {
            upgradeSurveyTables253();

            // Update DBVersion
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>253),"stg_name='DBVersion'");
        }
        if ( $iOldDBVersion < 254 )
        {
            upgradeSurveyTables254();
            // Update DBVersion
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>254),"stg_name='DBVersion'");
        }
        if ( $iOldDBVersion < 255 )
        {
            upgradeSurveyTables255();
            // Update DBVersion
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>255),"stg_name='DBVersion'");
        }
        if ( $iOldDBVersion < 256 )
        {
            upgradeTokenTables256();
            alterColumn('{{participants}}', 'email', "text", false);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>256),"stg_name='DBVersion'");
        }

        if ($iOldDBVersion < 257) {
            switch (Yii::app()->db->driverName){
                case 'pgsql':
                    $sSubstringCommand='substr';
                    break;
                default:
                    $sSubstringCommand='substring';
            }
            $oDB->createCommand("UPDATE {{templates}} set folder={$sSubstringCommand}(folder,1,50)")->execute();
            dropPrimaryKey('templates');
            alterColumn('{{templates}}', 'folder', "string(50)", false);
            addPrimaryKey('templates', 'folder');
            dropPrimaryKey('participant_attribute_names_lang');
            alterColumn('{{participant_attribute_names_lang}}', 'lang', "string(20)", false);
            addPrimaryKey('participant_attribute_names_lang', array('attribute_id','lang'));
            //Fixes the collation for the complete DB, tables and columns
            if (Yii::app()->db->driverName=='mysql')
            {
                fixMySQLCollations('utf8mb4','utf8mb4_unicode_ci');
                // Also apply again fixes from DBVersion 181 again for case sensitive token fields
                upgradeSurveyTables181('utf8mb4_bin');
                upgradeTokenTables181('utf8mb4_bin');
            }
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>257),"stg_name='DBVersion'");
        }

        /**
         * Remove adminimageurl from global settings
         */
        if ($iOldDBVersion < 258) {
            Yii::app()->getDb()->createCommand(
                "DELETE FROM {{settings_global}} WHERE stg_name='adminimageurl'"
            )->execute();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>258),"stg_name='DBVersion'");
        }

        /**
         * Add table for notifications
         * @since 2016-08-04
         * @author Olle Haerstedt
         */
        if ($iOldDBVersion < 259) {
            $oDB->createCommand()->createTable('{{notifications}}', array(
                'id' => 'pk',
                'entity' => 'string(15) not null',
                'entity_id' => 'int not null',
                'title' => 'string not null',  // varchar(255) in postgres
                'message' => 'text not null',
                'status' => 'string(15) default \'new\'',
                'importance' => 'int default 1',
                'display_class' => 'string(31) default \'default\'',
                'created' => 'datetime not null',
                'first_read' => 'datetime null'
            ));
            $oDB->createCommand()->createIndex('notif_index', '{{notifications}}', 'entity, entity_id, status', false);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>259),"stg_name='DBVersion'");
        }
        if ($iOldDBVersion < 260) {
            alterColumn('{{participant_attribute_names}}','defaultname',"string(255)",false);
            alterColumn('{{participant_attribute_names_lang}}','attribute_name',"string(255)",false);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>260),"stg_name='DBVersion'");
        }
        // Inform superadmin about update
        $superadmins = User::model()->getSuperAdmins();
        Notification::broadcast(array(
            'title' => gT('Database update'),
            'message' => sprintf(gT('The database has been updated from version %s to version %s.'), $iOldDBVersion, '260')
        ), $superadmins);


        $oTransaction->commit();
        // Activate schema caching
        $oDB->schemaCachingDuration=3600;
        // Load all tables of the application in the schema
        $oDB->schema->getTables();
        // clear the cache of all loaded tables
        $oDB->schema->refresh();
    }
    catch(Exception $e)
    {
        Yii::app()->setConfig('Updating',false);
        $oTransaction->rollback();
        // Activate schema caching
        $oDB->schemaCachingDuration=3600;
        // Load all tables of the application in the schema
        $oDB->schema->getTables();
        // clear the cache of all loaded tables
        $oDB->schema->refresh();
        //echo '<br /><br />'.gT('An non-recoverable error happened during the update. Error details:')."<p>".htmlspecialchars($e->getMessage()).'</p><br />';
        Yii::app()->user->setFlash('error', gT('An non-recoverable error happened during the update. Error details:')."<p>".htmlspecialchars($e->getMessage()).'</p><br />');
        return false;
    }
    fixLanguageConsistencyAllSurveys();
    Yii::app()->setConfig('Updating',false);
    return true;
}


function upgradeTokenTables256()
{
    $surveyidresult = dbGetTablesLike("tokens%");
    $oDB = Yii::app()->getDb();
    if ($surveyidresult)
    {
        foreach ( $surveyidresult as $sTableName )
        {
            try { setTransactionBookmark(); $oDB->createCommand()->dropIndex("idx_lime_{$sTableName}_efl",$sTableName); } catch(Exception $e) { rollBackToTransactionBookmark();}
            alterColumn($sTableName, 'email', "text");
            alterColumn($sTableName, 'firstname', "string(150)");
            alterColumn($sTableName, 'lastname', "string(150)");
        }
    }
}


function upgradeSurveyTables255()
{
    // We delete all the old boxes, and reinsert new ones
    Boxes::model()->deleteAll();

    // Then we recreate them
    $oDB = Yii::app()->db;
    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '1',
        'url'      => 'admin/survey/sa/newsurvey' ,
        'title'    => 'Create survey' ,
        'ico'      => 'add' ,
        'desc'     => 'Create a new survey' ,
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
        'url'      =>  'admin/templates/sa/view',
        'title'    =>  'Template editor',
        'ico'      =>  'templates',
        'desc'     =>  'Edit LimeSurvey templates',
        'page'     =>  'welcome',
        'usergroup' => '-2',
    ));

}

function upgradeSurveyTables254()
{
    Yii::app()->db->createCommand()->dropColumn('{{boxes}}','img');
    Yii::app()->db->createCommand()->addColumn('{{boxes}}','usergroup','integer');
}

function upgradeSurveyTables253()
{
    $oSchema = Yii::app()->db->schema;
    $aTables = dbGetTablesLike("survey\_%");
    foreach ( $aTables as $sTable )
    {
        $oTableSchema=$oSchema->getTable($sTable);
        if (in_array('refurl',$oTableSchema->columnNames))
        {
            alterColumn($sTable,'refurl',"text");
        }
        if (in_array('ipaddr',$oTableSchema->columnNames))
        {
            alterColumn($sTable,'ipaddr',"text");
        }
    }
}

function upgradeBoxesTable251()
{
    Yii::app()->db->createCommand()->addColumn('{{boxes}}','ico','string');
    Yii::app()->db->createCommand()->update('{{boxes}}',array('ico'=>'add',
                                                              'title'=>'Create survey')
                                                              ,"id=1");
    Yii::app()->db->createCommand()->update('{{boxes}}',array('ico'=>'list')
                                                              ,"id=2");
    Yii::app()->db->createCommand()->update('{{boxes}}',array('ico'=>'settings')
                                                              ,"id=3");
    Yii::app()->db->createCommand()->update('{{boxes}}',array('ico'=>'shield')
                                                              ,"id=4");
    Yii::app()->db->createCommand()->update('{{boxes}}',array('ico'=>'label')
                                                              ,"id=5");
    Yii::app()->db->createCommand()->update('{{boxes}}',array('ico'=>'templates')
                                                              ,"id=6");
}

/**
 * Create boxes table
 */
function createBoxes250()
{
    $oDB = Yii::app()->db;
    $oDB->createCommand()->createTable('{{boxes}}',array(
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
        'url'      => 'admin/survey/sa/newsurvey' ,
        'title'    => 'Create survey' ,
        'img'      => 'add.png' ,
        'desc'     => 'Create a new survey' ,
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
        'url'      =>  'admin/templates/sa/view',
        'title'    =>  'Template editor',
        'img'      =>  'templates.png',
        'desc'     =>  'Edit LimeSurvey templates',
        'page'     =>  'welcome',
    ));
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


/**
 * @param string $sMySQLCollation
 */
function upgradeSurveyTables181($sMySQLCollation)
{
    $oDB = Yii::app()->db;
    $oSchema = Yii::app()->db->schema;
    if(Yii::app()->db->driverName!='pgsql')
    {
        $aTables = dbGetTablesLike("survey\_%");
        if ($aTables)
        {
            foreach ( $aTables as $sTableName )
            {
                $oTableSchema=$oSchema->getTable($sTableName);
                if (!in_array('token',$oTableSchema->columnNames)) continue; // No token field in this table
                switch (Yii::app()->db->driverName){
                    case 'sqlsrv':
                    case 'dblib':
                    case 'mssql': dropSecondaryKeyMSSQL('token',$sTableName);
                        alterColumn($sTableName, 'token', "string(35) COLLATE SQL_Latin1_General_CP1_CS_AS");
                        $oDB->createCommand()->createIndex("idx_{$sTableName}_".rand(1,50000),  $sTableName,'token');
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

/**
 * @param string $sMySQLCollation
 */
function upgradeTokenTables181($sMySQLCollation)
{
    $oDB = Yii::app()->db;
    $oSchema = Yii::app()->db->schema;
    if(Yii::app()->db->driverName!='pgsql')
    {
        $aTables = dbGetTablesLike("tokens%");
        if (! empty($aTables))
        {
            foreach ( $aTables as $sTableName )
            {
                switch (Yii::app()->db->driverName){
                    case 'sqlsrv':
                    case 'dblib':
                    case 'mssql': dropSecondaryKeyMSSQL('token',$sTableName);
                        alterColumn($sTableName, 'token', "string(35) COLLATE SQL_Latin1_General_CP1_CS_AS");
                        $oDB->createCommand()->createIndex("idx_{$sTableName}_".rand(1,50000),  $sTableName,'token');
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
    $arSurveys = Survey::model()->findAll();
    // Fix any active token tables
    foreach ( $arSurveys as $arSurvey )
    {
        $sTokenTableName='tokens_'.$arSurvey->sid;
        if (tableExists($sTokenTableName))
        {                                        
            $aColumnNames=$aColumnNamesIterator=$oDB->schema->getTable('{{'.$sTokenTableName.'}}')->columnNames;
            $aAttributes = $arSurvey->tokenAttributes;
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
            Survey::model()->updateByPk($arSurvey->sid, array('attributedescriptions' => serialize($aAttributes)));
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
    $sSurveyQuery = "SELECT sid FROM {{surveys}}";
    $oSurveyResult = dbExecuteAssoc($sSurveyQuery);
    $aAllAttributes=questionAttributes(true);
    foreach ( $oSurveyResult->readAll()  as $aSurveyRow)
    {
        $iSurveyID=$aSurveyRow['sid'];
        $aLanguages=array_merge(array(Survey::model()->findByPk($iSurveyID)->language), Survey::model()->findByPk($iSurveyID)->additionalLanguages);

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
    global $modifyoutputt;
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
    global $modifyoutput;

    $aQIDReplacements=array();
    $answerquery = "select a.*, q.sid, q.gid from {{answers}} a,{{questions}} q where a.qid=q.qid and q.type in ('L','O','!') and a.default_value='Y'";
    $answerresult = Yii::app()->getDb()->createCommand($answerquery)->queryAll();
    foreach ( $answerresult as $row )
    {
        modifyDatabase("","INSERT INTO {{defaultvalues}} (qid, scale_id,language,specialtype,defaultvalue) VALUES ({$row['qid']},0,".dbQuoteAll($row['language']).",'',".dbQuoteAll($row['code']).")"); echo $modifyoutput; flush();@ob_flush();
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
            modifyDatabase("","INSERT INTO {{defaultvalues}} (qid, sqid, scale_id,language,specialtype,defaultvalue) VALUES ({$row['qid']},{$iSaveSQID},0,".dbQuoteAll($row['language']).",'','Y')"); echo $modifyoutput; flush();@ob_flush();
        }
    }
    // Sanitize data
    if (Yii::app()->db->driverName=='pgsql')
    {
        modifyDatabase("","delete from {{answers}} USING {{questions}} WHERE {{answers}}.qid={{questions}}.qid AND {{questions}}.type in ('1','F','H','M','P','W','Z')"); echo $modifyoutput; flush();@ob_flush();
    }
    else
    {
        modifyDatabase("","delete {{answers}} from {{answers}} LEFT join {{questions}} ON {{answers}}.qid={{questions}}.qid where {{questions}}.type in ('1','F','H','M','P','W','Z')"); echo $modifyoutput; flush();@ob_flush();
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
            modifyDatabase("","INSERT INTO {{answers}} (qid, code, answer, sortorder, language, assessment_value) VALUES ({$row['qid']},".dbQuoteAll($lrow['code']).",".dbQuoteAll($lrow['title']).",{$lrow['sortorder']},".dbQuoteAll($lrow['language']).",{$lrow['assessment_value']})"); echo $modifyoutput; flush();@ob_flush();
            //$labelids[]
        }
        if ($row['type']=='1')
        {
            $labelquery="Select * from {{labels}} where lid={$row['lid1']} and language=".dbQuoteAll($row['language']);
            $labelresult = Yii::app()->getDb()->createCommand($labelquery)->queryAll();
            foreach ( $labelresult as $lrow )
            {
                modifyDatabase("","INSERT INTO {{answers}} (qid, code, answer, sortorder, language, scale_id, assessment_value) VALUES ({$row['qid']},".dbQuoteAll($lrow['code']).",".dbQuoteAll($lrow['title']).",{$lrow['sortorder']},".dbQuoteAll($lrow['language']).",1,{$lrow['assessment_value']})"); echo $modifyoutput; flush();@ob_flush();
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
    modifyDatabase("",$updatequery); echo $modifyoutput; flush();@ob_flush();
    $updatequery = "update {{questions}} set type='L' where type='Z'";
    modifyDatabase("",$updatequery); echo $modifyoutput; flush();@ob_flush();

    // Now move all non-standard templates to the /upload dir
    $usertemplaterootdir=Yii::app()->getConfig("usertemplaterootdir");
    $standardtemplaterootdir = Yii::app()->getConfig('standardtemplaterootdir');

    if (!$usertemplaterootdir) {die("getTemplateList() no template directory");}
    if ($handle = opendir($standardtemplaterootdir))
    {
        while (false !== ($file = readdir($handle)))
        {
            if (!is_file("$standardtemplaterootdir/$file") && $file != "." && $file != ".." && $file!=".svn" && !isStandardTemplate($file))
            {
                if (!rename($standardtemplaterootdir.DIRECTORY_SEPARATOR.$file,$usertemplaterootdir.DIRECTORY_SEPARATOR.$file))
                {
                    echo "There was a problem moving directory '".$standardtemplaterootdir.DIRECTORY_SEPARATOR.$file."' to '".$usertemplaterootdir.DIRECTORY_SEPARATOR.$file."' due to missing permissions. Please do this manually.<br />";
                };
            }
        }
        closedir($handle);
    }

}


function upgradeQuestionAttributes142()
{
    global $modifyoutput;
    $attributequery="Select qid from {{question_attributes}} where attribute='exclude_all_other'  group by qid having count(qid)>1 ";
    $questionids = Yii::app()->getDb()->createCommand($attributequery)->queryRow();
    if(!is_array($questionids)) { return "Database Error"; }
    else
    {
        foreach ($questionids as $questionid)
        {
            //Select all affected question attributes
            $attributevalues=Yii::app()->getDb()->createCommand("SELECT value from {{question_attributes}} where attribute='exclude_all_other' and qid=".$questionid)->queryColumn();
            modifyDatabase("","delete from {{question_attributes}} where attribute='exclude_all_other' and qid=".$questionid); echo $modifyoutput; flush();@ob_flush();
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
    global $modifyoutput;
    $aTables = dbGetTablesLike("tokens%");
    foreach ( $aTables as $sTable )
    {
        addColumn($sTable,'validfrom',"datetime");
        addColumn($sTable,'validuntil',"datetime");
    }
}

// Add the reminders tracking fields
function upgradeTokens128()
{
    global $modifyoutput;
    $aTables = dbGetTablesLike("tokens%");
    foreach ( $aTables as $sTable )
    {
        addColumn($sTable,'remindersent',"string(17) DEFAULT 'N'");
        addColumn($sTable,'remindercount',"integer DEFAULT '0'");
    }
}


/**
 * @param string $sEncoding
 * @param string $sCollation
 */
function fixMySQLCollations($sEncoding, $sCollation)
{
    $surveyidresult = dbGetTablesLike("%");
    if ($surveyidresult)
    {
        foreach ( $surveyidresult as $sTableName )
        {
            try{
                Yii::app()->getDb()->createCommand("ALTER TABLE {$sTableName} CONVERT TO CHARACTER SET {$sEncoding} COLLATE {$sCollation};")->execute();
            } catch(Exception $e){
                // There are some big survey response tables that cannot be converted because the new charset probably uses
                // more bytes per character than the old one - we just leave them as they are for now.
            };
        }
    }
    $sDatabaseName=getDBConnectionStringProperty('dbname');
    Yii::app()->getDb()->createCommand("ALTER DATABASE `$sDatabaseName` DEFAULT CHARACTER SET {$sEncoding} COLLATE {$sCollation};");
}

function upgradeSurveyTables126()
{
    $surveyidquery = "SELECT sid FROM {{surveys}} WHERE active='Y' and datestamp='Y'";
    $surveyidresult = Yii::app()->getDb()->createCommand($surveyidquery)->queryAll();
    if (!$surveyidresult) {return "Database Error";}
    else
    {
        foreach ( $surveyidresult as $sv )
        {
            addColumn('{{survey_'.$sv['sid'].'}}','startdate','datetime');
        }
    }
}

function upgradeTokenTables126()
{
    global $modifyoutput;
    $aTables = dbGetTablesLike("tokens%");
    foreach ( $aTables as $sTable )
    {
        Yii::app()->getDb()->createCommand()->alterColumn($sTable,'token',"string(15)");
        addColumn($sTable,'emailstatus',"string(300) NOT NULL DEFAULT 'OK'");
    }
}

/**
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

/**
 * @param string $sTablename
 */
function addPrimaryKey($sTablename, $aColumns)
{
    return Yii::app()->db->createCommand()->addPrimaryKey('PK_'.$sTablename.'_'.randomChars(12,'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'), '{{'.$sTablename.'}}', $aColumns);
}

/**
* Modifies a primary key in one command  - this is only tested on MySQL
*
* @param string $sTablename The table name
* @param string[] $aColumns Column names to be in the new key
*/
function modifyPrimaryKey($sTablename, $aColumns)
{
    Yii::app()->db->createCommand("ALTER TABLE {{".$sTablename."}} DROP PRIMARY KEY, ADD PRIMARY KEY (".implode(',',$aColumns).")")->execute();
}

/**
 * @param string $sTablename
 */
function dropPrimaryKey($sTablename)
{
    switch (Yii::app()->db->driverName){
        case 'mysql':
            $sQuery="ALTER TABLE {{".$sTablename."}} DROP PRIMARY KEY";
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
            if ($primarykey!==false)
            {
                $sQuery="ALTER TABLE {{".$sTablename."}} DROP CONSTRAINT ".$primarykey[0];
                Yii::app()->db->createCommand($sQuery)->execute();
            }
            break;
        default: die('Unknown database type');
    }

    // find out the constraint name of the old primary key
}

function fixLanguageConsistencyAllSurveys()
{
    $surveyidquery = "SELECT sid,additional_languages FROM ".dbQuoteID('{{surveys}}');
    $surveyidresult = Yii::app()->db->createCommand($surveyidquery)->queryAll();
    foreach ( $surveyidresult as $sv )
    {
        fixLanguageConsistency($sv['sid'],$sv['additional_languages']);
    }
}

/**
 * @param string $sFieldType
 */
function alterColumn($sTable, $sColumn, $sFieldType, $bAllowNull=true, $sDefault='NULL')
{
    $oDB = Yii::app()->db;
    switch (Yii::app()->db->driverName){
        case 'mysql':
        case 'mysqli':
            $sType=$sFieldType;
            if ($bAllowNull!==true)
            {
                $sType.=' NOT NULL';
            }
            if ($sDefault!='NULL')
            {
                $sType.=" DEFAULT '{$sDefault}'";
            }
            $oDB->createCommand()->alterColumn($sTable,$sColumn,$sType);
            break;
        case 'dblib':
        case 'sqlsrv':
        case 'mssql':
            dropDefaultValueMSSQL($sColumn,$sTable);
            $sType=$sFieldType;
            if ($bAllowNull!=true && $sDefault!='NULL')
            {
                $oDB->createCommand("UPDATE {$sTable} SET [{$sColumn}]='{$sDefault}' where [{$sColumn}] is NULL;")->execute();
            }
            if ($bAllowNull!=true)
            {
                $sType.=' NOT NULL';
            }
            else
            {
                $sType.=' NULL';
            }
            $oDB->createCommand()->alterColumn($sTable,$sColumn,$sType);
            if ($sDefault!='NULL')
            {
                $oDB->createCommand("ALTER TABLE {$sTable} ADD default '{$sDefault}' FOR [{$sColumn}];")->execute();
            }
            break;
        case 'pgsql':
            $sType=$sFieldType;
            $oDB->createCommand()->alterColumn($sTable,$sColumn,$sType);
            try{ $oDB->createCommand("ALTER TABLE {$sTable} ALTER COLUMN {$sColumn} DROP DEFAULT")->execute();} catch(Exception $e) {};
            try{ $oDB->createCommand("ALTER TABLE {$sTable} ALTER COLUMN {$sColumn} DROP NOT NULL")->execute();} catch(Exception $e) {};

            if ($bAllowNull!=true)
            {
                $oDB->createCommand("ALTER TABLE {$sTable} ALTER COLUMN {$sColumn} SET NOT NULL")->execute();
            }
            if ($sDefault!='NULL')
            {
                $oDB->createCommand("ALTER TABLE {$sTable} ALTER COLUMN {$sColumn} SET DEFAULT '{$sDefault}'")->execute();
            }
            $oDB->createCommand()->alterColumn($sTable,$sColumn,$sType);
            break;
        default: die('Unknown database type');
    }
}


/**
 * @param string $sTableName
 * @param string $sColumnName
 */
function dropColumn($sTableName, $sColumnName)
{
    if (Yii::app()->db->getDriverName()=='mssql' || Yii::app()->db->getDriverName()=='sqlsrv' || Yii::app()->db->getDriverName()=='dblib')
    {
        dropDefaultValueMSSQL($sColumnName,$sTableName);
    }
    Yii::app()->db->createCommand()->dropColumn($sTableName,$sColumnName);
}





/**
 * @param string $sType
 */
function addColumn($sTableName, $sColumn, $sType)
{
    Yii::app()->db->createCommand()->addColumn($sTableName,$sColumn,$sType);
}


function setTransactionBookmark($sBookmark='limesurvey')
{
    if (Yii::app()->db->driverName=='pgsql')
    {
        Yii::app()->db->createCommand("SAVEPOINT {$sBookmark};")->execute();
    }
}

function rollBackToTransactionBookmark($sBookmark='limesurvey')
{
    if (Yii::app()->db->driverName=='pgsql')
    {
        Yii::app()->db->createCommand("ROLLBACK TO SAVEPOINT {$sBookmark};")->execute();
    }
}


function dropDefaultValueMSSQL($fieldname, $tablename)
{
    // find out the name of the default constraint
    // Did I already mention that this is the most suckiest thing I have ever seen in MSSQL database?
    $dfquery ="SELECT c_obj.name AS constraint_name
    FROM sys.sysobjects AS c_obj INNER JOIN
    sys.sysobjects AS t_obj ON c_obj.parent_obj = t_obj.id INNER JOIN
    sys.sysconstraints AS con ON c_obj.id = con.constid INNER JOIN
    sys.syscolumns AS col ON t_obj.id = col.id AND con.colid = col.colid
    WHERE (c_obj.xtype = 'D') AND (col.name = '$fieldname') AND (t_obj.name='{$tablename}')";
    $defaultname = Yii::app()->getDb()->createCommand($dfquery)->queryRow();
    if ($defaultname!=false)
    {
        Yii::app()->db->createCommand("ALTER TABLE {$tablename} DROP CONSTRAINT {$defaultname['constraint_name']}")->execute();
    }
}

/**
* This function drops a unique Key of an MSSQL database field by using the name of the field it lies upon and the table name
*
* @param string $sFieldName
* @param string $sTableName
*/
function dropUniqueKeyMSSQL($sFieldName, $sTableName)
{
    $sQuery ="select TC.Constraint_Name, CC.Column_Name from information_schema.table_constraints TC
    inner join information_schema.constraint_column_usage CC on TC.Constraint_Name = CC.Constraint_Name
    where TC.constraint_type = 'Unique' and Column_name='{$sFieldName}' and TC.TABLE_NAME='{$sTableName}'";
    $aUniqueKeyName = Yii::app()->getDb()->createCommand($sQuery)->queryRow();
    if ($aUniqueKeyName!=false)
    {
        Yii::app()->getDb()->createCommand("ALTER TABLE {$sTableName} DROP CONSTRAINT {$aUniqueKeyName['Constraint_Name']}")->execute();
    }
}

/**
 * @param string $sFieldName
 */
function dropSecondaryKeyMSSQL($sFieldName, $sTableName)
{
    $oDB = Yii::app()->getDb();
    $sQuery="select
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
    if ($aKeyName!=false)
    {
        try { $oDB->createCommand()->dropIndex($aKeyName,$sTableName); } catch(Exception $e) { }
    }
}

function replaceTemplateJS(){
    $usertemplaterootdir=Yii::app()->getConfig("usertemplaterootdir");

    if (!$usertemplaterootdir) {return false;}
    $countstartpage=0;
    $counterror=0;
    $errortemplate=array();
    if ($handle = opendir($usertemplaterootdir))
    {
        while (false !== ($file = readdir($handle)))
        {
            if ($file != "." && $file != ".." && is_dir("{$usertemplaterootdir}/{$file}")) {
                $fname = "$usertemplaterootdir/$file/startpage.pstpl";
                if (is_file($fname))
                {
                    if(is_writable($fname)){
                        $fhandle = fopen($fname,"r");
                        $content = fread($fhandle,filesize($fname));
                        $content = str_replace("<script type=\"text/javascript\" src=\"{TEMPLATEURL}template.js\"></script>", "{TEMPLATEJS}", $content);
                        $fhandle = fopen($fname,"w");
                        fwrite($fhandle,$content);
                        fclose($fhandle);
                        if(strpos($content, "{TEMPLATEJS}")===false)
                        {
                            $counterror++;
                            $errortemplate[]=$file;
                        }
                    }else{
                        $counterror++;
                    }
                    $countstartpage++;
                }
            }
        }
        closedir($handle);
    }
    if($counterror)
    {
        echo gT("Some user templates can not be updated, please add the placeholder {TEMPLATEJS} in your startpage.pstpl manually.");
        echo "<br />";
        echo gT("Template(s) to be verified :");
        echo implode(",",$errortemplate);
    }
    else
    {
        if($countstartpage){
            echo sprintf(gT("All %s user templates updated."),$countstartpage);
        }
    }
    if($counterror){
        return false;
    }else{
        return $countstartpage;
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
