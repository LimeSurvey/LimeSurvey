<?php

/**
 * @param $oDB DbConnection
 *
 * @throws CHttpException
 * @throws CException
 */
function populateDatabase($oDB)
{
    /**
    * Populate the database for a limesurvey installation
    * Rules:
    * - Use the provided addColumn, alterColumn, dropPrimaryKey etc. functions where applicable - they ensure cross-DB compatibility
    * - DEFAULT & NULL need to be uppercase (for MSSQL driver compatibility)
    * - Never use foreign keys
    * - Only use the database field types provided by Yii as they are guaranteed to be cross-DB compatible
    * - You may also use: text / mediumtext/ longtext
    *       - MySQL is differentiating here: text - max size 64kb, mediumtext - max size 16MB , longtext - max size 2.1 GB
    *       - MSSQL: all three types use text (max size 2.1 GB)
    *       - Postgres: all three types use text (max size unlimited)
    * - If you want to use database functions make sure they exist on all three supported database types
    * - Always prefix key/index names by using curly brackets {{ }}
    */

    // Get current database version:
    $version = require(\Yii::app()->getBasePath() . '/config/version.php');
    $databaseCurrentVersion = $version['dbversionnumber'];

    Yii::app()->loadHelper('database');
    Yii::app()->loadHelper('update.updatedb');
    $options = '';
    // The engine has to be explicitely set because MYSQL 8 switches the default engine to INNODB
    if ($oDB->driverName == 'mysql') {
        $options = 'ENGINE=' . Yii::app()->getConfig('mysqlEngine') . ' DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
        if (Yii::app()->getConfig('mysqlEngine') == 'INNODB') {
            $options .= ' ROW_FORMAT=DYNAMIC'; // Same than create-database
        }
    }

    $oTransaction = $oDB->beginTransaction();
    try {
        //answers table
        $oDB->createCommand()->createTable('{{answers}}', array(
            'aid' =>  "pk",
            'qid' => 'integer NOT NULL',
            'code' => 'string(5) NOT NULL',
            'sortorder' => 'integer NOT NULL',
            'assessment_value' => 'integer NOT NULL DEFAULT 0',
            'scale_id' => 'integer NOT NULL DEFAULT 0',
        ), $options);

        $oDB->createCommand()->createIndex('{{answers_idx}}', '{{answers}}', ['qid', 'code', 'scale_id'], false);
        $oDB->createCommand()->createIndex('{{answers_idx2}}', '{{answers}}', 'sortorder', false);

        $oDB->createCommand()->createTable('{{answer_l10ns}}', array(
            'id' =>  "pk",
            'aid' =>  "integer NOT NULL",
            'answer' =>  "mediumtext NOT NULL",
            'language' =>  "string(20) NOT NULL"
        ), $options);
        $oDB->createCommand()->createIndex('{{answer_l10ns_idx}}', '{{answer_l10ns}}', ['aid', 'language'], true);

        // assessements
        $oDB->createCommand()->createTable('{{assessments}}', array(
            'id' =>         'autoincrement',
            'sid' =>        'integer NOT NULL DEFAULT 0',
            'scope' =>      'string(5) NOT NULL'    ,
            'gid' =>        'integer NOT NULL DEFAULT 0',
            'name' =>       'text NOT NULL',
            'minimum' =>    'string(50) NOT NULL',
            'maximum' =>    'string(50) NOT NULL',
            'message' =>    'mediumtext NOT NULL',
            'language' =>   "string(20) NOT NULL DEFAULT 'en'",
            'composite_pk' => array('id', 'language')
        ), $options);

        $oDB->createCommand()->createIndex('{{assessments_idx2}}', '{{assessments}}', 'sid', false);
        $oDB->createCommand()->createIndex('{{assessments_idx3}}', '{{assessments}}', 'gid', false);

        // boxes
        $oDB->createCommand()->createTable('{{boxes}}', array(
            'id' => "pk",
            'position' => "integer NULL ",
            'url' => "text NOT NULL ",
            'title' => "text NOT NULL ",
            'buttontext' => "string(255) NULL ",
            'ico' => "string(255) NULL ",
            'desc' => "text NOT NULL ",
            'page' => "text NOT NULL ",
            'usergroup' => "integer NOT NULL "
        ), $options);

        foreach ($boxesData = LsDefaultDataSets::getBoxesData() as $box) {
            $oDB->createCommand()->insert("{{boxes}}", $box);
        }

        // conditions
        $oDB->createCommand()->createTable('{{conditions}}', array(
            'cid' => 'pk',
            'qid' => "integer NOT NULL DEFAULT '0'",
            'cqid' => "integer NOT NULL DEFAULT '0'",
            'cfieldname' => "string(50) NOT NULL DEFAULT ''",
            'method' => "string(5) NOT NULL DEFAULT ''",
            'value' => "string(255) NOT NULL DEFAULT ''",
            'scenario' => "integer NOT NULL DEFAULT 1"
        ), $options);
        $oDB->createCommand()->createIndex('{{conditions_idx}}', '{{conditions}}', 'qid', false);
        $oDB->createCommand()->createIndex('{{conditions_idx3}}', '{{conditions}}', 'cqid', false);


        // defaultvalues
        $oDB->createCommand()->createTable('{{defaultvalues}}', array(
            'dvid' =>  "pk",
            'qid' =>  "integer NOT NULL DEFAULT '0'",
            'scale_id' =>  "integer NOT NULL DEFAULT '0'",
            'sqid' =>  "integer NOT NULL DEFAULT '0'",
            'specialtype' =>  "string(20) NOT NULL DEFAULT ''",
        ), $options);
        $oDB->createCommand()->createIndex('{{idx1_defaultvalue}}', '{{defaultvalues}}', ['qid', 'scale_id', 'sqid', 'specialtype'], false);

        // defaultvalue_l10ns
        $oDB->createCommand()->createTable('{{defaultvalue_l10ns}}', array(
            'id' =>  "pk",
            'dvid' =>  "integer NOT NULL DEFAULT '0'",
            'language' =>  "string(20) NOT NULL",
            'defaultvalue' =>  "text",
        ), $options);
        $oDB->createCommand()->createIndex('{{idx1_defaultvalue_ls}}', '{{defaultvalue_l10ns}}', ['dvid', 'language'], false);

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
        ), $options);

        // failed_login_attempts
        $oDB->createCommand()->createTable('{{failed_login_attempts}}', array(
            'id' =>  "pk",
            'ip' =>  "string(40) NOT NULL",
            'last_attempt' =>  "string(20) NOT NULL",
            'number_attempts' =>  "integer NOT NULL",
            'is_frontend' => "boolean NOT NULL"
        ), $options);


        $oDB->createCommand()->createTable('{{groups}}', array(
            'gid' =>  "pk",
            'sid' =>  "integer NOT NULL DEFAULT '0'",
            'group_order' =>  "integer NOT NULL DEFAULT '0'",
            'randomization_group' =>  "string(20) NOT NULL DEFAULT ''",
            'grelevance' =>  "text NULL"
        ), $options);
        $oDB->createCommand()->createIndex('{{idx1_groups}}', '{{groups}}', 'sid', false);


        $oDB->createCommand()->createTable('{{group_l10ns}}', array(
            'id' =>  "pk",
            'gid' =>  "integer NOT NULL",
            'group_name' =>  "text NOT NULL",
            'description' =>  "mediumtext",
            'language' =>  "string(20) NOT NULL"
        ), $options);
        $oDB->createCommand()->createIndex('{{idx1_group_ls}}', '{{group_l10ns}}', ['gid', 'language'], true);

        // labels
        $oDB->createCommand()->createTable('{{labels}}', array(
            'id' =>  "pk",
            'lid' =>  "integer NOT NULL DEFAULT 0",
            'code' =>  "string(20) NOT NULL DEFAULT ''",
            'sortorder' =>  "integer NOT NULL",
            'assessment_value' =>  "integer NOT NULL DEFAULT '0'",
        ), $options);
        $oDB->createCommand()->createIndex('{{idx1_labels}}', '{{labels}}', 'code', false);
        $oDB->createCommand()->createIndex('{{idx2_labels}}', '{{labels}}', 'sortorder', false);
        $oDB->createCommand()->createIndex('{{idx4_labels}}', '{{labels}}', ['lid','sortorder'], false);
        $oDB->createCommand()->createIndex('{{idx5_labels}}', '{{labels}}', ['lid','code'], true);


        // label_l10ns
        $oDB->createCommand()->createTable('{{label_l10ns}}', array(
            'id' =>  "pk",
            'label_id' =>  "integer NOT NULL",
            'title' =>  "text",
            'language' =>  "string(20) NOT NULL DEFAULT 'en'"
        ), $options);

        // labelsets
        $oDB->createCommand()->createTable('{{labelsets}}', array(
            'lid' => 'pk',
            'owner_id' => "integer NULL",
            'label_name' =>  "string(100) NOT NULL DEFAULT ''",
            'languages' =>  "string(255) NOT NULL",
        ), $options);
        $oDB->createCommand()->createIndex('{{idx1_labelsets}}', '{{labelsets}}', 'owner_id', false);
        $oDB->createCommand()->createIndex('{{idx2_labelsets}}', '{{labelsets}}', ['lid','owner_id'], false);

        // notifications
        $oDB->createCommand()->createTable('{{notifications}}', array(
            'id' =>  "pk",
            'entity' =>  "string(15) NOT NULL ",
            'entity_id' =>  "integer NOT NULL",
            'title' =>  "string(255) NOT NULL",
            'message' =>  "mediumtext NOT NULL",
            'status' =>  "string(15) NOT NULL DEFAULT 'new' ",
            'importance' =>  "integer NOT NULL DEFAULT 1",
            'display_class' =>  "string(31) DEFAULT 'default' ",
            'hash' =>  "string(64)",
            'created' =>  "datetime",
            'first_read' =>  "datetime",
        ), $options);

        $oDB->createCommand()->createIndex('{{notifications_pk}}', '{{notifications}}', ['entity', 'entity_id', 'status'], false);
        $oDB->createCommand()->createIndex('{{idx1_notifications}}', '{{notifications}}', 'hash', false);


        //  participants
        $oDB->createCommand()->createTable('{{participants}}', array(
            'participant_id' =>  "string(50) NOT NULL",
            'firstname' =>  "text NULL",
            'lastname' =>  "text NULL",
            'email' =>  "text",
            'language' =>  "string(40) NULL",
            'blacklisted' =>  "string(1) NOT NULL",
            'owner_uid' =>  "integer NOT NULL",
            'created_by' =>  "integer NOT NULL",
            'created' =>  "datetime",
            'modified' =>  "datetime",
        ), $options);

        $oDB->createCommand()->addPrimaryKey('{{participant_pk}}', '{{participants}}', 'participant_id', false);
        $oDB->createCommand()->createIndex('{{idx3_participants}}', '{{participants}}', 'language', false);


        // participant_attribute
        $oDB->createCommand()->createTable('{{participant_attribute}}', array(
            'participant_id' =>  "string(50) NOT NULL",
            'attribute_id' =>  "integer NOT NULL",
            'value' =>  "text NOT NULL",
        ), $options);

        $oDB->createCommand()->addPrimaryKey('{{participant_attribute_pk}}', '{{participant_attribute}}', ['participant_id', 'attribute_id']);


        // participant_attribute_names_lang
        $oDB->createCommand()->createTable('{{participant_attribute_names_lang}}', array(
            'attribute_id' =>  "integer NOT NULL",
            'attribute_name' =>  "string(255) NOT NULL",
            'lang' =>  "string(20) NOT NULL",
        ), $options);

        $oDB->createCommand()->addPrimaryKey('{{participant_attribute_names_lang_pk}}', '{{participant_attribute_names_lang}}', ['attribute_id', 'lang']);



        // participant_attribute_names
        $oDB->createCommand()->createTable('{{participant_attribute_names}}', array(
            'attribute_id' =>  "autoincrement",
            'attribute_type' =>  "string(4) NOT NULL",
            'defaultname' =>  "string(255) NOT NULL",
            'visible' =>  "string(5) NOT NULL",
            'encrypted' =>  "string(5) NOT NULL",
            'core_attribute' =>  "string(5) NOT NULL",
            'composite_pk' => array('attribute_id', 'attribute_type')
        ), $options);

        $oDB->createCommand()->createIndex('{{idx_participant_attribute_names}}', '{{participant_attribute_names}}', ['attribute_id', 'attribute_type']);
        $aCoreAttributes = array('firstname', 'lastname', 'email');

        // load sodium library
        $sodium = Yii::app()->sodium;
        // check if sodium library exists
        if ($sodium->bLibraryExists === true) {
            $sEncrypted = 'Y';
        } else {
            $sEncrypted = 'N';
        }

        foreach ($aCoreAttributes as $attribute) {
            $oDB->createCommand()->insert('{{participant_attribute_names}}', array(
                'attribute_type'    => 'TB',
                'defaultname'       => $attribute,
                'visible'           => 'TRUE',
                'encrypted'         => $sEncrypted,
                'core_attribute'    => 'Y'
            ));
        }


        //participant_attribute_values
        $oDB->createCommand()->createTable('{{participant_attribute_values}}', array(
            'value_id' => "pk",
            'attribute_id' => "integer NOT NULL",
            'value' => "mediumtext NOT NULL",
        ), $options);



        //participant_shares
        $oDB->createCommand()->createTable('{{participant_shares}}', array(
            'participant_id' =>  "string(50) NOT NULL",
            'share_uid' =>  "integer NOT NULL",
            'date_added' =>  "datetime NOT NULL",
            'can_edit' =>  "string(5) NOT NULL",
        ), $options);

        $oDB->createCommand()->addPrimaryKey('{{participant_shares_pk}}', '{{participant_shares}}', ['participant_id', 'share_uid'], false);


        // permissions
        $oDB->createCommand()->createTable('{{permissions}}', array(
            'id' =>  "pk",
            'entity' =>  "string(50) NOT NULL",
            'entity_id' =>  "integer NOT NULL",
            'uid' =>  "integer NOT NULL",
            'permission' =>  "string(100) NOT NULL",
            'create_p' =>  "integer NOT NULL DEFAULT 0",
            'read_p' =>  "integer NOT NULL DEFAULT 0",
            'update_p' =>  "integer NOT NULL DEFAULT 0",
            'delete_p' =>  "integer NOT NULL DEFAULT 0",
            'import_p' =>  "integer NOT NULL DEFAULT 0",
            'export_p' =>  "integer NOT NULL DEFAULT 0",
        ), $options);

        $oDB->createCommand()->createIndex('{{idx1_permissions}}', '{{permissions}}', ['entity_id', 'entity', 'permission', 'uid'], true);


        // permissiontemplates
        $oDB->createCommand()->createTable("{{permissiontemplates}}", [
            'ptid' =>  "pk",
            'name' =>  "string(127) NOT NULL",
            'description' =>  "text NULL",
            'renewed_last' =>  "datetime NULL",
            'created_at' =>  "datetime NOT NULL",
            'created_by' =>  "int NOT NULL"
        ], $options);

        $oDB->createCommand()->createIndex('{{idx1_name}}', '{{permissiontemplates}}', 'name', true);

        // plugins
        $oDB->createCommand()->createTable('{{plugins}}', array(
            'id' =>  "pk",
            'name' =>  "string(50) NOT NULL",
            'plugin_type' =>  "string(6) DEFAULT 'user'",
            'active' =>  "integer NOT NULL DEFAULT 0",
            'priority' =>  "integer NOT NULL DEFAULT 0",
            'version' =>  "string(32) NULL",
            'load_error' => 'integer DEFAULT 0',
            'load_error_message' => 'text'
        ), $options);


        // plugin_settings
        $oDB->createCommand()->createTable('{{plugin_settings}}', array(
            'id' => "pk",
            'plugin_id' => "integer NOT NULL",
            'model' => "string(50) NULL",
            'model_id' => "integer NULL",
            'key' => "string(50) NOT NULL",
            'value' => "mediumtext NULL",
        ), $options);


        // questions
        $oDB->createCommand()->createTable('{{questions}}', array(
            'qid' =>  "pk",
            'parent_qid' =>  "integer NOT NULL DEFAULT '0'",
            'sid' =>  "integer NOT NULL DEFAULT '0'",
            'gid' =>  "integer NOT NULL DEFAULT '0'",
            'type' =>  "string(30) NOT NULL DEFAULT 'T'",
            'title' =>  "string(20) NOT NULL DEFAULT ''",
            'preg' =>  "text",
            'other' =>  "string(1) NOT NULL DEFAULT 'N'",
            'mandatory' =>  "string(1) NULL",
            'encrypted' =>  "string(1) NULL DEFAULT 'N'",
            'question_order' =>  "integer NOT NULL",
            'scale_id' =>  "integer NOT NULL DEFAULT '0'",
            'same_default' =>  "integer NOT NULL DEFAULT '0'",
            'relevance' =>  "text",
            'question_theme_name' => "string(150) NULL",
            'modulename' =>  "string(255) NULL",
            'same_script' => "integer NOT NULL DEFAULT '0'",
        ), $options);
        $oDB->createCommand()->createIndex('{{idx1_questions}}', '{{questions}}', 'sid', false);
        $oDB->createCommand()->createIndex('{{idx2_questions}}', '{{questions}}', 'gid', false);
        $oDB->createCommand()->createIndex('{{idx3_questions}}', '{{questions}}', 'type', false);
        $oDB->createCommand()->createIndex('{{idx4_questions}}', '{{questions}}', 'title', false);
        $oDB->createCommand()->createIndex('{{idx5_questions}}', '{{questions}}', 'parent_qid', false);


        // question language settings
        $oDB->createCommand()->createTable('{{question_l10ns}}', array(
            'id' =>  "pk",
            'qid' =>  "integer NOT NULL",
            'question' =>  "mediumtext NOT NULL",
            'help' =>  "mediumtext",
            'script' => " text NULL DEFAULT NULL",
            'language' =>  "string(20) NOT NULL"
        ), $options);

        $oDB->createCommand()->createIndex('{{idx1_question_ls}}', '{{question_l10ns}}', ['qid', 'language'], true);



        // question_attributes
        $oDB->createCommand()->createTable('{{question_attributes}}', array(
            'qaid' => "pk",
            'qid' => "integer NOT NULL DEFAULT '0'",
            'attribute' => "string(50) NULL",
            'value' => "mediumtext NULL",
            'language' => "string(20) NULL",
        ), $options);

        $oDB->createCommand()->createIndex('{{idx1_question_attributes}}', '{{question_attributes}}', 'qid', false);
        $oDB->createCommand()->createIndex('{{idx2_question_attributes}}', '{{question_attributes}}', 'attribute', false);


        // quota
        $oDB->createCommand()->createTable('{{quota}}', array(
            'id' => "pk",
            'sid' => "integer NULL",
            'name' => "string(255) NULL",
            'qlimit' => "integer NULL",
            'action' => "integer NULL",
            'active' => "integer NOT NULL DEFAULT '1'",
            'autoload_url' => "integer NOT NULL DEFAULT '0'",
        ), $options);

        $oDB->createCommand()->createIndex('{{idx1_quota}}', '{{quota}}', 'sid', false);


        //quota_languagesettings
        $oDB->createCommand()->createTable('{{quota_languagesettings}}', array(
            'quotals_id' => "pk",
            'quotals_quota_id' => "integer NOT NULL DEFAULT '0'",
            'quotals_language' => "string(45) NOT NULL DEFAULT 'en'",
            'quotals_name' => "string(255) NULL",
            'quotals_message' => "mediumtext NOT NULL",
            'quotals_url' => "string(255)",
            'quotals_urldescrip' => "string(255)",
        ), $options);
        $oDB->createCommand()->createIndex('{{idx1_quota_id}}', '{{quota_languagesettings}}', ['quotals_quota_id']);


        // quota_members
        $oDB->createCommand()->createTable('{{quota_members}}', array(
            'id' => "pk",
            'sid' => "integer NULL",
            'qid' => "integer NULL",
            'quota_id' => "integer NULL",
            'code' => "string(11) NULL",
        ), $options);

        $oDB->createCommand()->createIndex('{{idx1_quota_members}}', '{{quota_members}}', ['sid', 'qid', 'quota_id', 'code'], true);
        $oDB->createCommand()->createIndex('{{idx2_quota_id}}', '{{quota_members}}', ['quota_id']);



        // saved_control
        $oDB->createCommand()->createTable('{{saved_control}}', array(
            'scid' => "pk",
            'sid' => "integer NOT NULL DEFAULT '0'",
            'srid' => "integer NOT NULL DEFAULT '0'",
            'identifier' => "text NOT NULL",
            'access_code' => "text NOT NULL",
            'email' => "string(192)",
            'ip' => "text NOT NULL",
            'saved_thisstep' => "text NOT NULL",
            'status' => "string(1) NOT NULL DEFAULT ''",
            'saved_date' => "datetime NOT NULL",
            'refurl' => "text",
        ), $options);

        $oDB->createCommand()->createIndex('{{idx1_saved_control}}', '{{saved_control}}', 'sid');
        $oDB->createCommand()->createIndex('{{idx2_saved_control}}', '{{saved_control}}', 'srid');


        // sessions

        $oDB->createCommand()->createTable('{{sessions}}', array(
            'id' => "string(32) NOT NULL",
            'expire' => "integer NULL",
            'data' => "longbinary",
        ), $options);
        $oDB->createCommand()->addPrimaryKey('{{sessions_pk}}', '{{sessions}}', 'id');
        $oDB->createCommand()->createIndex('sess_expire', '{{sessions}}', 'expire');

        // settings_global

        $oDB->createCommand()->createTable('{{settings_global}}', array(
            'stg_name' =>  "string(50) NOT NULL DEFAULT ''",
            'stg_value' =>  "mediumtext NOT NULL",
        ), $options);

        $oDB->createCommand()->addPrimaryKey('{{settings_global_pk}}', '{{settings_global}}', 'stg_name');

        //this part has only been done in update...
        $defaultSetting = LsDefaultDataSets::getDefaultUserAdministrationSettings();
        $oDB->createCommand()->insert('{{settings_global}}', [
            "stg_name" => 'sendadmincreationemail',
            "stg_value" => $defaultSetting['sendadmincreationemail'],
        ]);

        $oDB->createCommand()->insert('{{settings_global}}', [
            "stg_name" => 'admincreationemailsubject',
            "stg_value" => $defaultSetting['admincreationemailsubject'],
        ]);

        $oDB->createCommand()->insert('{{settings_global}}', [
            "stg_name" => 'admincreationemailtemplate',
            "stg_value" => $defaultSetting['admincreationemailtemplate'],
        ]);



        //settings_user

        $oDB->createCommand()->createTable('{{settings_user}}', array(
            'id' => "pk",
            'uid' => "integer NOT NULL",
            'entity' => "string(15) NULL",
            'entity_id' => "string(31) NULL",
            'stg_name' => "string(63) NOT NULL",
            'stg_value' => "mediumtext NULL",
        ), $options);

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
        ), $options);

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
            'data' =>  "mediumtext",
            'getdatamethod' =>  "string(192)  NOT NULL DEFAULT ''",
            'language' =>  "string(32)  NOT NULL DEFAULT 'en-GB'",
            'showincollapse' => 'integer DEFAULT 0',
            'active' =>  "integer NOT NULL DEFAULT '0'",
            'changed_at' =>  "datetime NULL",
            'changed_by' =>  "integer NOT NULL DEFAULT '0'",
            'created_at' =>  "datetime NULL",
            'created_by' =>  "integer NOT NULL DEFAULT '0'",
        ), $options);

        $oDB->createCommand()->createIndex('{{idx1_surveymenu_entries}}', '{{surveymenu_entries}}', 'menu_id', false);
        $oDB->createCommand()->createIndex('{{idx5_surveymenu_entries}}', '{{surveymenu_entries}}', 'menu_title', false);
        $oDB->createCommand()->createIndex('{{surveymenu_entries_name}}', '{{surveymenu_entries}}', 'name', true);

        foreach ($surveyMenuEntryRowData = LsDefaultDataSets::getSurveyMenuEntryData() as $surveyMenuEntryRow) {
            if (in_array($oDB->getDriverName(), array('mssql', 'sqlsrv', 'dblib'))) {
                unset($surveyMenuEntryRow['id']);
            }
            $oDB->createCommand()->insert("{{surveymenu_entries}}", $surveyMenuEntryRow);
        }

        // surveys
        $oDB->createCommand()->createTable('{{surveys}}', array(
            'sid' => "integer NOT NULL",
            'owner_id' => "integer NOT NULL",
            'gsid' => "integer DEFAULT '1'",
            'admin' => "string(50) NULL",
            'active' => "string(1) NOT NULL DEFAULT 'N'",
            'expires' => "datetime NULL",
            'startdate' => "datetime NULL",
            'adminemail' => "string(254) NULL",
            'anonymized' => "string(1) NOT NULL DEFAULT 'N'",
            'format' => "string(1) NULL",
            'savetimings' => "string(1) NOT NULL DEFAULT 'N'",
            'template' => "string(100) DEFAULT 'default'",
            'language' => "string(50) NULL",
            'additional_languages' => "text NULL",
            'datestamp' => "string(1) NOT NULL DEFAULT 'Y'",
            'usecookie' => "string(1) NOT NULL DEFAULT 'N'",
            'allowregister' => "string(1) NOT NULL DEFAULT 'N'",
            'allowsave' => "string(1) NOT NULL DEFAULT 'Y'",
            'autonumber_start' => "integer NOT NULL DEFAULT '0'",
            'autoredirect' => "string(1) NOT NULL DEFAULT 'N'",
            'allowprev' => "string(1) NOT NULL DEFAULT 'N'",
            'printanswers' => "string(1) NOT NULL DEFAULT 'N'",
            'ipaddr' => "string(1) NOT NULL DEFAULT 'N'",
            'ipanonymize' => "string(1) NOT NULL DEFAULT 'N'",
            'refurl' => "string(1) NOT NULL DEFAULT 'N'",
            'savequotaexit' => "string(1) NOT NULL DEFAULT 'N'",
            'showsurveypolicynotice' => 'integer DEFAULT 0',
            'publicstatistics' => "string(1) NOT NULL DEFAULT 'N'",
            'publicgraphs' => "string(1) NOT NULL DEFAULT 'N'",
            'listpublic' => "string(1) NOT NULL DEFAULT 'N'",
            'htmlemail' => "string(1) NOT NULL DEFAULT 'Y'",
            'sendconfirmation' => "string(1) NOT NULL DEFAULT 'Y'",
            'tokenanswerspersistence' => "string(1) NOT NULL DEFAULT 'N'",
            'assessments' => "string(1) NOT NULL DEFAULT 'N'",
            'usecaptcha' => "string(1) NOT NULL DEFAULT 'N'",
            'usetokens' => "string(1) NOT NULL DEFAULT 'N'",
            'bounce_email' => "string(254) NULL",
            'attributedescriptions' => "mediumtext",
            'emailresponseto' => "text NULL",
            'emailnotificationto' => "text NULL",
            'tokenlength' => "integer NOT NULL DEFAULT '15'",
            'showxquestions' => "string(1) DEFAULT 'Y'",
            'showgroupinfo' => "string(1) DEFAULT 'B'",
            'shownoanswer' => "string(1) DEFAULT 'Y'",
            'showqnumcode' => "string(1) DEFAULT 'X'",
            'bouncetime' => "integer",
            'bounceprocessing' => "string(1) DEFAULT 'N'",
            'bounceaccounttype' => "string(4)",
            'bounceaccounthost' => "string(200)",
            'bounceaccountpass' => "text NULL",
            'bounceaccountencryption' => "string(3)",
            'bounceaccountuser' => "string(200)",
            'showwelcome' => "string(1) DEFAULT 'Y'",
            'showprogress' => "string(1) DEFAULT 'Y'",
            'questionindex' => "integer DEFAULT '0' NOT NULL",
            'navigationdelay' => "integer NOT NULL DEFAULT '0'",
            'nokeyboard' => "string(1) DEFAULT 'N'",
            'alloweditaftercompletion' => "string(1) DEFAULT 'N'",
            'googleanalyticsstyle' => "string(1) NULL",
            'googleanalyticsapikey' => "string(25) NULL",
            'tokenencryptionoptions' => "text NULL",
            'access_mode' => "string(1) DEFAULT 'O'",
            'lastmodified' => 'datetime NOT NULL',
        ), $options);

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
            'alwaysavailable' => "boolean NULL",
            'created' => "datetime NULL",
            'modified' => "datetime NULL",
            'created_by' => "integer NOT NULL"
        ), $options);

        $oDB->createCommand()->createIndex('{{idx1_surveys_groups}}', '{{surveys_groups}}', 'name', false);
        $oDB->createCommand()->createIndex('{{idx2_surveys_groups}}', '{{surveys_groups}}', 'title', false);

        foreach ($surveyGroupData = LsDefaultDataSets::getSurveygroupData() as $surveyGroup) {
            $oDB->createCommand()->insert("{{surveys_groups}}", $surveyGroup);
        }


        // surveys_groupsettings
        $oDB->createCommand()->createTable('{{surveys_groupsettings}}', array(
            'gsid' => "integer NOT NULL",
            'owner_id' => "integer NULL DEFAULT NULL",
            'admin' => "string(50) NULL DEFAULT NULL",
            'adminemail' => "string(254) NULL DEFAULT NULL",
            'anonymized' => "string(1) NOT NULL DEFAULT 'N'",
            'format' => "string(1) NULL DEFAULT NULL",
            'savetimings' => "string(1) NOT NULL DEFAULT 'N'",
            'template' => "string(100) NULL DEFAULT 'default'",
            'datestamp' => "string(1) NOT NULL DEFAULT 'Y'",
            'usecookie' => "string(1) NOT NULL DEFAULT 'N'",
            'allowregister' => "string(1) NOT NULL DEFAULT 'N'",
            'allowsave' => "string(1) NOT NULL DEFAULT 'Y'",
            'autonumber_start' => "integer NULL DEFAULT '0'",
            'autoredirect' => "string(1) NOT NULL DEFAULT 'N'",
            'allowprev' => "string(1) NOT NULL DEFAULT 'N'",
            'printanswers' => "string(1) NOT NULL DEFAULT 'N'",
            'ipaddr' => "string(1) NOT NULL DEFAULT 'N'",
            'ipanonymize' => "string(1) NOT NULL DEFAULT 'N'",
            'refurl' => "string(1) NOT NULL DEFAULT 'N'",
            'showsurveypolicynotice' => "integer NULL DEFAULT '0'",
            'publicstatistics' => "string(1) NOT NULL DEFAULT 'N'",
            'publicgraphs' => "string(1) NOT NULL DEFAULT 'N'",
            'listpublic' => "string(1) NOT NULL DEFAULT 'N'",
            'htmlemail' => "string(1) NOT NULL DEFAULT 'Y'",
            'sendconfirmation' => "string(1) NOT NULL DEFAULT 'Y'",
            'tokenanswerspersistence' => "string(1) NOT NULL DEFAULT 'N'",
            'assessments' => "string(1) NOT NULL DEFAULT 'N'",
            'usecaptcha' => "string(1) NOT NULL DEFAULT 'N'",
            'bounce_email' => "string(254) NULL DEFAULT NULL",
            'attributedescriptions' => "text NULL",
            'emailresponseto' => "text NULL",
            'emailnotificationto' => "text NULL",
            'tokenlength' => "integer NULL DEFAULT '15'",
            'showxquestions' => "string(1) NULL DEFAULT 'Y'",
            'showgroupinfo' => "string(1) NULL DEFAULT 'B'",
            'shownoanswer' => "string(1) NULL DEFAULT 'Y'",
            'showqnumcode' => "string(1) NULL DEFAULT 'X'",
            'showwelcome' => "string(1) NULL DEFAULT 'Y'",
            'showprogress' => "string(1) NULL DEFAULT 'Y'",
            'questionindex' => "integer NULL DEFAULT '0'",
            'navigationdelay' => "integer NULL DEFAULT '0'",
            'nokeyboard' => "string(1) NULL DEFAULT 'N'",
            'alloweditaftercompletion' => "string(1) NULL DEFAULT 'N'"
        ), $options);

        $oDB->createCommand()->addPrimaryKey('{{surveys_groupsettings_pk}}', '{{surveys_groupsettings}}', ['gsid']);

        // insert settings for global level
        $attributes1 = array(
            'gsid' => '0',
            'owner_id' => '1',
            'admin' => 'Administrator',
            'adminemail' => 'your-email@example.net',
            'anonymized' => 'N',
            'format' => 'G',
            'savetimings' => 'N',
            'template' => 'fruity_twentythree',
            'datestamp' => 'Y',
            'usecookie' => 'N',
            'allowregister' => 'N',
            'allowsave' => 'Y',
            'autonumber_start' => '0',
            'autoredirect' => 'N',
            'allowprev' => 'N',
            'printanswers' => 'N',
            'ipaddr' => 'N',
            'ipanonymize' => 'N',
            'refurl' => 'N',
            'savequotaexit' => 'N',
            'showsurveypolicynotice' => '0',
            'publicstatistics' => 'N',
            'publicgraphs' => 'N',
            'listpublic' => 'N',
            'htmlemail' => 'Y',
            'sendconfirmation' => 'Y',
            'tokenanswerspersistence' => 'N',
            'assessments' => 'N',
            'usecaptcha' => 'N',
            'tokenlength' => '15',
            'showxquestions' => 'Y',
            'showgroupinfo' => 'B',
            'shownoanswer' => 'Y',
            'showqnumcode' => 'X',
            'showwelcome' => 'Y',
            'showprogress' => 'Y',
            'questionindex' => '0',
            'navigationdelay' => '0',
            'nokeyboard' => 'N',
            'alloweditaftercompletion' => 'N'
        );
        $oDB->createCommand()->insert("{{surveys_groupsettings}}", $attributes1);

        // insert settings for default survey group
        $attributes2 =  array(
                "gsid" => 1,
                "owner_id" => -1,
                "admin" => "inherit",
                "adminemail" => "inherit",
                "anonymized" => "I",
                "format" => "I",
                "savetimings" => "I",
                "template" => "inherit",
                "datestamp" => "I",
                "usecookie" => "I",
                "allowregister" => "I",
                "allowsave" => "I",
                "autonumber_start" => 0,
                "autoredirect" => "I",
                "allowprev" => "I",
                "printanswers" => "I",
                "ipaddr" => "I",
                'ipanonymize' => "I",
                "refurl" => "I",
                "savequotaexit" => "I",
                "showsurveypolicynotice" => 0,
                "publicstatistics" => "I",
                "publicgraphs" => "I",
                "listpublic" => "I",
                "htmlemail" => "I",
                "sendconfirmation" => "I",
                "tokenanswerspersistence" => "I",
                "assessments" => "I",
                "usecaptcha" => "E",
                "bounce_email" => "inherit",
                "attributedescriptions" => null,
                "emailresponseto" => "inherit",
                "emailnotificationto" => "inherit",
                "tokenlength" => -1,
                "showxquestions" => "I",
                "showgroupinfo" => "I",
                "shownoanswer" => "I",
                "showqnumcode" => "I",
                "showwelcome" => "I",
                "showprogress" => "I",
                "questionindex" => -1,
                "navigationdelay" => -1,
                "nokeyboard" => "I",
                "alloweditaftercompletion" => "I",
        );
        $oDB->createCommand()->insert("{{surveys_groupsettings}}", $attributes2);


        // surveys_languagesettings
        $oDB->createCommand()->createTable('{{surveys_languagesettings}}', array(
            'surveyls_survey_id' => "integer NOT NULL",
            'surveyls_language' => "string(45) NOT NULL DEFAULT 'en'",
            'surveyls_title' => "string(200) NOT NULL",
            'surveyls_description' => "mediumtext NULL",
            'surveyls_welcometext' => "mediumtext NULL",
            'surveyls_endtext' => "mediumtext NULL",
            'surveyls_policy_notice' => "mediumtext NULL",
            'surveyls_policy_error' => "text NULL",
            'surveyls_policy_notice_label' => 'string(192) NULL',
            'surveyls_url' => "text NULL",
            'surveyls_urldescription' => "string(255) NULL",
            'surveyls_email_invite_subj' => "string(255) NULL",
            'surveyls_email_invite' => "mediumtext NULL",
            'surveyls_email_remind_subj' => "string(255) NULL",
            'surveyls_email_remind' => "mediumtext NULL",
            'surveyls_email_register_subj' => "string(255) NULL",
            'surveyls_email_register' => "mediumtext NULL",
            'surveyls_email_confirm_subj' => "string(255) NULL",
            'surveyls_email_confirm' => "mediumtext NULL",
            'surveyls_dateformat' => "integer NOT NULL DEFAULT 1",
            'surveyls_attributecaptions' => "text NULL",
            'surveyls_alias' => "string(100) NULL",
            'email_admin_notification_subj' => "string(255) NULL",
            'email_admin_notification' => "mediumtext NULL",
            'email_admin_responses_subj' => "string(255) NULL",
            'email_admin_responses' => "mediumtext NULL",
            'surveyls_numberformat' => "integer NOT NULL DEFAULT 0",
            'attachments' => "text NULL",
        ), $options);

        $oDB->createCommand()->addPrimaryKey('{{surveys_languagesettings_pk}}', '{{surveys_languagesettings}}', ['surveyls_survey_id', 'surveyls_language']);

        // TODO: varchar(200) is too big for MySQL index, the index will be truncated to 191 chars.
        // If debug = 2, this line will fail (can happen if installing from CLI and you already have a config file).
        $oDB->createCommand()->createIndex('{{idx1_surveys_languagesettings}}', '{{surveys_languagesettings}}', 'surveyls_title', false);


        // survey_links
        $oDB->createCommand()->createTable('{{survey_links}}', array(
            'participant_id' => "string(50) NOT NULL",
            'token_id' => "integer NOT NULL",
            'survey_id' => "integer NOT NULL",
            'date_created' => "datetime",
            'date_invited' => "datetime",
            'date_completed' => "datetime",
        ), $options);

        $oDB->createCommand()->addPrimaryKey('{{survey_links_pk}}', '{{survey_links}}', ['participant_id','token_id','survey_id']);



        // survey_url_parameters
        $oDB->createCommand()->createTable('{{survey_url_parameters}}', array(
            'id' => "pk",
            'sid' => "integer NOT NULL",
            'parameter' => "string(50) NOT NULL",
            'targetqid' => "integer NULL",
            'targetsqid' => "integer NULL",
        ), $options);

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
            'license' =>  "mediumtext",
            'version' =>  "string(45) NULL",
            'api_version' =>  "string(45) NOT NULL",
            'view_folder' =>  "string(45) NOT NULL",
            'files_folder' =>  "string(45) NOT NULL",
            'description' =>  "mediumtext",
            'last_update' =>  "datetime NULL",
            'owner_id' =>  "integer NULL",
            'extends' =>  "string(150)  NULL",
        ), $options);

        $oDB->createCommand()->createIndex('{{idx1_templates}}', '{{templates}}', 'name', false);
        $oDB->createCommand()->createIndex('{{idx2_templates}}', '{{templates}}', 'title', false);
        $oDB->createCommand()->createIndex('{{idx3_templates}}', '{{templates}}', 'owner_id', false);
        $oDB->createCommand()->createIndex('{{idx4_templates}}', '{{templates}}', 'extends', false);

        // NOTE: PLEASE DON'T USE ARRAY COMBINE !!! HARD TO READ AND MODIFY !!!!
        $headerArray = ['name','folder','title','creation_date','author','author_email','author_url','copyright','license','version','api_version','view_folder','files_folder',
        'description','last_update','owner_id','extends'];

        foreach ($templateData = LsDefaultDataSets::getTemplatesData() as $template) {
            $oDB->createCommand()->insert("{{templates}}", $template);
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
            'cssframework_css' => "mediumtext",
            'cssframework_js' => "mediumtext",
            'packages_to_load' => "text",
            'packages_ltr' => "text",
            'packages_rtl' => "text",
        ), $options);

        $oDB->createCommand()->createIndex('{{idx1_template_configuration}}', '{{template_configuration}}', 'template_name', false);
        $oDB->createCommand()->createIndex('{{idx2_template_configuration}}', '{{template_configuration}}', 'sid', false);
        $oDB->createCommand()->createIndex('{{idx3_template_configuration}}', '{{template_configuration}}', 'gsid', false);
        $oDB->createCommand()->createIndex('{{idx4_template_configuration}}', '{{template_configuration}}', 'uid', false);

        foreach ($templateConfigurationData = LsDefaultDataSets::getTemplateConfigurationData() as $templateConfiguration) {
            $oDB->createCommand()->insert("{{template_configuration}}", $templateConfiguration);
        }

        // question_themes
        $oDB->createCommand()->createTable('{{question_themes}}', [
            'id' => "pk",
            'name' => "string(150) NOT NULL",
            'visible' => "string(1) NULL",
            'xml_path' => "string(255) NULL",
            'image_path' => 'string(255) NULL',
            'title' => "string(100) NOT NULL",
            'creation_date' => "datetime NULL",
            'author' => "string(150) NULL",
            'author_email' => "string(255) NULL",
            'author_url' => "string(255) NULL",
            'copyright' => "text",
            'license' => "text",
            'version' => "string(45) NULL",
            'api_version' => "string(45) NOT NULL",
            'description' => "text",
            'last_update' => "datetime NULL",
            'owner_id' => "integer NULL",
            'theme_type' => "string(150)",
            'question_type' => "string(150) NOT NULL",
            'core_theme' => 'boolean',
            'extends' => "string(150) NULL",
            'group' => "string(150)",
            'settings' => "text"
        ], $options);

        $oDB->createCommand()->createIndex('{{idx1_question_themes}}', '{{question_themes}}', 'name', false);

        $baseQuestionThemeEntries = LsDefaultDataSets::getBaseQuestionThemeEntries();
        foreach ($baseQuestionThemeEntries as $baseQuestionThemeEntry) {
            $oDB->createCommand()->insert("{{question_themes}}", $baseQuestionThemeEntry);
        }

        //tutorials
        $oDB->createCommand()->createTable(
            '{{tutorials}}',
            [
                'tid' =>  'pk',
                'name' =>  'string(128)',
                'title' =>  'string(192)',
                'icon' =>  'string(64)',
                'description' =>  'text',
                'active' =>  'integer DEFAULT 0',
                'settings' => 'mediumtext',
                'permission' =>  'string(128) NOT NULL',
                'permission_grade' =>  'string(128) NOT NULL'
            ],
            $options
        );
        $oDB->createCommand()->createIndex('{{idx1_tutorials}}', '{{tutorials}}', 'name', true);

        //tutorial user mapping
        $oDB->createCommand()->createTable('{{map_tutorial_users}}', array(
            'tid' => 'integer NOT NULL',
            'uid' => 'integer NOT NULL',
            'taken' => 'integer DEFAULT 1',
        ), $options);

        $oDB->createCommand()->addPrimaryKey('{{map_tutorial_users_pk}}', '{{map_tutorial_users}}', ['uid','tid']);

        //tutorial entry groups
        $oDB->createCommand()->createTable('{{tutorial_entry_relation}}', array(
            'teid' => 'integer NOT NULL',
            'tid' => 'integer NOT NULL',
            'uid' => 'integer NULL',
            'sid' => 'integer NULL',
        ), $options);

        $oDB->createCommand()->addPrimaryKey('{{tutorial_entry_relation_pk}}', '{{tutorial_entry_relation}}', ['teid','tid']);
        $oDB->createCommand()->createIndex('{{idx1_tutorial_entry_relation}}', '{{tutorial_entry_relation}}', 'uid', false);
        $oDB->createCommand()->createIndex('{{idx2_tutorial_entry_relation}}', '{{tutorial_entry_relation}}', 'sid', false);

        //tutorial entries
        $oDB->createCommand()->createTable(
            '{{tutorial_entries}}',
            [
                'teid' =>  'pk',
                'ordering' =>  'integer',
                'title' =>  'text',
                'content' =>  'mediumtext',
                'settings' => 'mediumtext'
            ],
            $options
        );

        //user_in_groups
        $oDB->createCommand()->createTable('{{user_in_groups}}', array(
            'ugid' => "integer NOT NULL",
            'uid' => "integer NOT NULL",
        ), $options);

        $oDB->createCommand()->addPrimaryKey('{{user_in_groups_pk}}', '{{user_in_groups}}', ['ugid','uid']);

        //user_in_permissionrole
        $oDB->createCommand()->createTable('{{user_in_permissionrole}}', array(
            'ptid' => "integer NOT NULL",
            'uid' => "integer NOT NULL",
        ), $options);

        $oDB->createCommand()->addPrimaryKey('{{user_in_permissionrole_pk}}', '{{user_in_permissionrole}}', ['ptid','uid']);

        // users
        $oDB->createCommand()->createTable('{{users}}', array(
            'uid' => "pk",
            'users_name' => "string(64) NOT NULL DEFAULT ''",
            'password' => "text NOT NULL",
            'full_name' => "string(50) NOT NULL",
            'parent_id' => "integer NOT NULL",
            'lang' => "string(20)",
            'email' => "string(192)",
            'htmleditormode' => "string(7) DEFAULT 'default'",
            'templateeditormode' => "string(7) NOT NULL DEFAULT 'default'",
            'questionselectormode' => "string(7) NOT NULL DEFAULT 'default'",
            'one_time_pw' => "text",
            'dateformat' => "integer NOT NULL DEFAULT 1",
            'last_login' => "datetime NULL",
            'created' => "datetime",
            'modified' => "datetime",
            'validation_key' => 'string(38)',
            'validation_key_expiration' => 'datetime',
            'last_forgot_email_password' => 'datetime',
            'expires' => 'datetime',
            'user_status' => 'integer NOT NULL DEFAULT 1'
        ), $options);

        $oDB->createCommand()->createIndex('{{idx1_users}}', '{{users}}', 'users_name', true);
        $oDB->createCommand()->createIndex('{{idx2_users}}', '{{users}}', 'email', false);


        //user_groups
        $oDB->createCommand()->createTable('{{user_groups}}', array(
            'ugid' => "pk",
            'name' => "string(20) NOT NULL",
            'description' => "text NOT NULL",
            'owner_id' => "integer NOT NULL",
        ), $options);

        $oDB->createCommand()->createIndex('{{idx1_user_groups}}', '{{user_groups}}', 'name', true);

        // asset version
        $oDB->createCommand()->createTable('{{asset_version}}', array(
            'id' => 'pk',
            'path' => 'text NOT NULL',
            'version' => 'integer NOT NULL',
        ), $options);

        // archived_table_settings
        $oDB->createCommand()->createTable('{{archived_table_settings}}', [
            'id' => "pk",
            'survey_id' => "int NOT NULL",
            'user_id' => "int NOT NULL",
            'tbl_name' => "string(255) NOT NULL",
            'tbl_type' => "string(10) NOT NULL",
            'created' => "datetime NOT NULL",
            'properties' => "text NOT NULL",
            'attributes' => "text NULL",
        ], $options);

        // language tables: sourcemessage + message and constraint
        $oDB->createCommand()->createTable(
            '{{source_message}}',
            [
                'id' => "pk",
                'category' => "string(35)",
                'message' => "text",
            ],
            $options
        );
        $oDB->createCommand()->createTable(
            '{{message}}',
            [
                'id' => "integer NOT NULL",
                'language' => "string(50) NOT NULL DEFAULT ''",
                'translation' => "text",
            ],
            $options
        );
        $oDB->createCommand()->addPrimaryKey(
            '{{message_pk}}',
            '{{message}}',
            ['id', 'language']
        );

        // Install default plugins.
        foreach (LsDefaultDataSets::getDefaultPluginsData() as $plugin) {
            unset($plugin['id']);
            $oDB->createCommand()->insert("{{plugins}}", $plugin);
        }

        $oDB->createCommand()->createTable(
            '{{failed_emails}}',
            [
                'id' => "pk",
                'surveyid' => "integer NOT NULL",
                'responseid' => "integer NOT NULL",
                'email_type' => "string(200) NOT NULL",
                'recipient' => "string(320) NOT NULL",
                'language' => "string(20) NOT NULL DEFAULT 'en'",
                'error_message'  => "text",
                'created' => "datetime NOT NULL",  //this one has always to be set to delete after x days ...
                'status' => "string(20) NULL DEFAULT 'SEND FAILED'",
                'updated' => "datetime NULL",
                'resend_vars' => "mediumtext NOT NULL"
            ]
        );

        // Set database version
        $oDB->createCommand()->insert("{{settings_global}}", ['stg_name' => 'DBVersion' , 'stg_value' => $databaseCurrentVersion]);
    } catch (Exception $e) {
        $oTransaction->rollback();
        throw new CHttpException(500, $e->getMessage());
    }
    // Some database (like MySQl) do not support table creation in transaction and will auto-commit
    // Any error in the transaction commit should not be propagated
    try {
        $oTransaction->commit();
    } catch (Exception $e) {
    };
}
