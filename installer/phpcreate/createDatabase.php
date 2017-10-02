<?php

/**
 * Populate the database for a limesurvey installation
 */

Yii::app()->loadHelper('database');
$sUserTemplateRootDir       = Yii::app()->getConfig('usertemplaterootdir');
$sStandardTemplateRootDir   = Yii::app()->getConfig('standardtemplaterootdir');
$oDB                        = Yii::app()->getDb();


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
    $oDB->createCommand()->createIndex('sortorder', '{{answers}}', 'sortorder', false);
    
$oTransaction->commit();

// assessements
$oTransaction = $oDB->beginTransaction();
    
    $oDB->createCommand()->createTable('{{answers}}', array(
        'id' =>         'pk',
        'sid' =>        'integer DEFAULT 0',
        'scope' =>      varchar(5)	 
        'gid' =>        integer [0]	 
        'name' =>       text	 
        'minimum' =>    varchar(50) []	 
        'maximum' =>    varchar(50) []	 
        'message' =>    text	 
        'language' =>   varchar(20) [en]
    );
    $oDB->createCommand()->createIndex('sortorder', '{{answers}}', 'sortorder', false);
    
$oTransaction->commit();
