<?php
function createDatabase($oDB){
/**
 * Populate the database for a limesurvey installation
 */
 
////// Current database version: //////
    $databaseCurrentVersion = "315";
///////////////////////////////////////

Yii::app()->loadHelper('database');
// $oDB                        = Yii::app()->getDb();

//answers table
$oTransaction = $oDB->beginTransaction();
    
    $oDB->createCommand()->createTable('{{answers}}', array(
        'qid' => 'pk',
        'code' => 'string(5) not null',
        'answer' => 'text',
        'sortorder' => 'integer', 
        'assessment_value' => 'integer', 
        'language' => 'string(20) DEFAULT "en"'	,
        'scale_id' => 'integer DEFAULT 0',
    ));
    $oDB->createCommand()->createIndex('answers_idx2', '{{answers}}', 'sortorder', false);
    
try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// assessements
$oTransaction = $oDB->beginTransaction();
    
    $oDB->createCommand()->createTable('{{assessments}}', array(
        'id' =>         'pk',
        'sid' =>        'integer DEFAULT 0',
        'scope' =>      'string(5)'	,
        'gid' =>        'integer DEFAULT 0',
        'name' =>       'text',	 
        'minimum' =>    'string(50)',
        'maximum' =>    'string(50)',
        'message' =>    'text',
        'language' =>   'string(20) DEFAULT "en"'
    ));
    $oDB->createCommand()->createIndex('assessments_idx2', '{{assessments}}', 'sid', false);
    $oDB->createCommand()->createIndex('assessments_idx3', '{{assessments}}', 'gid', false);
    
try{ $oTransaction->commit(); }catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}
    
// boxes
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{boxes}}', array(
      'id' => "pk",
      'position' => "integer DEFAULT NULL COMMENT 'position of the box'",
      'url' => "text NOT NULL COMMENT 'URL the box points'",
      'title' => "text NOT NULL COMMENT 'Box title'",
      'ico' => "string(255) DEFAULT NULL COMMENT 'the ico name in font'",
      'desc' => "text NOT NULL COMMENT 'Box description'",
      'page' => "text NOT NULL COMMENT 'Page name where the box should be shown '",
      'usergroup' => "integer NOT NULL COMMENT  'Those boxes will be shown for that user group'"
    ));

    $oDB->createCommand()->insert("{{boxes}}", ['id' => 1, 'position' => 1, 'url' => 'admin/survey/sa/newsurvey', 'title' => 'Create survey', 'ico' => 'add', 'desc' => 'Create a new survey', 'page' => 'welcome', 'usergroup' => '-2']);
    $oDB->createCommand()->insert("{{boxes}}", ['id' => 2, 'position' => 2, 'url' => 'admin/survey/sa/listsurveys', 'title' => 'List surveys', 'ico' => 'list', 'desc' => 'List available surveys', 'page' => 'welcome', 'usergroup' => '-1']);
    $oDB->createCommand()->insert("{{boxes}}", ['id' => 3, 'position' => 3, 'url' => 'admin/globalsettings', 'title' => 'Global settings', 'ico' => 'settings', 'desc' => 'Edit global settings', 'page' => 'welcome', 'usergroup' => '-2']);
    $oDB->createCommand()->insert("{{boxes}}", ['id' => 4, 'position' => 4, 'url' => 'admin/update', 'title' => 'ComfortUpdate', 'ico' => 'shield', 'desc' => 'Stay safe and up to date', 'page' => 'welcome', 'usergroup' => '-2']);
    $oDB->createCommand()->insert("{{boxes}}", ['id' => 5, 'position' => 5, 'url' => 'admin/labels/sa/view', 'title' => 'Label sets', 'ico' => 'label', 'desc' => 'Edit label sets', 'page' => 'welcome', 'usergroup' => '-2']);
    $oDB->createCommand()->insert("{{boxes}}", ['id' => 6, 'position' => 6, 'url' => 'admin/templateoptions', 'title' => 'Templates', 'ico' => 'templates', 'desc' => 'View templates list', 'page' => 'welcome', 'usergroup' => '-2']);

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// conditions
$oTransaction = $oDB->beginTransaction();
    
    $oDB->createCommand()->createTable('{{conditions}}', array(
        'cid' => 'pk',
        'qid' => "integer NOT NULL default '0'",
        'cqid' => "integer NOT NULL default '0'",
        'cfieldname' => "string(50) NOT NULL default ''",
        'method' => "string(5) NOT NULL default ''",
        'value' => "string(255) NOT NULL default ''",
        'scenario' => "integer NOT NULL default '1'"
    ));
    $oDB->createCommand()->createIndex('conditions_idx2', '{{conditions}}', 'qid', false);
    $oDB->createCommand()->createIndex('conditions_idx3', '{{conditions}}', 'cqid', false);
    
    try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}
    
    
// defaultvalues
$oTransaction = $oDB->beginTransaction();
    
    $oDB->createCommand()->createTable('{{defaultvalues}}', array(
        'qid' =>  "integer NOT NULL default '0'",
        'scale_id' =>  "integer NOT NULL default '0'",
        'sqid' =>  "integer NOT NULL default '0'",
        'language' =>  "string(20) NOT NULL",
        'specialtype' =>  "string(20) NOT NULL default ''",
        'defaultvalue' =>  "text",
    ));

    $oDB->createCommand()->addPrimaryKey('defaultvalues_pk', '{{defaultvalues}}', ['qid', 'specialtype', 'language', 'scale_id', 'sqid'], false);

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// expression_errors
$oTransaction = $oDB->beginTransaction();
    
    $oDB->createCommand()->createTable('{{expression_errors}}', array(
        'id' =>  "pk",
        'errortime' =>  "string(50) DEFAULT NULL",
        'sid' =>  "integer DEFAULT NULL",
        'gid' =>  "integer DEFAULT NULL",
        'qid' =>  "integer DEFAULT NULL",
        'gseq' =>  "integer DEFAULT NULL",
        'qseq' =>  "integer DEFAULT NULL",
        'type' =>  "string(50)",
        'eqn' =>  "text",
        'prettyprint' =>  "text",
    ));
try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// failed_login_attempts
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{failed_login_attempts}}', array(
        'id' =>  "pk",
        'ip' =>  "string(40) NOT NULL",
        'last_attempt' =>  "string(20) NOT NULL",
        'number_attempts' =>  "integer NOT NULL",
    ));

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// groups
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{groups}}', array(
        'gid' =>  "pk",
        'sid' =>  "integer NOT NULL default '0'",
        'group_name' =>  "string(100) NOT NULL default ''",
        'group_order' =>  "integer NOT NULL default '0'",
        'description' =>  "text",
        'language' =>  "string(20) default 'en'",
        'randomization_group' =>  "string(20) NOT NULL default ''",
        'grelevance' =>  "text DEFAULT NULL"
    ));
    $oDB->createCommand()->createIndex('idx1_groups', '{{groups}}', 'sid', false);
    $oDB->createCommand()->createIndex('idx2_groups', '{{groups}}', 'group_name', false);
    $oDB->createCommand()->createIndex('idx3_groups', '{{groups}}', 'language', false);

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// labels
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{labels}}', array(
        'lid' =>  "integer NOT NULL default '0'",
        'code' =>  "string(5) NOT NULL default ''",
        'title' =>  "text",
        'sortorder' =>  "integer NOT NULL",
        'language' =>  "string(20) default 'en'",
        'assessment_value' =>  "integer NOT NULL default '0'",
    ));

    $oDB->createCommand()->addPrimaryKey('labels_pk', '{{labels}}', ['lid', 'sortorder', 'language'], false);
    
    $oDB->createCommand()->createIndex('idx1_labels', '{{labels}}', 'code', false);
    $oDB->createCommand()->createIndex('idx2_labels', '{{labels}}', 'language', false);

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// labelsets
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{labelsets}}', array(
        'lid' => 'pk',
        'label_name' =>  "string(100) default ''",
        'languages' =>  "string(200) default 'en'",
    ));

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}


// notifications
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{notifications}}', array(
        'id' =>  "pk",
        'entity' =>  "string(15) NOT NULL COMMENT 'Should be either survey or user'",
        'entity_id' =>  "integer NOT NULL",
        'title' =>  "string(255) NOT NULL",
        'message' =>  "TEXT NOT NULL",
        'status' =>  "string(15) NOT NULL DEFAULT 'new' COMMENT 'new or read'",
        'importance' =>  "integer NOT NULL DEFAULT 1",
        'display_class' =>  "string(31) DEFAULT 'default' COMMENT 'Bootstrap class, like warning, info, success'",
        'hash' =>  "string(64) DEFAULT NULL COMMENT 'Hash of title, message and entity to avoid duplication'",
        'created' =>  "DATETIME NOT NULL",
        'first_read' =>  "DATETIME DEFAULT NULL",
    ));

    $oDB->createCommand()->createIndex('notifications_pk', '{{notifications}}', ['entity', 'entity_id', 'status'], false);
    $oDB->createCommand()->createIndex('idx1_notifications', '{{notifications}}', 'hash', false);
    
try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

//  participants
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{participants}}', array(
        'participant_id' =>  "string(50) NOT NULL",
        'firstname' =>  "string(150) DEFAULT NULL",
        'lastname' =>  "string(150) DEFAULT NULL",
        'email' =>  "text",
        'language' =>  "string(40) DEFAULT NULL",
        'blacklisted' =>  "string(1) NOT NULL",
        'owner_uid' =>  "integer NOT NULL",
        'created_by' =>  "integer NOT NULL",
        'created' =>  "datetime",
        'modified' =>  "datetime",
    ));

    $oDB->createCommand()->addPrimaryKey('participant_pk', '{{participants}}', 'participant_id', false);
    $oDB->createCommand()->createIndex('idx1_participants', '{{participants}}', 'firstname', false);
    $oDB->createCommand()->createIndex('idx2_participants', '{{participants}}', 'lastname', false);
    $oDB->createCommand()->createIndex('idx3_participants', '{{participants}}', 'language', false);
    
    try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}
    
// participant_attribute
$oTransaction = $oDB->beginTransaction();
    
    $oDB->createCommand()->createTable('{{participant_attribute}}', array(
        'participant_id' =>  "string(50) NOT NULL",
        'attribute_id' =>  "integer NOT NULL",
        'value' =>  "text NOT NULL",
    ));

    $oDB->createCommand()->addPrimaryKey('participant_attribute_pk', '{{participant_attribute}}', ['participant_id', 'attribute_id']);
    
    try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}
    
// participant_attribute_names_lang
$oTransaction = $oDB->beginTransaction();
try{

    $oDB->createCommand()->createTable('{{participant_attribute_names_lang}}', array(
        'attribute_id' =>  "integer NOT NULL",
        'attribute_name' =>  "string(255) NOT NULL",
        'lang' =>  "string(20) NOT NULL",
        ));

    $oDB->createCommand()->addPrimaryKey('participant_attribute_names_lang_pk', '{{participant_attribute_names_lang}}', ['attribute_id', 'lang']);

    $oTransaction->commit();

}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}


// participant_attribute_names
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{participant_attribute_names}}', array(
        'attribute_id' =>  "pk",
        'attribute_type' =>  "string(4) NOT NULL",
        'defaultname' =>  "string(255) NOT NULL",
        'visible' =>  "string(5) NOT NULL",
    ));

    $oDB->createCommand()->createIndex('idx_participant_attribute_names', '{{participant_attribute_names}}', ['attribute_id', 'attribute_type']);

 try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

//participant_attribute_values
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{participant_attribute_values}}', array(
        'value_id' => "pk",
        'attribute_id' => "integer NOT NULL",
        'value' => "text NOT NULL",
    ));

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}


//participant_shares
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{participant_shares}}', array(
        'participant_id' =>  "string(50) NOT NULL",
        'share_uid' =>  "integer NOT NULL",
        'date_added' =>  "datetime NOT NULL",
        'can_edit' =>  "string(5) NOT NULL",
    ));

    $oDB->createCommand()->addPrimaryKey('participant_shares_pk', '{{participant_shares}}', ['participant_id', 'share_uid'], false);

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}


// permissions

$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{permissions}}', array(
        'id' =>  "pk",
        'entity' =>  "string(50) NOT NULL",
        'entity_id' =>  "integer NOT NULL",
        'uid' =>  "integer NOT NULL",
        'permission' =>  "string(100) NOT NULL",
        'create_p' =>  "integer NOT NULL default '0'",
        'read_p' =>  "integer NOT NULL default '0'",
        'update_p' =>  "integer NOT NULL default '0'",
        'delete_p' =>  "integer NOT NULL default '0'",
        'import_p' =>  "integer NOT NULL default '0'",
        'export_p' =>  "integer NOT NULL default '0'",
    ));

    $oDB->createCommand()->createIndex('idx1_permissions', '{{permissions}}', ['entity_id', 'entity', 'permission', 'uid'], true); 

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}


// plugins
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{plugins}}', array(
        'id' =>  "pk",
        'name' =>  "string(50) NOT NULL",
        'active' =>  "integer NOT NULL default '0'",
        'version' =>  "string(32) default null",
    ));

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// plugin_settings
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{plugin_settings}}', array(
        'id' => "pk",
        'plugin_id' => "integer NOT NULL",
        'model' => "string(50) NULL",
        'model_id' => "integer NULL",
        'key' => "string(50) NOT NULL",
        'value' => "text NULL",
    ));

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// questions
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{questions}}', array(
        'qid' =>  "pk",
        'parent_qid' =>  "integer NOT NULL default '0'",
        'sid' =>  "integer NOT NULL default '0'",
        'gid' =>  "integer NOT NULL default '0'",
        'type' =>  "string(1) NOT NULL default 'T'",
        'title' =>  "string(20) NOT NULL default ''",
        'question' =>  "text NOT NULL",
        'preg' =>  "text",
        'help' =>  "text",
        'other' =>  "string(1) NOT NULL default 'N'",
        'mandatory' =>  "string(1) default NULL",
        'question_order' =>  "integer NOT NULL",
        'language' =>  "string(20) default 'en'",
        'scale_id' =>  "integer NOT NULL default '0'",
        'same_default' =>  "integer NOT NULL default '0' COMMENT 'Saves if user set to use the same default value across languages in default options dialog'",
        'relevance' =>  "text",
        'modulename' =>  "string(255) DEFAULT NULL",
    ));

    $oDB->createCommand()->createIndex('idx1_questions', '{{questions}}', 'sid', false); 
    $oDB->createCommand()->createIndex('idx2_questions', '{{questions}}', 'gid', false); 
    $oDB->createCommand()->createIndex('idx3_questions', '{{questions}}', 'type', false); 
    $oDB->createCommand()->createIndex('idx4_questions', '{{questions}}', 'title', false); 
    $oDB->createCommand()->createIndex('idx5_questions', '{{questions}}', 'parent_qid', false); 

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// question_attributes
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{question_attributes}}', array(
        'qaid' => "pk",
        'qid' => "integer NOT NULL default '0'",
        'attribute' => "string(50) default NULL",
        'value' => "text default NULL",
        'language' => "string(20) default NULL",
    ));

    $oDB->createCommand()->createIndex('idx1_question_attributes', '{{question_attributes}}', 'qid', false); 
    $oDB->createCommand()->createIndex('idx2_question_attributes', '{{question_attributes}}', 'attribute', false); 

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// quota
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{quota}}', array(
        'id' => "pk",
        'sid' => "integer default NULL",
        'name' => "string(255) default NULL",
        'qlimit' => "integer default NULL",
        'action' => "integer default NULL",
        'active' => "integer NOT NULL default '1'",
        'autoload_url' => "integer NOT NULL default '0'",
    ));

    $oDB->createCommand()->createIndex('idx1_quota', '{{quota}}', 'sid', false); 

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

//quota_languagesettings
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{quota_languagesettings}}', array(
        'quotals_id' => "pk",
        'quotals_quota_id' => "integer NOT NULL default '0'",
        'quotals_language' => "string(45) NOT NULL default 'en'",
        'quotals_name' => "string(255) default NULL",
        'quotals_message' => "text NOT NULL",
        'quotals_url' => "string(255)",
        'quotals_urldescrip' => "string(255)",
    ));

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// quota_members
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{quota_members}}', array(
        'id' => "pk",
        'sid' => "integer default NULL",
        'qid' => "integer default NULL",
        'quota_id' => "integer default NULL",
        'code' => "string(11) default NULL",
    ));

    $oDB->createCommand()->createIndex('idx1_quota_members', '{{quota_members}}', ['sid', 'qid', 'quota_id', 'code'], true); 

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}


// saved_control
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{saved_control}}', array(
        'scid' => "pk",
        'sid' => "integer NOT NULL default '0'",
        'srid' => "integer NOT NULL default '0'",
        'identifier' => "text NOT NULL",
        'access_code' => "text NOT NULL",
        'email' => "string(254)",
        'ip' => "text NOT NULL",
        'saved_thisstep' => "text NOT NULL",
        'status' => "string(1) NOT NULL default ''",
        'saved_date' => "datetime NOT NULL",
        'refurl' => "text",
    ));

    $oDB->createCommand()->createIndex('idx1_saved_control', '{{saved_control}}', 'sid'); 
    $oDB->createCommand()->createIndex('idx2_saved_control', '{{saved_control}}', 'srid'); 

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}


// sessions
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{sessions}}', array(
        'id' => "string(32) NOT NULL",
        'expire' => "integer DEFAULT NULL",
        'data' => "longblob",
    ));

    $oDB->createCommand()->addPrimaryKey('sessions_pk', '{{sessions}}', 'id');

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// settings_global
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{settings_global}}', array(
        'stg_name' =>  "string(50) NOT NULL default ''",
        'stg_value' =>  "text NOT NULL",
    ));

    $oDB->createCommand()->addPrimaryKey('settings_global_pk', '{{settings_global}}', 'stg_name');

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

//settings_user 
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{settings_user}}', array(
        'id' => "pk",
        'uid' => "integer NOT NULL",
        'entity' => "string(15) DEFAULT NULL",
        'entity_id' => "string(31) DEFAULT NULL",
        'stg_name' => "string(63) NOT NULL",
        'stg_value' => "TEXT DEFAULT NULL",
    ));
    
    $oDB->createCommand()->createIndex('idx1_settings_user', '{{settings_user}}', 'uid', false);
    $oDB->createCommand()->createIndex('idx2_settings_user', '{{settings_user}}', 'entity', false);
    $oDB->createCommand()->createIndex('idx3_settings_user', '{{settings_user}}', 'entity_id', false);
    $oDB->createCommand()->createIndex('idx4_settings_user', '{{settings_user}}', 'stg_name', false);

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}


//Surveymenu
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{surveymenu}}', array(
        'id' => "pk",
        'parent_id' => "integer DEFAULT NULL",
        'survey_id' => "integer DEFAULT NULL",
        'user_id' => "integer DEFAULT NULL",
        'ordering' => "integer DEFAULT '0'",
        'level' => "integer DEFAULT '0'",
        'title' => "string(255)  NOT NULL DEFAULT ''",
        'position' => "string(255)  NOT NULL DEFAULT 'side'",
        'description' => "text ",
        'active' => "integer NOT NULL DEFAULT '0'",
        'changed_at' => "datetime NULL",
        'changed_by' => "integer NOT NULL DEFAULT '0'",
        'created_at' => "datetime NOT NULL",
        'created_by' => "integer NOT NULL DEFAULT '0'",
    ));
    
    $oDB->createCommand()->createIndex('idx1_surveymenu', '{{surveymenu}}', 'ordering', false);
    $oDB->createCommand()->createIndex('idx2_surveymenu', '{{surveymenu}}', 'title', false);
    $headerArray = ['id',
'parent_id',
'survey_id',
'user_id',
'ordering',
'level',
'title',
'position',
'description',
'active',
'changed_at',
'changed_by',
'created_at',
'created_by'];
    $oDB->createCommand()->insert("{{surveymenu}}", array_combine($headerArray, [1,NULL,NULL,NULL,0,0,'surveymenu','side','Main survey menu',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0]));
    $oDB->createCommand()->insert("{{surveymenu}}", array_combine($headerArray, [2,NULL,NULL,NULL,0,0,'quickmenue','collapsed','quickmenu',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0]));

    try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// Surveymenu entries
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{surveymenu_entries}}', array(
        'id' =>  "pk",
        'menu_id' =>  "integer DEFAULT NULL",
        'user_id' =>  "integer DEFAULT NULL",
        'ordering' =>  "integer DEFAULT '0'",
        'name' =>  "string(255)  NOT NULL DEFAULT ''",
        'title' =>  "string(255)  NOT NULL DEFAULT ''",
        'menu_title' =>  "string(255)  NOT NULL DEFAULT ''",
        'menu_description' =>  "text ",
        'menu_icon' =>  "string(255)  NOT NULL DEFAULT ''",
        'menu_icon_type' =>  "string(255)  NOT NULL DEFAULT ''",
        'menu_class' =>  "string(255)  NOT NULL DEFAULT ''",
        'menu_link' =>  "string(255)  NOT NULL DEFAULT ''",
        'action' =>  "string(255)  NOT NULL DEFAULT ''",
        'template' =>  "string(255)  NOT NULL DEFAULT ''",
        'partial' =>  "string(255)  NOT NULL DEFAULT ''",
        'classes' =>  "string(255)  NOT NULL DEFAULT ''",
        'permission' =>  "string(255)  NOT NULL DEFAULT ''",
        'permission_grade' =>  "string(255)  DEFAULT NULL",
        'data' =>  "text ",
        'getdatamethod' =>  "string(255)  NOT NULL DEFAULT ''",
        'language' =>  "string(255)  NOT NULL DEFAULT 'en-GB'",
        'active' =>  "integer NOT NULL DEFAULT '0'",
        'changed_at' =>  "datetime NULL",
        'changed_by' =>  "integer NOT NULL DEFAULT '0'",
        'created_at' =>  "datetime NOT NULL",
        'created_by' =>  "integer NOT NULL DEFAULT '0'",
    ));

    $oDB->createCommand()->createIndex('idx1_surveymenu_entries', '{{surveymenu_entries}}', 'menu_id', false);
    $oDB->createCommand()->createIndex('idx2_surveymenu_entries', '{{surveymenu_entries}}', 'ordering', false);
    $oDB->createCommand()->createIndex('idx3_surveymenu_entries', '{{surveymenu_entries}}', 'title', false);
    $oDB->createCommand()->createIndex('idx4_surveymenu_entries', '{{surveymenu_entries}}', 'language', false);
    $oDB->createCommand()->createIndex('idx5_surveymenu_entries', '{{surveymenu_entries}}', 'menu_title', false);

    $headerArray = ['id','menu_id','user_id','ordering','name','title','menu_title','menu_description','menu_icon','menu_icon_type','menu_class','menu_link','action','template','partial','classes','permission','permission_grade','data','getdatamethod','language','active','changed_at','changed_by','created_at','created_by'];
    $basicMenues = [
        [1,1,NULL,1,'overview','Survey overview','Overview','Open general survey overview and quick action','list','fontawesome','','admin/survey/sa/view','','','','','','','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [2,1,NULL,2,'generalsettings','Edit survey general settings','General settings','Open general survey settings','gears','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',NULL,'_generalTabEditSurvey','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [3,1,NULL,3,'surveytexts','Edit survey text elements','Survey texts','Edit survey text elements','file-text-o','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/tab_edit_view','','surveylocale','read',NULL,'_getTextEditData','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [4,1,NULL,4,'template_options','Template options','Template options','Edit Template options for this survey','paint-brush','fontawesome','','admin/templateoptions/sa/updatesurvey','','','','','templates','read','{"render": {"link": { "pjaxed": false, "data": {"surveyid": ["survey","sid"], "gsid":["survey","gsid"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [5,1,NULL,5,'participants','Survey participants','Survey participants','Go to survey participant and token settings','user','fontawesome','','admin/tokens/sa/index/','','','','','surveysettings','update','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [6,1,NULL,6,'presentation','Presentation &amp; navigation settings','Presentation','Edit presentation and navigation settings','eye-slash','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_presentation_panel','','surveylocale','read',NULL,'_tabPresentationNavigation','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [7,1,NULL,7,'publication','Publication and access control settings','Publication &amp; access','Edit settings for publicationa and access control','key','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_publication_panel','','surveylocale','read',NULL,'_tabPublicationAccess','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [8,1,NULL,8,'surveypermissions','Edit surveypermissions','Survey permissions','Edit permissions for this survey','lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [9,1,NULL,9,'tokens','Token handling','Participant tokens','Define how tokens should be treated or generated','users','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read',NULL,'_tabTokens','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [10,1,NULL,10,'quotas','Edit quotas','Survey quotas','Edit quotas for this survey.','tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [11,1,NULL,11,'assessments','Edit assessments','Assessments','Edit and look at the asessements for this survey.','comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [12,1,NULL,12,'notification','Notification and data management settings','Data management','Edit settings for notification and data management','feed','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_notification_panel','','surveylocale','read',NULL,'_tabNotificationDataManagement','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [13,1,NULL,13,'emailtemplates','Email templates','Email templates','Edit the templates for invitation, reminder and registration emails','envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','assessments','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [14,1,NULL,14,'panelintegration','Edit survey panel integration','Panel integration','Define panel integrations for your survey','link','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_integration_panel','','surveylocale','read',NULL,'_tabPanelIntegration','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [15,1,NULL,15,'resources','Add/Edit resources to the survey','Resources','Add/Edit resources to the survey','file','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_resources_panel','','surveylocale','read',NULL,'_tabResourceManagement','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [16,2,NULL,1,'activateSurvey','Activate survey','Activate survey','Activate survey','play','fontawesome','','admin/survey/sa/activate','','','','','surveyactivation','update','{\"render\": {\"isActive\": false, \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [17,2,NULL,2,'deactivateSurvey','Stop this survey','Stop this survey','Stop this survey','stop','fontawesome','','admin/survey/sa/deactivate','','','','','surveyactivation','update','{\"render\": {\"isActive\": true, \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [18,2,NULL,3,'testSurvey','Go to survey','Go to survey','Go to survey','cog','fontawesome','','survey/index/','','','','','','','{\"render\"\: {\"link\"\: {\"external\"\: true, \"data\"\: {\"sid\"\: [\"survey\",\"sid\"], \"newtest\"\: \"Y\", \"lang\"\: [\"survey\",\"language\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [19,2,NULL,4,'listQuestions','List questions','List questions','List questions','list','fontawesome','','admin/survey/sa/listquestions','','','','','surveycontent','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [20,2,NULL,5,'listQuestionGroups','List question groups','List question groups','List question groups','th-list','fontawesome','','admin/survey/sa/listquestiongroups','','','','','surveycontent','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [21,2,NULL,6,'generalsettings','Edit survey general settings','General settings','Open general survey settings','gears','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',NULL,'_generalTabEditSurvey','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [22,2,NULL,7,'surveypermissions','Edit surveypermissions','Survey permissions','Edit permissions for this survey','lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [23,2,NULL,8,'quotas','Edit quotas','Survey quotas','Edit quotas for this survey.','tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [24,2,NULL,9,'assessments','Edit assessments','Assessments','Edit and look at the asessements for this survey.','comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [25,2,NULL,10,'emailtemplates','Email templates','Email templates','Edit the templates for invitation, reminder and registration emails','envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','surveylocale','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [26,2,NULL,11,'surveyLogicFile','Survey logic file','Survey logic file','Survey logic file','sitemap','fontawesome','','admin/expressions/sa/survey_logic_file/','','','','','surveycontent','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [27,2,NULL,12,'tokens','Token handling','Participant tokens','Define how tokens should be treated or generated','user','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','_tabTokens','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [28,2,NULL,13,'cpdb','Central participant database','Central participant database','Central participant database','users','fontawesome','','admin/participants/sa/displayParticipants','','','','','tokens','read','{render\: {\"link\"\: {}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [29,2,NULL,14,'responses','Responses','Responses','Responses','icon-browse','iconclass','','admin/responses/sa/browse/','','','','','responses','read','{\"render\"\: {\"isActive\"\: true}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [30,2,NULL,15,'statistics','Statistics','Statistics','Statistics','bar-chart','fontawesome','','admin/statistics/sa/index/','','','','','statistics','read','{\"render\"\: {\"isActive\"\: true}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
        [31,2,NULL,16,'reorder','Reorder questions/question groups','Reorder questions/question groups','Reorder questions/question groups','icon-organize','iconclass','','admin/survey/sa/organize/','','','','','surveycontent','update','{\"render\": {\"isActive\": false, \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0],
    ];
    foreach($basicMenues as $basicMenu){
        $oDB->createCommand()->insert("{{surveymenu_entries}}", array_combine($headerArray, $basicMenu));
    }


try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}


// surveys

$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{surveys}}', array(
        'sid' => "integer NOT NULL",
        'owner_id' => "integer NOT NULL",
        'gsid' => "integer default '1'",
        'admin' => "string(50) default NULL",
        'active' => "string(1) NOT NULL default 'N'",
        'expires' => "datetime default NULL",
        'startdate' => "datetime default NULL",
        'adminemail' => "string(254) default NULL",
        'anonymized' => "string(1) NOT NULL default 'N'",
        'faxto' => "string(20) default NULL",
        'format' => "string(1) default NULL",
        'savetimings' => "string(1) NOT NULL default 'N'",
        'template' => "string(100) default 'default'",
        'language' => "string(50) default NULL",
        'additional_languages' => "string(255) default NULL",
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
        'datecreated' => "date default NULL",
        'publicstatistics' => "string(1) NOT NULL default 'N'",
        'publicgraphs' => "string(1) NOT NULL default 'N'",
        'listpublic' => "string(1) NOT NULL default 'N'",
        'htmlemail' => "string(1) NOT NULL default 'N'",
        'sendconfirmation' => "string(1) NOT NULL default 'Y'",
        'tokenanswerspersistence' => "string(1) NOT NULL default 'N'",
        'assessments' => "string(1) NOT NULL default 'N'",
        'usecaptcha' => "string(1) NOT NULL default 'N'",
        'usetokens' => "string(1) NOT NULL default 'N'",
        'bounce_email' => "string(254) default NULL",
        'attributedescriptions' => "text",
        'emailresponseto' => "text default NULL",
        'emailnotificationto' => "text default NULL",
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
        'googleanalyticsstyle' => "string(1) DEFAULT NULL",
        'googleanalyticsapikey' => "string(25) DEFAULT NULL",
    ));

    $oDB->createCommand()->addPrimaryKey('surveys_pk', '{{surveys}}', 'sid');
    
    $oDB->createCommand()->createIndex('idx1_surveys', '{{surveys}}', 'owner_id', false);
    $oDB->createCommand()->createIndex('idx2_surveys', '{{surveys}}', 'gsid', false);

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// surveys_groups

$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{surveys_groups}}', array(
        'gsid' => "pk",
        'name' => "string(45) NOT NULL",
        'title' => "string(100) DEFAULT NULL",
        'template' => "string(128) DEFAULT 'default'",
        'description' => "text ",
        'sortorder' => "integer NOT NULL",
        'owner_uid' => "integer DEFAULT NULL",
        'parent_id' => "integer DEFAULT NULL",
        'created' => "datetime DEFAULT NULL",
        'modified' => "datetime DEFAULT NULL",
        'created_by' => "integer NOT NULL"
    ));

    $oDB->createCommand()->createIndex('idx1_surveys_groups', '{{surveys_groups}}', 'name', false);    
    $oDB->createCommand()->createIndex('idx2_surveys_groups', '{{surveys_groups}}', 'title', false);    

    $oDB->createCommand()->insert("{{surveys_groups}}", [
        'gsid' => 1,
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

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}


// surveys_languagesettings

$oTransaction = $oDB->beginTransaction();

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
        'attachments' => "text DEFAULT NULL",
    ));

    $oDB->createCommand()->addPrimaryKey('surveys_languagesettings_pk', '{{surveys_languagesettings}}', ['surveyls_survey_id', 'surveyls_language']);

    $oDB->createCommand()->createIndex('idx1_surveys_languagesettings', '{{surveys_languagesettings}}', 'surveyls_title', false);    

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// survey_links
$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{survey_links}}', array(
        'participant_id' => "string(50) NOT NULL",
        'token_id' => "integer NOT NULL",
        'survey_id' => "integer NOT NULL",
        'date_created' => "datetime",
        'date_invited' => "datetime",
        'date_completed' => "datetime",
    ));

    $oDB->createCommand()->addPrimaryKey('survey_links_pk', '{{survey_links}}', ['participant_id','token_id','survey_id']);

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// survey_url_parameters

$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{survey_url_parameters}}', array(
        'id' => "pk",
        'sid' => "integer NOT NULL",
        'parameter' => "string(50) NOT NULL",
        'targetqid' => "integer NULL",
        'targetsqid' => "integer NULL",
    ));

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}


// templates

$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{templates}}', array(
        'id' =>  "pk",
        'name' =>  "string(150) NOT NULL",
        'folder' =>  "string(45) DEFAULT NULL",
        'title' =>  "string(100) NOT NULL",
        'creation_date' =>  "datetime NOT NULL",
        'author' =>  "string(150) DEFAULT NULL",
        'author_email' =>  "string(255) DEFAULT NULL",
        'author_url' =>  "string(255) DEFAULT NULL",
        'copyright' =>  "text ",
        'license' =>  "text ",
        'version' =>  "string(45) DEFAULT NULL",
        'api_version' =>  "string(45) NOT NULL",
        'view_folder' =>  "string(45) NOT NULL",
        'files_folder' =>  "string(45) NOT NULL",
        'description' =>  "text ",
        'last_update' =>  "datetime DEFAULT NULL",
        'owner_id' =>  "integer DEFAULT NULL",
        'extends' =>  "string(150)  DEFAULT NULL",
    ));

    $oDB->createCommand()->createIndex('idx1_templates', '{{templates}}', 'name', false);    
    $oDB->createCommand()->createIndex('idx2_templates', '{{templates}}', 'title', false);    
    $oDB->createCommand()->createIndex('idx3_templates', '{{templates}}', 'owner_id', false);    
    $oDB->createCommand()->createIndex('idx4_templates', '{{templates}}', 'extends', false);    
    
    $headerArray = ['id','name','folder','title','creation_date','author','author_email','author_url','copyright','license','version','api_version','view_folder','files_folder','description','last_update','owner_id','extends'];
    $oDB->createCommand()->insert("{{templates}}", array_combine($headerArray, [1,'default', 'default', 'Advanced Template', date('Y-m-d H:i:s'), 'Louis Gac', 'louis.gac@limesurvey.org', 'https://www.limesurvey.org/', 'Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.', 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.', '1.0', '3.0', 'views', 'files', "<strong>LimeSurvey Advanced Template</strong><br>A template with custom options to show what it's possible to do with the new engines. Each template provider will be able to offer its own option page (loaded from template)", NULL, 1, '']));
    $oDB->createCommand()->insert("{{templates}}", array_combine($headerArray,[2,'minimal', 'minimal', 'Minimal Template', date('Y-m-d H:i:s'), 'Louis Gac', 'louis.gac@limesurvey.org', 'https://www.limesurvey.org/', 'Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.', 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.', '1.0', '3.0', 'views', 'files', '<strong>LimeSurvey Minimal Template</strong><br>A clean and simple base that can be used by developers to create their own solution.', NULL, 1, '']));
    $oDB->createCommand()->insert("{{templates}}", array_combine($headerArray,[3,'material', 'material', 'Material Template', date('Y-m-d H:i:s'), 'Louis Gac', 'louis.gac@limesurvey.org', 'https://www.limesurvey.org/', 'Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.', 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.', '1.0', '3.0', 'views', 'files', '<strong>LimeSurvey Advanced Template</strong><br> A template extending default, to show the inheritance concept. Notice the options, differents from Default.<br><small>uses FezVrasta\'s Material design theme for Bootstrap 3</small>', NULL, 1, 'default']));
    
    
try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// template_configuration

$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{template_configuration}}', array(
        'id' => "pk",
        'template_name' => "string(150)  NOT NULL",
        'sid' => "integer DEFAULT NULL",
        'gsid' => "integer DEFAULT NULL",
        'uid' => "integer DEFAULT NULL",
        'files_css' => "text",
        'files_js' => "text",
        'files_print_css' => "text",
        'options' => "text ",
        'cssframework_name' => "string(45) DEFAULT NULL",
        'cssframework_css' => "text",
        'cssframework_js' => "text",
        'packages_to_load' => "text",
        'packages_ltr' => "text",
        'packages_rtl' => "text",
    ));

    $oDB->createCommand()->createIndex('idx1_template_configuration', '{{template_configuration}}', 'template_name', false);    
    $oDB->createCommand()->createIndex('idx2_template_configuration', '{{template_configuration}}', 'sid', false);    
    $oDB->createCommand()->createIndex('idx3_template_configuration', '{{template_configuration}}', 'gsid', false);    
    $oDB->createCommand()->createIndex('idx4_template_configuration', '{{template_configuration}}', 'uid', false);    

    $headerArray = ['id','template_name','sid','gsid','uid','files_css','files_js','files_print_css','options','cssframework_name','cssframework_css','cssframework_js','packages_to_load','packages_ltr','packages_rtl'];
    $oDB->createCommand()->insert("{{template_configuration}}", array_combine($headerArray,[1,'default',NULL,NULL,NULL,'{"add": ["css/template.css", "css/animate.css"]}','{"add": ["scripts/template.js"]}','{"add":"css/print_template.css",}','{"ajaxmode":"off","brandlogo":"on", "boxcontainer":"on", "backgroundimage":"on","animatebody":"on","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}','bootstrap','{"replace": [["css/bootstrap.css","css/flatly.css"]]}','','','','']));
    $oDB->createCommand()->insert("{{template_configuration}}", array_combine($headerArray,[2,'minimal',NULL,NULL,NULL,'{"add": ["css/template.css"]}','{"add": ["scripts/template.js"]}','{"add":"css/print_template.css",}','{}','bootstrap','{}','','','','']));
    $oDB->createCommand()->insert("{{template_configuration}}", array_combine($headerArray,[3,'material',NULL,NULL,NULL,'{"add": ["css/template.css", "css/bootstrap-material-design.css", "css/ripples.min.css"]}','{"add": ["scripts/template.js", "scripts/material.js", "scripts/ripples.min.js"]}','{"add":"css/print_template.css",}','{"ajaxmode":"off","brandlogo":"on", "animatebody":"on","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}','bootstrap','{"replace": [["css/bootstrap.css","css/bootstrap.css"]]}','','','','']));
   
    
try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}
    

//user_in_groups

$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{user_in_groups}}', array(
        'ugid' => "integer NOT NULL",
        'uid' => "integer NOT NULL",
    ));
    
    $oDB->createCommand()->addPrimaryKey('user_in_groups_pk', '{{user_in_groups}}', ['ugid','uid']);

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// users

$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{users}}', array(
        'uid' => "pk",
        'users_name' => "string(64) NOT NULL default ''",
        'password' => "binary NOT NULL",
        'full_name' => "string(50) NOT NULL",
        'parent_id' => "integer NOT NULL",
        'lang' => "string(20)",
        'email' => "string(254)",
        'htmleditormode' => "string(7) default 'default'",
        'templateeditormode' => "string(7) NOT NULL default 'default'",
        'questionselectormode' => "string(7) NOT NULL default 'default'",
        'one_time_pw' => "binary",
        'dateformat' => "integer NOT NULL DEFAULT 1",
        'created' => "datetime",
        'modified' => "datetime",
    ));

    $oDB->createCommand()->createIndex('idx1_users', '{{users}}', 'users_name', true);   
    $oDB->createCommand()->createIndex('idx2_users', '{{users}}', 'email', false);   

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}



//user_groups

$oTransaction = $oDB->beginTransaction();

    $oDB->createCommand()->createTable('{{user_groups}}', array(
        'ugid' => "pk",
        'name' => "string(20) NOT NULL",
        'description' => "TEXT NOT NULL",
        'owner_id' => "integer NOT NULL",
    ));

    $oDB->createCommand()->createIndex('idx1_user_groups', '{{user_groups}}', 'name', true);   

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}

// Set database version

$oTransaction = $oDB->beginTransaction();
    
    $oDB->createCommand()->insert("{{settings_global}}", ['stg_name'=> 'DBVersion' , 'stg_value' => $databaseCurrentVersion]);

try{$oTransaction->commit();}catch(Exception $e){throw new CHttpException(print_r(["ERROR" => $e, "transaction" => $oDB->currentTransaction()]),true);}
}
