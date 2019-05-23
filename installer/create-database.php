<?php

function createDatabase($oDB){
    /**
    * Populate the database for a limesurvey installation
    * Rules:
    * - Use the provided addColumn, alterColumn, dropPrimaryKey etc. functions where applicable - they ensure cross-DB compatibility
    * - Never use foreign keys
    * - Do not use fancy database field types (like mediumtext, timestamp, etc) - only use the ones provided by Yii
    * - If you want to use database functions make sure they exist on all three supported database types
    * - Always prefix key/index names by using curly brackets {{ }}*
    */

    ////// Current database version: //////
    $version = require(\Yii::app()->getBasePath() . '/config/version.php');
    $databaseCurrentVersion = $version['dbversionnumber'];
    ///////////////////////////////////////

    Yii::app()->loadHelper('database');
    Yii::app()->loadHelper('update.updatedb');

    ///// Load DefaultData Helper
    // $oDB                        = Yii::app()->getDb();

    $oTransaction = $oDB->beginTransaction();
    try{
        //answers table
        $oDB->createCommand()->createTable('{{answers}}', array(
            'qid' => 'integer NOT NULL',
            'code' => 'string(5) NOT NULL',
            'answer' => 'text NOT NULL',
            'sortorder' => 'integer NOT NULL',
            'assessment_value' => 'integer NOT NULL DEFAULT 0',
            'language' => "string(20) NOT NULL DEFAULT 'en'",
            'scale_id' => 'integer NOT NULL DEFAULT 0',
        ));

        $oDB->createCommand()->addPrimaryKey('{{answers_pk}}', '{{answers}}', ['qid', 'code', 'language', 'scale_id'], false);
        $oDB->createCommand()->createIndex('{{answers_idx2}}', '{{answers}}', 'sortorder', false);

        // assessements
        $oDB->createCommand()->createTable('{{assessments}}', array(
            'id' =>         'autoincrement',
            'sid' =>        'integer NOT NULL DEFAULT 0',
            'scope' =>      'string(5) NOT NULL'	,
            'gid' =>        'integer NOT NULL DEFAULT 0',
            'name' =>       'text NOT NULL',
            'minimum' =>    'string(50) NOT NULL',
            'maximum' =>    'string(50) NOT NULL',
            'message' =>    'text NOT NULL',
            'language' =>   "string(20) NOT NULL DEFAULT 'en'",
            'composite_pk' => array('id', 'language')
        ));

        $oDB->createCommand()->createIndex('{{assessments_idx2}}', '{{assessments}}', 'sid', false);
        $oDB->createCommand()->createIndex('{{assessments_idx3}}', '{{assessments}}', 'gid', false);

        // boxes
        $oDB->createCommand()->createTable('{{boxes}}', array(
            'id' => "pk",
            'position' => "integer NULL ",
            'url' => "text NOT NULL ",
            'title' => "text NOT NULL ",
            'ico' => "string(255) NULL ",
            'desc' => "text NOT NULL ",
            'page' => "text NOT NULL ",
            'usergroup' => "integer NOT NULL "
        ));
        
        foreach( $boxesData=LsDefaultDataSets::getBoxesData() as $box){
            $oDB->createCommand()->insert("{{boxes}}", $box);
        }
       
        // conditions
        $oDB->createCommand()->createTable('{{conditions}}', array(
            'cid' => 'pk',
            'qid' => "integer NOT NULL default '0'",
            'cqid' => "integer NOT NULL default '0'",
            'cfieldname' => "string(50) NOT NULL default ''",
            'method' => "string(5) NOT NULL default ''",
            'value' => "string(255) NOT NULL default ''",
            'scenario' => "integer NOT NULL default 1"
        ));
        $oDB->createCommand()->createIndex('{{conditions_idx}}', '{{conditions}}', 'qid', false);
        $oDB->createCommand()->createIndex('{{conditions_idx3}}', '{{conditions}}', 'cqid', false);


        // defaultvalues
        $oDB->createCommand()->createTable('{{defaultvalues}}', array(
            'qid' =>  "integer NOT NULL default '0'",
            'scale_id' =>  "integer NOT NULL default '0'",
            'sqid' =>  "integer NOT NULL default '0'",
            'language' =>  "string(20) NOT NULL",
            'specialtype' =>  "string(20) NOT NULL default ''",
            'defaultvalue' =>  "text",
        ));

        $oDB->createCommand()->addPrimaryKey('{{defaultvalues_pk}}', '{{defaultvalues}}', ['qid', 'specialtype', 'language', 'scale_id', 'sqid'], false);

        // expression_errors
        $oDB->createCommand()->createTable('{{expression_errors}}', array(
            'id' =>  "pk",
            'errortime' =>  "string(50) NULL",
            'sid' =>  "integer NULL",
            'gid' =>  "integer NULL",
            'qid' =>  "integer NULL",
            'gseq' =>  "integer NULL",
            'qseq' =>  "integer NULL",
            'type' =>  "string(50)",
            'eqn' =>  "text",
            'prettyprint' =>  "text",
        ));

        // failed_login_attempts
        $oDB->createCommand()->createTable('{{failed_login_attempts}}', array(
            'id' =>  "pk",
            'ip' =>  "string(40) NOT NULL",
            'last_attempt' =>  "string(20) NOT NULL",
            'number_attempts' =>  "integer NOT NULL",
        ));


        $oDB->createCommand()->createTable('{{groups}}', array(
            'gid' =>  "autoincrement",
            'sid' =>  "integer NOT NULL default '0'",
            'group_name' =>  "string(100) NOT NULL default ''",
            'group_order' =>  "integer NOT NULL default '0'",
            'description' =>  "text",
            'language' =>  "string(20) default 'en' NOT NULL",
            'randomization_group' =>  "string(20) NOT NULL default ''",
            'grelevance' =>  "text NULL",
            'composite_pk' => array('gid', 'language')
        ));
        $oDB->createCommand()->createIndex('{{idx1_groups}}', '{{groups}}', 'sid', false);
        $oDB->createCommand()->createIndex('{{idx2_groups}}', '{{groups}}', 'group_name', false);
        $oDB->createCommand()->createIndex('{{idx3_groups}}', '{{groups}}', 'language', false);

        // labels
        $oDB->createCommand()->createTable('{{labels}}', array(
            'id' =>  "pk",
            'lid' =>  "integer NOT NULL DEFAULT 0",
            'code' =>  "string(5) NOT NULL default ''",
            'title' =>  "text",
            'sortorder' =>  "integer NOT NULL",
            'language' =>  "string(20) NOT NULL DEFAULT 'en'",
            'assessment_value' =>  "integer NOT NULL default '0'",
        ));

        $oDB->createCommand()->createIndex('{{idx1_labels}}', '{{labels}}', 'code', false);
        $oDB->createCommand()->createIndex('{{idx2_labels}}', '{{labels}}', 'sortorder', false);
        $oDB->createCommand()->createIndex('{{idx3_labels}}', '{{labels}}', 'language', false);
        $oDB->createCommand()->createIndex('{{idx4_labels}}', '{{labels}}', ['lid','sortorder','language'], false);


        // labelsets
        $oDB->createCommand()->createTable('{{labelsets}}', array(
            'lid' => 'pk',
            'label_name' =>  "string(100) NOT NULL DEFAULT ''",
            'languages' =>  "string(200) DEFAULT 'en'",
        ));


        // notifications
        $oDB->createCommand()->createTable('{{notifications}}', array(
            'id' =>  "pk",
            'entity' =>  "string(15) NOT NULL ",
            'entity_id' =>  "integer NOT NULL",
            'title' =>  "string(255) NOT NULL",
            'message' =>  "text NOT NULL",
            'status' =>  "string(15) NOT NULL DEFAULT 'new' ",
            'importance' =>  "integer NOT NULL DEFAULT 1",
            'display_class' =>  "string(31) DEFAULT 'default' ",
            'hash' =>  "string(64)",
            'created' =>  "datetime",
            'first_read' =>  "datetime",
        ));

        $oDB->createCommand()->createIndex('{{notifications_pk}}', '{{notifications}}', ['entity', 'entity_id', 'status'], false);
        $oDB->createCommand()->createIndex('{{idx1_notifications}}', '{{notifications}}', 'hash', false);


        //  participants
        $oDB->createCommand()->createTable('{{participants}}', array(
            'participant_id' =>  "string(50) NOT NULL",
            'firstname' =>  "string(150) NULL",
            'lastname' =>  "string(150) NULL",
            'email' =>  "text",
            'language' =>  "string(40) NULL",
            'blacklisted' =>  "string(1) NOT NULL",
            'owner_uid' =>  "integer NOT NULL",
            'created_by' =>  "integer NOT NULL",
            'created' =>  "datetime",
            'modified' =>  "datetime",
        ));

        $oDB->createCommand()->addPrimaryKey('{{participant_pk}}', '{{participants}}', 'participant_id', false);
        $oDB->createCommand()->createIndex('{{idx1_participants}}', '{{participants}}', 'firstname', false);
        $oDB->createCommand()->createIndex('{{idx2_participants}}', '{{participants}}', 'lastname', false);
        $oDB->createCommand()->createIndex('{{idx3_participants}}', '{{participants}}', 'language', false);


        // participant_attribute
        $oDB->createCommand()->createTable('{{participant_attribute}}', array(
            'participant_id' =>  "string(50) NOT NULL",
            'attribute_id' =>  "integer NOT NULL",
            'value' =>  "text NOT NULL",
        ));

        $oDB->createCommand()->addPrimaryKey('{{participant_attribute_pk}}', '{{participant_attribute}}', ['participant_id', 'attribute_id']);


        // participant_attribute_names_lang
        $oDB->createCommand()->createTable('{{participant_attribute_names_lang}}', array(
            'attribute_id' =>  "integer NOT NULL",
            'attribute_name' =>  "string(255) NOT NULL",
            'lang' =>  "string(20) NOT NULL",
        ));

        $oDB->createCommand()->addPrimaryKey('{{participant_attribute_names_lang_pk}}', '{{participant_attribute_names_lang}}', ['attribute_id', 'lang']);



        // participant_attribute_names
        $oDB->createCommand()->createTable('{{participant_attribute_names}}', array(
            'attribute_id' =>  "autoincrement",
            'attribute_type' =>  "string(4) NOT NULL",
            'defaultname' =>  "string(255) NOT NULL",
            'visible' =>  "string(5) NOT NULL",
            'composite_pk' => array('attribute_id', 'attribute_type')
        ));

        $oDB->createCommand()->createIndex('{{idx_participant_attribute_names}}', '{{participant_attribute_names}}', ['attribute_id', 'attribute_type']);


        //participant_attribute_values
        $oDB->createCommand()->createTable('{{participant_attribute_values}}', array(
            'value_id' => "pk",
            'attribute_id' => "integer NOT NULL",
            'value' => "text NOT NULL",
        ));



        //participant_shares
        $oDB->createCommand()->createTable('{{participant_shares}}', array(
            'participant_id' =>  "string(50) NOT NULL",
            'share_uid' =>  "integer NOT NULL",
            'date_added' =>  "datetime NOT NULL",
            'can_edit' =>  "string(5) NOT NULL",
        ));

        $oDB->createCommand()->addPrimaryKey('{{participant_shares_pk}}', '{{participant_shares}}', ['participant_id', 'share_uid'], false);


        // permissions
        $oDB->createCommand()->createTable('{{permissions}}', array(
            'id' =>  "pk",
            'entity' =>  "string(50) NOT NULL",
            'entity_id' =>  "integer NOT NULL",
            'uid' =>  "integer NOT NULL",
            'permission' =>  "string(100) NOT NULL",
            'create_p' =>  "integer NOT NULL default 0",
            'read_p' =>  "integer NOT NULL default 0",
            'update_p' =>  "integer NOT NULL default 0",
            'delete_p' =>  "integer NOT NULL default 0",
            'import_p' =>  "integer NOT NULL default 0",
            'export_p' =>  "integer NOT NULL default 0",
        ));

        $oDB->createCommand()->createIndex('{{idx1_permissions}}', '{{permissions}}', ['entity_id', 'entity', 'permission', 'uid'], true);


        // plugins
        $oDB->createCommand()->createTable('{{plugins}}', array(
            'id' =>  "pk",
            'name' =>  "string(50) NOT NULL",
            'active' =>  "integer NOT NULL default 0",
            'version' =>  "string(32) NULL",
        ));


        // plugin_settings
        $oDB->createCommand()->createTable('{{plugin_settings}}', array(
            'id' => "pk",
            'plugin_id' => "integer NOT NULL",
            'model' => "string(50) NULL",
            'model_id' => "integer NULL",
            'key' => "string(50) NOT NULL",
            'value' => "text NULL",
        ));


        // questions
        $oDB->createCommand()->createTable('{{questions}}', array(
            'qid' =>  "autoincrement",
            'parent_qid' =>  "integer NOT NULL default '0'",
            'sid' =>  "integer NOT NULL default '0'",
            'gid' =>  "integer NOT NULL default '0'",
            'type' =>  "string(1) NOT NULL default 'T'",
            'title' =>  "string(20) NOT NULL default ''",
            'question' =>  "text NOT NULL",
            'preg' =>  "text",
            'help' =>  "text",
            'other' =>  "string(1) NOT NULL default 'N'",
            'mandatory' =>  "string(1) NULL",
            'question_order' =>  "integer NOT NULL",
            'language' =>  "string(20) default 'en' NOT NULL",
            'scale_id' =>  "integer NOT NULL default '0'",
            'same_default' =>  "integer NOT NULL default '0'",
            'relevance' =>  "text",
            'modulename' =>  "string(255) NULL",
            'composite_pk' => array('qid', 'language')
        ));

        $oDB->createCommand()->createIndex('{{idx1_questions}}', '{{questions}}', 'sid', false);
        $oDB->createCommand()->createIndex('{{idx2_questions}}', '{{questions}}', 'gid', false);
        $oDB->createCommand()->createIndex('{{idx3_questions}}', '{{questions}}', 'type', false);
        $oDB->createCommand()->createIndex('{{idx4_questions}}', '{{questions}}', 'title', false);
        $oDB->createCommand()->createIndex('{{idx5_questions}}', '{{questions}}', 'parent_qid', false);



        // question_attributes
        $oDB->createCommand()->createTable('{{question_attributes}}', array(
            'qaid' => "pk",
            'qid' => "integer NOT NULL default '0'",
            'attribute' => "string(50) NULL",
            'value' => "text NULL",
            'language' => "string(20) NULL",
        ));

        $oDB->createCommand()->createIndex('{{idx1_question_attributes}}', '{{question_attributes}}', 'qid', false);
        $oDB->createCommand()->createIndex('{{idx2_question_attributes}}', '{{question_attributes}}', 'attribute', false);


        // quota
        $oDB->createCommand()->createTable('{{quota}}', array(
            'id' => "pk",
            'sid' => "integer NULL",
            'name' => "string(255) NULL",
            'qlimit' => "integer NULL",
            'action' => "integer NULL",
            'active' => "integer NOT NULL default '1'",
            'autoload_url' => "integer NOT NULL default '0'",
        ));

        $oDB->createCommand()->createIndex('{{idx1_quota}}', '{{quota}}', 'sid', false);


        //quota_languagesettings
        $oDB->createCommand()->createTable('{{quota_languagesettings}}', array(
            'quotals_id' => "pk",
            'quotals_quota_id' => "integer NOT NULL default '0'",
            'quotals_language' => "string(45) NOT NULL default 'en'",
            'quotals_name' => "string(255) NULL",
            'quotals_message' => "text NOT NULL",
            'quotals_url' => "string(255)",
            'quotals_urldescrip' => "string(255)",
        ));


        // quota_members
        $oDB->createCommand()->createTable('{{quota_members}}', array(
            'id' => "pk",
            'sid' => "integer NULL",
            'qid' => "integer NULL",
            'quota_id' => "integer NULL",
            'code' => "string(11) NULL",
        ));

        $oDB->createCommand()->createIndex('{{idx1_quota_members}}', '{{quota_members}}', ['sid', 'qid', 'quota_id', 'code'], true);



        // saved_control
        $oDB->createCommand()->createTable('{{saved_control}}', array(
            'scid' => "pk",
            'sid' => "integer NOT NULL default '0'",
            'srid' => "integer NOT NULL default '0'",
            'identifier' => "text NOT NULL",
            'access_code' => "text NOT NULL",
            'email' => "string(192)",
            'ip' => "text NOT NULL",
            'saved_thisstep' => "text NOT NULL",
            'status' => "string(1) NOT NULL default ''",
            'saved_date' => "datetime NOT NULL",
            'refurl' => "text",
        ));

        $oDB->createCommand()->createIndex('{{idx1_saved_control}}', '{{saved_control}}', 'sid');
        $oDB->createCommand()->createIndex('{{idx2_saved_control}}', '{{saved_control}}', 'srid');


        // sessions

        $oDB->createCommand()->createTable('{{sessions}}', array(
            'id' => "string(32) NOT NULL",
            'expire' => "integer NULL",
            'data' => "longbinary",
        ));

        $oDB->createCommand()->addPrimaryKey('{{sessions_pk}}', '{{sessions}}', 'id');


        // settings_global

        $oDB->createCommand()->createTable('{{settings_global}}', array(
            'stg_name' =>  "string(50) NOT NULL default ''",
            'stg_value' =>  "text NOT NULL",
        ));

        $oDB->createCommand()->addPrimaryKey('{{settings_global_pk}}', '{{settings_global}}', 'stg_name');



        //settings_user

        $oDB->createCommand()->createTable('{{settings_user}}', array(
            'id' => "pk",
            'uid' => "integer NOT NULL",
            'entity' => "string(15) NULL",
            'entity_id' => "string(31) NULL",
            'stg_name' => "string(63) NOT NULL",
            'stg_value' => "text NULL",
        ));

        $oDB->createCommand()->createIndex('{{idx1_settings_user}}', '{{settings_user}}', 'uid', false);
        $oDB->createCommand()->createIndex('{{idx2_settings_user}}', '{{settings_user}}', 'entity', false);
        $oDB->createCommand()->createIndex('{{idx3_settings_user}}', '{{settings_user}}', 'entity_id', false);
        $oDB->createCommand()->createIndex('{{idx4_settings_user}}', '{{settings_user}}', 'stg_name', false);




        //Surveymenu

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
            'showincollapse' => 'integer DEFAULT 0',
            'active' => "integer NOT NULL DEFAULT '0'",
            'changed_at' => "datetime",
            'changed_by' => "integer NOT NULL DEFAULT '0'",
            'created_at' => "datetime",
            'created_by' => "integer NOT NULL DEFAULT '0'",
        ));

        $oDB->createCommand()->createIndex('{{surveymenu_name}}', '{{surveymenu}}', 'name', true);
        $oDB->createCommand()->createIndex('{{idx2_surveymenu}}', '{{surveymenu}}', 'title', false);

        $surveyMenuRowData = LsDefaultDataSets::getSurveyMenuData();
            foreach ($surveyMenuRowData as $surveyMenuRow) {
                if (in_array($oDB->getDriverName(), array('mssql', 'sqlsrv', 'dblib'))) {
                    unset($surveyMenuRow['id']);
                }
                $oDB->createCommand()->insert("{{surveymenu}}", $surveyMenuRow);
            }
        
        // Surveymenu entries

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
            'showincollapse' => 'integer DEFAULT 0',
            'active' =>  "integer NOT NULL DEFAULT '0'",
            'changed_at' =>  "datetime NULL",
            'changed_by' =>  "integer NOT NULL DEFAULT '0'",
            'created_at' =>  "datetime NULL",
            'created_by' =>  "integer NOT NULL DEFAULT '0'",
        ));

        $oDB->createCommand()->createIndex('{{idx1_surveymenu_entries}}', '{{surveymenu_entries}}', 'menu_id', false);
        $oDB->createCommand()->createIndex('{{idx5_surveymenu_entries}}', '{{surveymenu_entries}}', 'menu_title', false);
        $oDB->createCommand()->createIndex('{{surveymenu_entries_name}}', '{{surveymenu_entries}}', 'name', true);
        
        foreach($surveyMenuEntryRowData=LsDefaultDataSets::getSurveyMenuEntryData() as $surveyMenuEntryRow){
            if (in_array($oDB->getDriverName(), array('mssql', 'sqlsrv', 'dblib'))) {
                unset($surveyMenuEntryRow['id']);
            }
            $oDB->createCommand()->insert("{{surveymenu_entries}}", $surveyMenuEntryRow);
            
        }

        // surveys
        $oDB->createCommand()->createTable('{{surveys}}', array(
            'sid' => "integer NOT NULL",
            'owner_id' => "integer NOT NULL",
            'gsid' => "integer default '1'",
            'admin' => "string(50) NULL",
            'active' => "string(1) NOT NULL default 'N'",
            'expires' => "datetime NULL",
            'startdate' => "datetime NULL",
            'adminemail' => "string(254) NULL",
            'anonymized' => "string(1) NOT NULL default 'N'",
            'faxto' => "string(20) NULL",
            'format' => "string(1) NULL",
            'savetimings' => "string(1) NOT NULL default 'N'",
            'template' => "string(100) default 'default'",
            'language' => "string(50) NULL",
            'additional_languages' => "string(255) NULL",
            'datestamp' => "string(1) NOT NULL default 'N'",
            'usecookie' => "string(1) NOT NULL default 'N'",
            'allowregister' => "string(1) NOT NULL default 'N'",
            'allowsave' => "string(1) NOT NULL default 'Y'",
            'autonumber_start' => "integer NOT NULL default '0'",
            'autoredirect' => "string(1) NOT NULL default 'N'",
            'allowprev' => "string(1) NOT NULL default 'N'",
            'printanswers' => "string(1) NOT NULL default 'N'",
            'ipaddr' => "string(1) NOT NULL default 'N'",
            'refurl' => "string(1) NOT NULL default 'N'",
            'datecreated' => "datetime",
            'showsurveypolicynotice' => 'integer DEFAULT 0',
            'publicstatistics' => "string(1) NOT NULL default 'N'",
            'publicgraphs' => "string(1) NOT NULL default 'N'",
            'listpublic' => "string(1) NOT NULL default 'N'",
            'htmlemail' => "string(1) NOT NULL default 'N'",
            'sendconfirmation' => "string(1) NOT NULL default 'Y'",
            'tokenanswerspersistence' => "string(1) NOT NULL default 'N'",
            'assessments' => "string(1) NOT NULL default 'N'",
            'usecaptcha' => "string(1) NOT NULL default 'N'",
            'usetokens' => "string(1) NOT NULL default 'N'",
            'bounce_email' => "string(254) NULL",
            'attributedescriptions' => "text",
            'emailresponseto' => "text NULL",
            'emailnotificationto' => "text NULL",
            'tokenlength' => "integer NOT NULL default '15'",
            'showxquestions' => "string(1) default 'Y'",
            'showgroupinfo' => "string(1) default 'B'",
            'shownoanswer' => "string(1) default 'Y'",
            'showqnumcode' => "string(1) default 'X'",
            'bouncetime' => "integer",
            'bounceprocessing' => "string(1) default 'N'",
            'bounceaccounttype' => "string(4)",
            'bounceaccounthost' => "string(200)",
            'bounceaccountpass' => "string(100)",
            'bounceaccountencryption' => "string(3)",
            'bounceaccountuser' => "string(200)",
            'showwelcome' => "string(1) default 'Y'",
            'showprogress' => "string(1) default 'Y'",
            'questionindex' => "integer default '0' NOT NULL",
            'navigationdelay' => "integer NOT NULL default '0'",
            'nokeyboard' => "string(1) default 'N'",
            'alloweditaftercompletion' => "string(1) default 'N'",
            'googleanalyticsstyle' => "string(1) NULL",
            'googleanalyticsapikey' => "string(25) NULL",
        ));

        $oDB->createCommand()->addPrimaryKey('{{surveys_pk}}', '{{surveys}}', 'sid');

        $oDB->createCommand()->createIndex('{{idx1_surveys}}', '{{surveys}}', 'owner_id', false);
        $oDB->createCommand()->createIndex('{{idx2_surveys}}', '{{surveys}}', 'gsid', false);


        // surveys_groups
        $oDB->createCommand()->createTable('{{surveys_groups}}', array(
            'gsid' => "pk",
            'name' => "string(45) NOT NULL",
            'title' => "string(100) NULL",
            'template' => "string(128) DEFAULT 'default'",
            'description' => "text ",
            'sortorder' => "integer NOT NULL",
            'owner_id' => "integer NULL",
            'parent_id' => "integer NULL",
            'created' => "datetime NULL",
            'modified' => "datetime NULL",
            'created_by' => "integer NOT NULL"
        ));

        $oDB->createCommand()->createIndex('{{idx1_surveys_groups}}', '{{surveys_groups}}', 'name', false);
        $oDB->createCommand()->createIndex('{{idx2_surveys_groups}}', '{{surveys_groups}}', 'title', false);

        foreach($surveyGroupData=LsDefaultDataSets::getSurveygroupData() as $surveyGroup){
            $oDB->createCommand()->insert("{{surveys_groups}}", $surveyGroup);
        }


        // surveys_languagesettings
        $oDB->createCommand()->createTable('{{surveys_languagesettings}}', array(
            'surveyls_survey_id' => "integer NOT NULL",
            'surveyls_language' => "string(45) NOT NULL DEFAULT 'en'",
            'surveyls_title' => "string(200) NOT NULL",
            'surveyls_description' => "text NULL",
            'surveyls_welcometext' => "text NULL",
            'surveyls_endtext' => "text NULL",
            'surveyls_policy_notice' => "text NULL",
            'surveyls_policy_error' => "text NULL",
            'surveyls_policy_notice_label' => 'string(192) NULL',
            'surveyls_url' => "text NULL",
            'surveyls_urldescription' => "string(255) NULL",
            'surveyls_email_invite_subj' => "string(255) NULL",
            'surveyls_email_invite' => "text NULL",
            'surveyls_email_remind_subj' => "string(255) NULL",
            'surveyls_email_remind' => "text NULL",
            'surveyls_email_register_subj' => "string(255) NULL",
            'surveyls_email_register' => "text NULL",
            'surveyls_email_confirm_subj' => "string(255) NULL",
            'surveyls_email_confirm' => "text NULL",
            'surveyls_dateformat' => "integer NOT NULL DEFAULT 1",
            'surveyls_attributecaptions' => "text NULL",
            'email_admin_notification_subj' => "string(255) NULL",
            'email_admin_notification' => "text NULL",
            'email_admin_responses_subj' => "string(255) NULL",
            'email_admin_responses' => "text NULL",
            'surveyls_numberformat' => "integer NOT NULL DEFAULT 0",
            'attachments' => "text NULL",
        ));

        $oDB->createCommand()->addPrimaryKey('{{surveys_languagesettings_pk}}', '{{surveys_languagesettings}}', ['surveyls_survey_id', 'surveyls_language']);

        $oDB->createCommand()->createIndex('{{idx1_surveys_languagesettings}}', '{{surveys_languagesettings}}', 'surveyls_title', false);


        // survey_links
        $oDB->createCommand()->createTable('{{survey_links}}', array(
            'participant_id' => "string(50) NOT NULL",
            'token_id' => "integer NOT NULL",
            'survey_id' => "integer NOT NULL",
            'date_created' => "datetime",
            'date_invited' => "datetime",
            'date_completed' => "datetime",
        ));

        $oDB->createCommand()->addPrimaryKey('{{survey_links_pk}}', '{{survey_links}}', ['participant_id','token_id','survey_id']);



        // survey_url_parameters
        $oDB->createCommand()->createTable('{{survey_url_parameters}}', array(
            'id' => "pk",
            'sid' => "integer NOT NULL",
            'parameter' => "string(50) NOT NULL",
            'targetqid' => "integer NULL",
            'targetsqid' => "integer NULL",
        ));



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

        // NOTE: PLEASE DON'T USE ARRAY COMBINE !!! HARD TO READ AND MODIFY !!!!
        $headerArray = ['name','folder','title','creation_date','author','author_email','author_url','copyright','license','version','api_version','view_folder','files_folder',
        'description','last_update','owner_id','extends'];

        foreach($templateData=LsDefaultDataSets::getTemplatesData() as $template){
            $oDB->createCommand()->insert("{{templates}}", $template );
        }

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

        foreach($templateConfigurationData=LsDefaultDataSets::getTemplateConfigurationData() as $templateConfiguration){
            $oDB->createCommand()->insert("{{template_configuration}}", $templateConfiguration );
        }

        //tutorials
        $oDB->createCommand()->createTable(
            '{{tutorials}}',[
                'tid' =>  'pk',
                'name' =>  'string(128)',
                'title' =>  'string(192)',
                'icon' =>  'string(64)',
                'description' =>  'text',
                'active' =>  'integer DEFAULT 0',
                'settings' => 'text',
                'permission' =>  'string(128) NOT NULL',
                'permission_grade' =>  'string(128) NOT NULL'
            ]
        );
        $oDB->createCommand()->createIndex('{{idx1_tutorials}}', '{{tutorials}}', 'name', true);

        //tutorial user mapping
        $oDB->createCommand()->createTable('{{map_tutorial_users}}', array(
            'tid' => 'integer NOT NULL',
            'uid' => 'integer NOT NULL',
            'taken' => 'integer DEFAULT 1',
        ));

        $oDB->createCommand()->addPrimaryKey('{{map_tutorial_users_pk}}', '{{map_tutorial_users}}', ['uid','tid']);

        //tutorial entry groups
        $oDB->createCommand()->createTable('{{tutorial_entry_relation}}', array(
            'teid' => 'integer NOT NULL',
            'tid' => 'integer NOT NULL',
            'uid' => 'integer NULL',
            'sid' => 'integer NULL',
        ));

        $oDB->createCommand()->addPrimaryKey('{{tutorial_entry_relation_pk}}', '{{tutorial_entry_relation}}', ['teid','tid']);
        $oDB->createCommand()->createIndex('{{idx1_tutorial_entry_relation}}', '{{tutorial_entry_relation}}', 'uid', false);
        $oDB->createCommand()->createIndex('{{idx2_tutorial_entry_relation}}', '{{tutorial_entry_relation}}', 'sid', false);

        //tutorial entries
        $oDB->createCommand()->createTable(
            '{{tutorial_entries}}',[
                'teid' =>  'pk',
                'ordering' =>  'integer',
                'title' =>  'text',
                'content' =>  'text',
                'settings' => 'text'
            ]
        );

        //user_in_groups
        $oDB->createCommand()->createTable('{{user_in_groups}}', array(
            'ugid' => "integer NOT NULL",
            'uid' => "integer NOT NULL",
        ));

        $oDB->createCommand()->addPrimaryKey('{{user_in_groups_pk}}', '{{user_in_groups}}', ['ugid','uid']);


        // users
        $oDB->createCommand()->createTable('{{users}}', array(
            'uid' => "pk",
            'users_name' => "string(64) NOT NULL default ''",
            'password' => "text NOT NULL",
            'full_name' => "string(50) NOT NULL",
            'parent_id' => "integer NOT NULL",
            'lang' => "string(20)",
            'email' => "string(192)",
            'htmleditormode' => "string(7) default 'default'",
            'templateeditormode' => "string(7) NOT NULL default 'default'",
            'questionselectormode' => "string(7) NOT NULL default 'default'",
            'one_time_pw' => "text",
            'dateformat' => "integer NOT NULL DEFAULT 1",
            'created' => "datetime",
            'modified' => "datetime",
        ));

        $oDB->createCommand()->createIndex('{{idx1_users}}', '{{users}}', 'users_name', true);
        $oDB->createCommand()->createIndex('{{idx2_users}}', '{{users}}', 'email', false);


        //user_groups
        $oDB->createCommand()->createTable('{{user_groups}}', array(
            'ugid' => "pk",
            'name' => "string(20) NOT NULL",
            'description' => "text NOT NULL",
            'owner_id' => "integer NOT NULL",
        ));

        $oDB->createCommand()->createIndex('{{idx1_user_groups}}', '{{user_groups}}', 'name', true);

        // asset version
        $oDB->createCommand()->createTable('{{asset_version}}',array(
            'id' => 'pk',
            'path' => 'text NOT NULL',
            'version' => 'integer NOT NULL',
        ));

        // Set database version
        $oDB->createCommand()->insert("{{settings_global}}", ['stg_name'=> 'DBVersion' , 'stg_value' => $databaseCurrentVersion]);

        $oTransaction->commit();
    }catch(Exception $e){
        $oTransaction->rollback();
        throw new CHttpException(500, $e->getMessage());
    }

    
    // $oTransaction = $oDB->beginTransaction();
    // try{  
    //     $surveyMenuRowData = LsDefaultDataSets::getSurveyMenuData();
    //     foreach ($surveyMenuRowData as $surveyMenuRow) {
    //         switchMSSQLIdentityInsert("surveymenu", true, $oDB);
    //         $oDB->createCommand()->insert("{{surveymenu}}", $surveyMenuRow);
    //         switchMSSQLIdentityInsert("surveymenu", false, $oDB);
    //     }
    //     $oTransaction->commit();
    // }catch(Exception $e){
    //     $oTransaction->rollback();
    //     throw new CHttpException(500, $e->getMessage());
    // }

    // $oTransaction = $oDB->beginTransaction();
    // try{  
    //     foreach($surveyMenuEntryRowData=LsDefaultDataSets::getSurveyMenuEntryData() as $surveyMenuEntryRow){
    //         switchMSSQLIdentityInsert("surveymenu_entries", true, $oDB);
    //         $oDB->createCommand()->insert("{{surveymenu_entries}}", $surveyMenuEntryRow);
    //         switchMSSQLIdentityInsert("surveymenu_entries", false, $oDB);
    //     }
    //     $oTransaction->commit();
    // }catch(Exception $e){
    //     $oTransaction->rollback();
    //     throw new CHttpException(500, $e->getMessage());
    // }
    // return true;
}
