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
    // $oDB                        = Yii::app()->getDb();

    $oTransaction = $oDB->beginTransaction();
    try{
        //answers table
        $oDB->createCommand()->createTable('{{answers}}', array(
            'qid' => 'integer NOT NULL',
            'code' => 'string(5) NOT NULL',
            'answer' => 'text NOT NULL',
            'sortorder' => 'integer NOT NULL',
            'assessment_value' => 'integer NOT NULL',
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

        $oDB->createCommand()->insert("{{boxes}}", ['position' => 1, 'url' => 'admin/survey/sa/newsurvey', 'title' => 'Create survey', 'ico' => 'add', 'desc' => 'Create a new survey', 'page' => 'welcome', 'usergroup' => '-2']);
        $oDB->createCommand()->insert("{{boxes}}", ['position' => 2, 'url' => 'admin/survey/sa/listsurveys', 'title' => 'List surveys', 'ico' => 'list', 'desc' => 'List available surveys', 'page' => 'welcome', 'usergroup' => '-1']);
        $oDB->createCommand()->insert("{{boxes}}", ['position' => 3, 'url' => 'admin/globalsettings', 'title' => 'Global settings', 'ico' => 'settings', 'desc' => 'Edit global settings', 'page' => 'welcome', 'usergroup' => '-2']);
        $oDB->createCommand()->insert("{{boxes}}", ['position' => 4, 'url' => 'admin/update', 'title' => 'ComfortUpdate', 'ico' => 'shield', 'desc' => 'Stay safe and up to date', 'page' => 'welcome', 'usergroup' => '-2']);
        $oDB->createCommand()->insert("{{boxes}}", ['position' => 5, 'url' => 'admin/labels/sa/view', 'title' => 'Label sets', 'ico' => 'label', 'desc' => 'Edit label sets', 'page' => 'welcome', 'usergroup' => '-2']);
        $oDB->createCommand()->insert("{{boxes}}", ['position' => 6, 'url' => 'admin/themeoptions', 'title' => 'Themes', 'ico' => 'templates', 'desc' => 'Themes', 'page' => 'welcome', 'usergroup' => '-2']);


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
            'language' =>  "string(20) default 'en'",
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
            'message' =>  "TEXT NOT NULL",
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
            'active' =>  "boolean NOT NULL default 0",
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
            'language' =>  "string(20) default 'en'",
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
            'data' => "binary",
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
            'stg_value' => "TEXT NULL",
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
            'name' => "string(128)  NOT NULL",
            'ordering' => "integer NULL DEFAULT '0'",
            'level' => "integer NULL DEFAULT '0'",
            'title' => "string(192)  NOT NULL DEFAULT ''",
            'position' => "string(192)  NOT NULL DEFAULT 'side'",
            'description' => "text ",
            'active' => "boolean NOT NULL DEFAULT '0'",
            'changed_at' => "datetime",
            'changed_by' => "integer NOT NULL DEFAULT '0'",
            'created_at' => "datetime",
            'created_by' => "integer NOT NULL DEFAULT '0'",
        ));

        $oDB->createCommand()->createIndex('{{surveymenu_name}}', '{{surveymenu}}', 'name', true);
        $oDB->createCommand()->createIndex('{{idx2_surveymenu}}', '{{surveymenu}}', 'title', false);

        $headerArray = ['parent_id','survey_id','user_id','ordering','level','name','title','position','description','active','changed_at','changed_by','created_at','created_by'];
        $oDB->createCommand()->insert("{{surveymenu}}", array_combine($headerArray, [NULL,NULL,NULL,0,0,'mainmenu','Survey menu','side','Main survey menu',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0]));
        $oDB->createCommand()->insert("{{surveymenu}}", array_combine($headerArray, [NULL,NULL,NULL,0,0,'quickmenu','Quick menu','collapsed','Quick menu',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0]));
        $oDB->createCommand()->insert("{{surveymenu}}", array_combine($headerArray, [1,NULL,NULL,0,1,'pluginmenu','Plugin menu','side','Plugin menu',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0]));


        // Surveymenu entries

        $oDB->createCommand()->createTable('{{surveymenu_entries}}', array(
            'id' =>  "pk",
            'menu_id' =>  "integer NULL",
            'user_id' =>  "integer NULL",
            'ordering' =>  "integer DEFAULT '0'",
            'name' =>  "string(168)  NOT NULL DEFAULT ''",
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
            'active' =>  "boolean NOT NULL DEFAULT '0'",
            'changed_at' =>  "datetime NULL",
            'changed_by' =>  "integer NOT NULL DEFAULT '0'",
            'created_at' =>  "datetime NULL",
            'created_by' =>  "integer NOT NULL DEFAULT '0'",
        ));

        $oDB->createCommand()->createIndex('{{idx1_surveymenu_entries}}', '{{surveymenu_entries}}', 'menu_id', false);
        $oDB->createCommand()->createIndex('{{idx5_surveymenu_entries}}', '{{surveymenu_entries}}', 'menu_title', false);
        $oDB->createCommand()->createIndex('{{surveymenu_entries_name}}', '{{surveymenu_entries}}', 'name', true);

        $headerArray = ['menu_id','user_id','ordering','name','title','menu_title','menu_description','menu_icon','menu_icon_type','menu_class','menu_link','action','template','partial','classes','permission','permission_grade','data','getdatamethod','language','active','changed_at','changed_by','created_at','created_by'];
        $basicMenues = [
            [1,NULL,1,'overview','Survey overview','Overview','Open general survey overview and quick action','list','fontawesome','','admin/survey/sa/view','','','','','','','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [1,NULL,2,'generalsettings','General survey settings','General settings','Open general survey settings','gears','fontawesome','','','updatesurveylocalesettings_generalsettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',NULL,'_generalTabEditSurvey','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [1,NULL,3,'surveytexts','Survey text elements','Text elements','Survey text elements','file-text-o','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/tab_edit_view','','surveylocale','read',NULL,'_getTextEditData','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [1,NULL,4,'theme_options','Theme options','Theme options','Edit theme options for this survey','paint-brush','fontawesome','','admin/themeoptions/sa/updatesurvey','','','','','themes','read','{"render": {"link": { "pjaxed": true, "data": {"surveyid": ["survey","sid"], "gsid":["survey","gsid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [1,NULL,5,'participants','Survey participants','Survey participants','Go to survey participant and token settings','user','fontawesome','','admin/tokens/sa/index/','','','','','surveysettings','update','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [1,NULL,6,'presentation','Presentation &amp; navigation settings','Presentation','Edit presentation and navigation settings','eye-slash','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_presentation_panel','','surveylocale','read',NULL,'_tabPresentationNavigation','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [1,NULL,7,'publication','Publication and access control settings','Publication &amp; access','Edit settings for publicationa and access control','key','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_publication_panel','','surveylocale','read',NULL,'_tabPublicationAccess','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [1,NULL,8,'surveypermissions','Edit surveypermissions','Survey permissions','Edit permissions for this survey','lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [1,NULL,9,'tokens','Survey participant settings','Participant settings','Set additional options for survey participants','users','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read',NULL,'_tabTokens','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [1,NULL,10,'quotas','Edit quotas','Quotas','Edit quotas for this survey.','tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [1,NULL,11,'assessments','Edit assessments','Assessments','Edit and look at the assessements for this survey.','comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [1,NULL,12,'notification','Notification and data management settings','Data management','Edit settings for notification and data management','feed','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_notification_panel','','surveylocale','read',NULL,'_tabNotificationDataManagement','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [1,NULL,13,'emailtemplates','Email templates','Email templates','Edit the templates for invitation, reminder and registration emails','envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','assessments','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [1,NULL,14,'panelintegration','Edit survey panel integration','Panel integration','Define panel integrations for your survey','link','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_integration_panel','','surveylocale','read','{"render": {"link": { "pjaxed": false}}}','_tabPanelIntegration','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [1,NULL,15,'resources','Add/Edit resources to the survey','Resources','Add/Edit resources to the survey','file','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_resources_panel','','surveylocale','read',NULL,'_tabResourceManagement','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [2,NULL,1,'activateSurvey','Activate survey','Activate survey','Activate survey','play','fontawesome','','admin/survey/sa/activate','','','','','surveyactivation','update','{"render": {"isActive": false, "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [2,NULL,2,'deactivateSurvey','Stop this survey','Stop this survey','Stop this survey','stop','fontawesome','','admin/survey/sa/deactivate','','','','','surveyactivation','update','{"render": {"isActive": true, "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [2,NULL,3,'testSurvey','Go to survey','Go to survey','Go to survey','cog','fontawesome','','survey/index/','','','','','','','{"render": {"link": {"external": true, "data": {"sid": ["survey","sid"], "newtest": "Y", "lang": ["survey","language"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [2,NULL,4,'listQuestions','List questions','List questions','List questions','list','fontawesome','','admin/survey/sa/listquestions','','','','','surveycontent','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [2,NULL,5,'listQuestionGroups','List question groups','List question groups','List question groups','th-list','fontawesome','','admin/survey/sa/listquestiongroups','','','','','surveycontent','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [2,NULL,6,'generalsettings_collapsed','General survey settings','General settings','Open general survey settings','gears','fontawesome','','','updatesurveylocalesettings_generalsettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',NULL,'_generalTabEditSurvey','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [2,NULL,7,'surveypermissions_collapsed','Edit surveypermissions','Survey permissions','Edit permissions for this survey','lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [2,NULL,8,'quotas_collapsed','Edit quotas','Survey quotas','Edit quotas for this survey.','tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [2,NULL,9,'assessments_collapsed','Edit assessments','Assessments','Edit and look at the assessements for this survey.','comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [2,NULL,10,'emailtemplates_collapsed','Email templates','Email templates','Edit the templates for invitation, reminder and registration emails','envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','surveylocale','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [2,NULL,11,'surveyLogicFile','Survey logic file','Survey logic file','Survey logic file','sitemap','fontawesome','','admin/expressions/sa/survey_logic_file/','','','','','surveycontent','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [2,NULL,12,'tokens_collapsed','Survey participant settings','Participant settings','Set additional options for survey participants','user','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read','{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}','_tabTokens','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [2,NULL,13,'cpdb','Central participant database','Central participant database','Central participant database','users','fontawesome','','admin/participants/sa/displayParticipants','','','','','tokens','read','{"render": {"link": {}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [2,NULL,14,'responses','Responses','Responses','Responses','icon-browse','iconclass','','admin/responses/sa/browse/','','','','','responses','read','{"render": {"isActive": true, "link": {"data": {"surveyid": ["survey", "sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [2,NULL,15,'statistics','Statistics','Statistics','Statistics','bar-chart','fontawesome','','admin/statistics/sa/index/','','','','','statistics','read','{"render": {"isActive": true, "link": {"data": {"surveyid": ["survey", "sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [2,NULL,16,'reorder','Reorder questions/question groups','Reorder questions/question groups','Reorder questions/question groups','icon-organize','iconclass','','admin/survey/sa/organize/','','','','','surveycontent','update','{"render": {"isActive": false, "link": {"data": {"surveyid": ["survey","sid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
            [3,NULL,16,'plugins','Simple plugin settings', 'Simple plugins', 'Edit simple plugin settings','plug','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_plugins_panel','','surveysettings','read','{"render": {"link": {"data": {"surveyid": ["survey","sid"]}}}}','_pluginTabSurvey','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        ];

        foreach($basicMenues as $basicMenu){
            $oDB->createCommand()->insert("{{surveymenu_entries}}", array_combine($headerArray, $basicMenu));
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
            'owner_uid' => "integer NULL",
            'parent_id' => "integer NULL",
            'created' => "datetime NULL",
            'modified' => "datetime NULL",
            'created_by' => "integer NOT NULL"
        ));

        $oDB->createCommand()->createIndex('{{idx1_surveys_groups}}', '{{surveys_groups}}', 'name', false);
        $oDB->createCommand()->createIndex('{{idx2_surveys_groups}}', '{{surveys_groups}}', 'title', false);

        $oDB->createCommand()->insert("{{surveys_groups}}", [
            'name' => 'default',
            'title' => 'Default Survey Group',
            'template' =>  NULL,
            'description' => 'LimeSurvey core default survey group',
            'sortorder' => 0,
            'owner_uid' => 1,
            'parent_id' => NULL,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
            'created_by' => 1
        ]);



        // surveys_languagesettings
        $oDB->createCommand()->createTable('{{surveys_languagesettings}}', array(
            'surveyls_survey_id' => "integer NOT NULL",
            'surveyls_language' => "string(45) NOT NULL DEFAULT 'en'",
            'surveyls_title' => "string(200) NOT NULL",
            'surveyls_description' => "TEXT NULL",
            'surveyls_welcometext' => "TEXT NULL",
            'surveyls_endtext' => "TEXT NULL",
            'surveyls_url' => "TEXT NULL",
            'surveyls_urldescription' => "string(255) NULL",
            'surveyls_email_invite_subj' => "string(255) NULL",
            'surveyls_email_invite' => "TEXT NULL",
            'surveyls_email_remind_subj' => "string(255) NULL",
            'surveyls_email_remind' => "TEXT NULL",
            'surveyls_email_register_subj' => "string(255) NULL",
            'surveyls_email_register' => "TEXT NULL",
            'surveyls_email_confirm_subj' => "string(255) NULL",
            'surveyls_email_confirm' => "TEXT NULL",
            'surveyls_dateformat' => "integer NOT NULL DEFAULT 1",
            'surveyls_attributecaptions' => "TEXT NULL",
            'email_admin_notification_subj' => "string(255) NULL",
            'email_admin_notification' => "TEXT NULL",
            'email_admin_responses_subj' => "string(255) NULL",
            'email_admin_responses' => "TEXT NULL",
            'surveyls_numberformat' => "INT NOT NULL DEFAULT 0",
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


        $oDB->createCommand()->insert("{{templates}}", [
            'name'          => 'vanilla',
            'folder'        => 'vanilla',
            'title'         => 'Bootstrap Vanilla Theme',
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
            'last_update'   => NULL,
            'owner_id'      => 1,
            'extends'       => '',
        ]);

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

        $headerArray = ['template_name','sid','gsid','uid','files_css','files_js','files_print_css','options',
        'cssframework_name','','cssframework_js','packages_to_load','packages_ltr','packages_rtl'];

        $oDB->createCommand()->insert("{{template_configuration}}", [
            'template_name'     =>  'vanilla',
            'sid'               =>  NULL,
            'gsid'              =>  NULL,
            'uid'               =>  NULL,
            'files_css'         => '{"add":["css/ajaxify.css","css/theme.css","css/custom.css"]}',
            'files_js'          =>  '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
            'files_print_css'   => '{"add":["css/print_theme.css"]}',
            'options'           => '{"ajaxmode":"on","brandlogo":"on","brandlogofile":"./files/logo.png","font":"noto"}',
            'cssframework_name' => 'bootstrap',
            'cssframework_css'  => '{}',
            'cssframework_js'   => '',
            'packages_to_load'  => '{"add":["pjax","font-noto"]}',
            'packages_ltr'      => NULL,
            'packages_rtl'      => NULL]);


        //tutorials
        $oDB->createCommand()->createTable(
            '{{tutorials}}',[
                'tid' =>  'pk',
                'name' =>  'string(128)',
                'title' =>  'string(192)',
                'icon' =>  'string(64)',
                'description' =>  'text',
                'active' =>  'int DEFAULT 0',
                'settings' => 'text',
                'permission' =>  'string(128) NOT NULL',
                'permission_grade' =>  'string(128) NOT NULL'
            ]
        );
        $oDB->createCommand()->createIndex('{{idx1_tutorials}}', '{{tutorials}}', 'name', true);
        
        $oDB->createCommand()->insert('{{tutorials}}', array(
            'tid' => 1,
            'name' => 'firstStartTour',
            'title' => 'First start tour',
            'icon' => 'fa-rocket',
            'description' => 'The first start tour to get your first feeling into LimeSurvey',
            'active' => 1,
            'settings' => json_encode(array(
                'debug' => true,
                'orphan' => true,
                'keyboard' => false,
                'template' => "<div class='popover tour lstutorial__template--mainContainer'> <div class='arrow'></div> <h3 class='popover-title lstutorial__template--title'></h3> <div class='popover-content lstutorial__template--content'></div> <div class='popover-navigation lstutorial__template--navigation'>     <div class='btn-group col-xs-8' role='group' aria-label='...'>         <button class='btn btn-default col-xs-6' data-role='prev'>".gT('Previous')."</button>         <button class='btn btn-primary col-xs-6' data-role='next'>".gT('Next')."</button>     </div>     <div class='col-xs-4'>         <button class='btn btn-warning' data-role='end'>".gT('End tour')."</button>     </div> </div></div>",
                'onShown' => "(function(tour){ console.log($('#notif-container').children()); $('#notif-container').children().remove(); })",
                'onEnd' => "(function(){ $.post(LS.data.baseUrl+(LS.data.urlFormat == 'path' ? '/admin/tutorial/sa/triggerfinished/tid/1' : '?r=admin/tutorial/sa/triggerfinished/tid/1'))})",
                'onStart' => "(function(){window.location.href=LS.data.baseUrl+(LS.data.urlFormat == 'path' ? '/admin/index' : '?r=admin/index')})"
            )),
            'permission' => 'survey',
            'permission_grade' => 'create'
    
        ));

        //tutorial user mapping
        $oDB->createCommand()->createTable('{{map_tutorial_users}}', array(
            'tid' => 'int NOT NULL',
            'uid' => 'int DEFAULT NULL',
            'taken' => 'boolean DEFAULT 1',
        ));
    
        $oDB->createCommand()->addPrimaryKey('{{map_tutorial_users_pk}}', '{{map_tutorial_users}}', ['uid','tid']);
    
        //tutorial entry groups
        $oDB->createCommand()->createTable('{{tutorial_entry_relation}}', array(
            'teid' => 'int NOT NULL',
            'tid' => 'int NOT NULL',
            'uid' => 'int DEFAULT NULL',
            'sid' => 'int DEFAULT NULL',
        ));

        $oDB->createCommand()->addPrimaryKey('{{tutorial_entry_relation_pk}}', '{{tutorial_entry_relation}}', ['teid','tid']);
        $oDB->createCommand()->createIndex('{{idx1_tutorial_entry_relation}}', '{{tutorial_entry_relation}}', 'uid', false);
        $oDB->createCommand()->createIndex('{{idx2_tutorial_entry_relation}}', '{{tutorial_entry_relation}}', 'sid', false);

        //tutorial entries
        $oDB->createCommand()->createTable(
            '{{tutorial_entries}}',[
                'teid' =>  'pk',
                'ordering' =>  'int',
                'title' =>  'text',
                'content' =>  'text',
                'settings' => 'text'
            ]
        );

        $contentArrays = array(
            array(
                'teid' => 1,
                'ordering' => 1,
                'title' => 'Welcome to LimeSurvey!',
                'content' => "This tour will help you to easily get a basic understanding of LimeSurvey."."<br/>"
                    ."We would like to help you with a quick tour of the most essential functions and features.",
                'settings' => json_encode(array (
                    'element' => '#lime-logo',
                    'orphan' => true,
                    'path' => '/admin/index',
                    'placement' => 'bottom',
                    'redirect' => false,
                    'onShow' => "(function(tour){ $('#welcomeModal').modal('hide'); })"
                    ))
                ),
            array(
                'teid' => 2,
                'ordering' => 2,
                'title' => 'The basic functions',
                'content' => "The three top boxes are the most basic functions of LimeSurvey."."<br/>"
                    ."From left to right it should be 'Create survey', 'List surveys' and 'Global settings'. Best we start by creating a survey."
                    .'<p class="alert bg-warning">'."Click on the 'Create survey' box - or 'Next' in this tutorial".'</p>',
                'settings' => json_encode(array(
                    'element' => '.selector__create_survey',
                    'path' => '/admin/index',
                    'reflex' => true,
                    'redirect' => true,
                    'onShow' => "(function(tour){ $('#welcomeModal').modal('hide'); $('.selector__create_survey').on('click', function(){tour.next();});})"
                ))
            ),
            array(
                'teid' => 3,
                'ordering' => 3,
                'title' => 'The survey title',
                'content' => "This is the title of your survey."."<br/>"
                ."Your participants will see this title in the browser's title bar and on the welcome screen."
                ."<p class='bg-warning alert'>"."You have to put in at least a title for the survey to be saved.".'</p>',
                'settings' => json_encode(array(
                    'path' => '/admin/survey/sa/newsurvey',
                    'element' => '#surveyls_title',
                    'redirect' => true,
                ))
            ),
            array(
                'teid' => 4,
                'ordering' => 4,
                'title' => 'The survey description',
                'content' => "In this field you may type a short description of your survey."."<br/>"
                ."The text inserted here will be displayed on the welcome screen, which is the first thing that your respondents will see when they access your survey..".' '
                ."Describe your survey, but do not ask any question yet.",
                'settings' => json_encode(array(
                    'element' => '#cke_description',
                    'path' => '/admin/survey/sa/newsurvey',
                    'placement' => 'top',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 5,
                'ordering' => 5,
                'title' => 'Create a sample question and question group',
                'content' => "We will be creating a question group and a question in this tutorial. There is need to automatically create it.",
                'settings' => json_encode(array(
                    'element' => '.bootstrap-switch-id-createsample',
                    'path' => '/admin/survey/sa/newsurvey',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 6,
                'ordering' => 6,
                'title' => 'The welcome message',
                'content' => "This message is shown directly below the survey description on the welcome page. You may leave this blank for now but it is a good way to introduce your participants to the survey.",
                'settings' => json_encode(array(
                    'element' => '#cke_welcome',
                    'placement' => 'top',
                    'path' => '/admin/survey/sa/newsurvey',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 7,
                'ordering' => 7,
                'title' => 'The end message',
                'content' => "This message is shown at the end of your survey to every participant. It's a great way to say thank you or give some links or hints where to go next.",
                'settings' => json_encode(array(
                    'element' => '#cke_endtext',
                    'path' => '/admin/survey/sa/newsurvey',
                    'placement' => 'top',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 8,
                'ordering' => 8,
                'title' => 'Now save your survey',
                'content' => "You may play around with more settings, but let's save and start adding questions to your survey now. Just click on 'Save'.",
                'settings' => json_encode(array(
                    'element' => '#save-form-button',
                    'path' => '/admin/survey/sa/newsurvey',
                    'placement' => 'bottom',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    $('#save-form-button').trigger('click');
                                    return Promise.resolve(tour);
                                })",
                ))
            ),
            array(
                'teid' => 9,
                'ordering' => 9,
                'title' => 'The sidebar',
                'content' => 'This is the sidebar.'.'<br/>'
                .'All important settings can be reached in this sidebar.'.'<br/>'
                .'The most important settings of your survey can be reached from this sidebar: the survey settings menu and the survey structure menu.'.' '
                .gT('You may resize it to fit your screen to easily navigate through the available options.'
                .' If the size of the sidebar is too small, the options get collapsed and the quick-menu is displayed.'
                .' If you wish to work from the quick-menu, either click on the arrow button or drag it to the left.'),
                'settings' => json_encode(array(
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}']],
                    'element' => '#sidebar',
                    'placement' => 'right',
                    'redirect' => false,
                    'onShow' => "(function(tour){
                                    return Promise.resolve(tour);
                                })"
                ))
            ),
            array(
                'teid' => 10,
                'ordering' => 10,
                'title' => 'The settings tab with the survey menu',
                'content' => 'If you click on this tab, the survey settings menu will be displayed.'.' '
                .'The most important settings of your survey are accessible from this menu.'. '<br/>'
                .'If you want to know more about them, check our manual.',
                'settings' => json_encode(array(
                    'element' => '#adminpanel__sidebar--selectorSettingsButton',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 11,
                'ordering' => 11,
                'title' => 'The top bar',
                'content' => 'This is the top bar.'.'<br/>'
                .'This bar will change as you move through the functionalities.'.' '
                .'The current bar corresponds to the "overview" tab. It contains the most important LimeSurvey functionalities such as preview and activate survey.',
                'settings' => json_encode(array(
                    'element' => '#surveybarid',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 12,
                'ordering' => 12,
                'title' => 'The survey structure',
                'content' => 'This is the structure view of your survey. Here you can see all your question groups and questions.',
                'settings' => json_encode(array(
                    'element' => '#adminpanel__sidebar--selectorStructureButton',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'redirect' => false,
                    'onShow' => "(function(tour){
                                    $('#adminpanel__sidebar--selectorStructureButton').trigger('click');
                                    return Promise.resolve(tour);
                                })",
                ))
            ),
            array(
                'teid' => 13,
                'ordering' => 13,
                'title' => "Let's add a question group",
                'content' => "What good would your survey be without questions?".'<br/>'
                .'In LimeSurvey a survey is organized in question groups and questions. To begin creating questions, we first need a question group.'
                .'<p class="alert bg-warning">'."Click on the 'Add question group' button".'</p>',
                'settings' => json_encode(array(
                    'element' => '#adminpanel__sidebar--selectorCreateQuestionGroup',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'right',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    document.location.href = $('#adminpanel__sidebar--selectorCreateQuestionGroup').attr('href');
                                    return Promise.resolve(tour);
                                })",
                ))
            ),
            array(
                'teid' => 14,
                'ordering' => 14,
                'title' => 'Enter a title for your first question group',
                'content' => 'The title of the question group is visible to your survey participants (this setting can be changed later and it cannot be empty). '
                .'Question groups are important because they allow the survey administrators to logically group the questions. '
                .'By default, each question group (including its questions) is shown on its own page (this setting can be changed later).',
                'settings' => json_encode(array(
                    'element' => '#group_name_en',
                    'path' => ['/admin/questiongroups/sa/add', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 15,
                'ordering' => 15,
                'title' => 'A description for your question group',
                'content' => 'This description is also visible to your participants.'.'<br/>'
                .'You do not need to add a description to your question group, but sometimes it makes sense to add a little extra information for your participants.',
                'settings' => json_encode(array(
                    'element' => 'label[for=description_en]',
                    'path' => ['/admin/questiongroups/sa/add', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'top',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 16,
                'ordering' => 16,
                'title' => 'Advanced settings',
                'content' => "For now it's best to leave these additional settings as they are. If you want to know more about randomization and relevance settings, have a look at our manual.",
                'settings' => json_encode(array(
                    'element' => '#randomization_group',
                    'path' => ['/admin/questiongroups/sa/add', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'left',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 17,
                'ordering' => 17,
                'title' => 'Save and add a new question',
                'content' => "Now when you are finished click on 'Save and add question'.".'<br/>'
                .'This will directly add a question to the current question group.'
                .'<p class="alert bg-warning">'."Now click on 'Save and add question'.".'</p>',
                'settings' => json_encode(array(
                    'element' => '#save-and-new-question-button',
                    'path' => ['/admin/questiongroups/sa/add', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    $('#save-and-new-question-button').trigger('click');
                                    return Promise.resolve(tour);
                                })",
                ))
            ),
            array(
                'teid' => 18,
                'ordering' => 18,
                'title' => 'The title of your question',
                'content' => 
                "This code is normally not shown to your participants, still it is necessary and has to be unique for the survey.".'<br>'
                ."This code is also the name of the variable that will be exported to SPSS or Excel."
                .'<p class="alert bg-warning">'."Please type in a code that consists only of letters and numbers, and doesn't start with a number.".'</p>',
                'settings' => json_encode(array(
                    'element' => '#title',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'top',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 19,
                'ordering' => 19,
                'title' => 'The actual question text',
                'content' => 'The content of this box is the actual question text shown to your participants.'.' '
                .'It may be empty, but that is not recommended. You may use all the power of our WYSIWYG editor to make your question shine.',
                'settings' => json_encode(array(
                    'element' => '#cke_question_en',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'top',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 20,
                'ordering' => 20,
                'title' => 'An additional help text for your question',
                'content' => 'You can add some additional help text to your question. '
                .'If you decide not to offer any additional question hints, then no help text will be displayed to your respondents.',
                'settings' => json_encode(array(
                    'element' => '#cke_help_en',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'top',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 21,
                'ordering' => 21,
                'title' => 'Set your question type.',
                'content' => "LimeSurvey offers you a lot of different question types.".'<br/>'
                ."As you can see, the preselected question type is the 'Long free text' one. We will use in this example the 'Array' question type.".'<br/>'
                ."This type of question allows you to add multiple subquestions and a set of answers."
                .'<p class="alert bg-warning">'."Please select the 'Array'-type.".'</p>',
                'settings' => json_encode(array(
                    'element' => '#question_type_button',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'left',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 22,
                'ordering' => 22,
                'title' => 'Now save the created question',
                'content' => 'Next, we will create subquestions and answer options.'.'<br/>'
                    .'Please remember that in order to have a valid code, it must contain only letters and numbers, '
                    .'also please check that it starts with a letter.',
                'settings' => json_encode(array(
                    'element' => '#save-button',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'left',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    $('#question_type').val('F');
                                    $('#save-button').trigger('click');
                                    return Promise.resolve(tour);
                                })",
                ))
            ),
            array(
                'teid' => 23,
                'ordering' => 23,
                'title' => 'The question bar',
                'content' => 'This is the question bar.'.'<br/>'
                    .'The most important question-related options are displayed here.'.'<br/>'
                    .'The availability of options is related to the type of question you previously chose.',
                'settings' => json_encode(array(
                    'element' => '#questionbarid',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 24,
                'ordering' => 24,
                'title' => 'Add some subquestions to your question',
                'content' => "The array question is a type that creates a matrix for the participant.".'<br/>'
                    ."To fully use it, you have to add subquestions as well as answer options.".'<br/>'
                    ."Let's start with subquestions."
                    .'<p class="alert bg-warning">'."Click on the 'Edit subquestions' button.".'</p>',
                'settings' => json_encode(array(
                    'element' => '#adminpanel__topbar--selectorAddSubquestions',
                    'placement' => 'bottom',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    document.location.href = $('#adminpanel__topbar--selectorAddSubquestions').attr('href');
                                    return Promise.resolve(tour);
                                })",
                ))
            ),
            array(
                'teid' => 25,
                'ordering' => 25,
                'title' => 'Edit subquestions',
                'content' => "You should add some subquestions for your question here.".'<br/>'
                ."Every row is one subquestion. We recommend the usage of logical or numerical codes for subquestions.".' '
                ."Your participants cannot see the subquestion code, only the subquestion text itself."
                ."<p class='bg-info alert'>"."Pro tip: The subquestion may even contain HTML code.".'</p>',
                'settings' => json_encode(array(
                    'element' => '#rowcontainer',
                    'path' => ['admin/questions/sa/subquestions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}'],
                    'placement' => 'bottom',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 26,
                'ordering' => 26,
                'title' => 'Add subquestion row',
                'content' => sprintf('Click on the plus sign %s to add another subquestion to your question.', '<i class="icon-add text-success"></i>')
                ."<p class='bg-warning alert'>".'Please add at least two subquestions'."</p>",
                'settings' => json_encode(array(
                    'element' => '#rowcontainer>tr:first-of-type .btnaddanswer',
                    'path' => ['admin/questions/sa/subquestions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}'],
                    'placement' => 'left',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 27,
                'ordering' => 27,
                'title' => 'Now save the subquestions',
                'content' => "You may save empty subquestions, but that would be pointless."
                ."<p class='bg-warning alert'>"."Save and close now and let's edit the answer options.".'</p>',
                'settings' => json_encode(array(
                    'element' => '#save-and-close-button',
                    'path' => ['admin/questions/sa/subquestions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}'],
                    'placement' => 'left',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    $('#save-and-close-button').trigger('click');
                                    return Promise.resolve(tour);
                                })"
                ))
            ),
            array(
                'teid' => 28,
                'ordering' => 28,
                'title' => 'Add some answer options to your question',
                'content' => "Now that we've got some subquestions, we have to add answer options as well.".'<br/>'
                ."The answer options will be shown for each subquestion."
                .'<p class="alert bg-warning">'."Click on the 'Edit answer options' button.".'</p>',
                'settings' => json_encode(array(
                    'element' => '#adminpanel__topbar--selectorAddAnswerOptions',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    document.location.href = $('#adminpanel__topbar--selectorAddAnswerOptions').attr('href');
                                    return Promise.resolve(tour);
                                })",
                ))
            ),
            array(
                'teid' => 29,
                'ordering' => 29,
                'title' => 'Edit answer options',
                'content' => "As you can see, editing answer options is quite similar to editing subquestions.".'<br/>'
                .'Remember the plus button <i class="icon-add text-success"></i>?'.'<br/>'
                .'<p class="alert bg-warning">'."Please add at least two answer options to proceed.".'</p>',
                'settings' => json_encode(array(
                    'element' => '#rowcontainer',
                    'path' => ['admin/questions/sa/answeroptions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}'],
                    'placement' => 'bottom',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 30,
                'ordering' => 30,
                'title' => 'Now save the answer options',
                'content' => "Click on 'Save and close' or 'Next' to proceed.",
                'settings' => json_encode(array(
                    'element' => '#save-and-close-button',
                    'path' => ['admin/questions/sa/answeroptions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}'],
                    'placement' => 'left',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    $('#save-and-close-button').trigger('click');
                                    return Promise.resolve(tour);
                                })"
                ))
            ),
            array(
                'teid' => 31,
                'ordering' => 31,
                'title' => 'Preview survey',
                'content' => "Now is the time to preview your first survey.".'<br/>'
                ."Just click on this button and a new window will open, where you can test run your survey.".'<br/>'
                ."Please be aware that your answers will not be saved, because the survey isn't active yet."
                .'<p class="alert bg-warning">'."Click on 'Preview survey' and return to this window when you are done testing.".'</p>',
                'settings' => json_encode(array(
                    'element' => '.selector__topbar--previewSurvey',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'redirect' => false,
                ))
            ),
            array(
                'teid' => 32,
                'ordering' => 32,
                'title' => 'Easy navigation with the "breadcrumbs"',
                'content' => 'You can see the "breadcrumbs" In the top bar of the admin interface.'.'<br/>'
                ."They represent an easy way to get back to any previous setting, and provide a general overview of where you are."
                .'<p class="alert bg-warning">'."Click on the name of your survey to get back to the survey settings overview.".'</p>',
                'settings' => json_encode(array(
                    'element' => '#breadcrumb-container',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'reflex' => '#breadcrumb__survey--overview',
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    document.location.href = $('#breadcrumb__survey--overview').attr('href');
                                    return Promise.resolve(tour);
                                })",
                ))
            ),
            array(
                'teid' => 33,
                'ordering' => 33,
                'title' => 'Finally, activate your survey',
                'content' => "Now, activate your survey.".'<br/>'
                ."You can create as many surveys as you like."
                .'<p class="alert bg-warning">'."Click on 'Activate this survey'".'</p>',
                'settings' => json_encode(array(
                    'element' => '#ls-activate-survey',
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                            document.location.href = $('#ls-activate-survey').attr('href');
                            return Promise.resolve(tour);
                        })",
                ))
            ),
            array(
                'teid' => 34,
                'ordering' => 34,
                'title' => 'Activation settings',
                'content' => 'These settings cannot be changed once the survey is online.'.'<br/>'
                ."For this simple survey the default settings are ok, but read the disclaimer carefully when you activate your own surveys.".'<br/>'
                ."For more information consult our manual, or our forums."
                .'<p class="alert bg-warning">'.'Now click on "Save & activate survey"'.'</p>',
                'settings' => json_encode(array(
                    'element' => '#activateSurvey__basicSettings--proceed',
                    'path' => ['/admin/survey/sa/activate', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                            $('#activateSurvey__basicSettings--proceed').trigger('click');
                            return Promise.resolve(tour);
                        })",
                ))
            ),
            array(
                'teid' => 35,
                'ordering' => 35,
                'title' => ('Activate token table'),
                'content' => "Here you can select to start your survey in closed access mode."."<br/>"
                ."For our simple survey it is better to start in open access mode."."<br/>"
                ."The closed access mode needs a participant list, which you may create by clicking on the menu entry 'Participants'."."<br/>"
                ."For more information please consult our manual or our forum."
                .'<p class="alert bg-warning">'."Click on 'No, thanks'".'</p>',
                'settings' => json_encode(array(
                    'element' => '#activateTokenTable__selector--no',
                    'path' => ['/admin/survey/sa/activate', ['surveyid' => '[0-9]{4,25}']],
                    'placement' => 'bottom',
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                            $('#activateTokenTable__selector--no').trigger('click');
                            return Promise.resolve(tour);
                        })",
                ))
            ),
            array(
                'teid' => 36,
                'ordering' => 36,
                'title' => 'Share this link',
                'content' => "Just share this link with some of your friends and of course, test it yourself."
                .'<p class="alert bg-success lstutorial__typography--white">'."Thank you for taking the tour!".'</p>',
                'settings' => json_encode(array(
                    'element' => '#adminpanel__surveysummary--mainLanguageLink',
                    'path' => ['/'.'(index.php)?'],
                    'placement' => 'top',
                    'redirect' => false
                ))
            ),
        );

        foreach($contentArrays as $contentArray) {
            $oDB->createCommand()->insert('{{tutorial_entries}}', $contentArray);
            $oDB->createCommand()->insert('{{tutorial_entry_relation}}', array('tid' => 1, 'teid' => $contentArray['teid']));
        }

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
            'one_time_pw' => "binary",
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
            'description' => "TEXT NOT NULL",
            'owner_id' => "integer NOT NULL",
        ));

        $oDB->createCommand()->createIndex('{{idx1_user_groups}}', '{{user_groups}}', 'name', true);


        // Set database version
        $oDB->createCommand()->insert("{{settings_global}}", ['stg_name'=> 'DBVersion' , 'stg_value' => $databaseCurrentVersion]);

        $oTransaction->commit();
        return true;
    }catch(Exception $e){

        $oTransaction->rollback();
        throw new CHttpException(500, $e->getMessage()." ".$e->getTraceAsString());
    }
    return false;
}
