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
    $aCriticalDBVersions = array();
    $aAllUpdates         = range($iOldDBVersion+1,Yii::app()->getConfig('dbversionnumber'));

    // If trying to update silenty check if it is really possible
    if ($bSilent && (count(array_intersect($aCriticalDBVersions,$aAllUpdates))>0)){
        return false;
    }

    /// This function does anything necessary to upgrade
    /// older versions to match current functionality
    global $modifyoutput;

    Yii::app()->loadHelper('database');
    $sUserTemplateRootDir       = Yii::app()->getConfig('usertemplaterootdir');
    $sStandardTemplateRootDir   = Yii::app()->getConfig('standardtemplaterootdir');
    $oDB                        = Yii::app()->getDb();
    $oDB->schemaCachingDuration = 0; // Deactivate schema caching
    Yii::app()->setConfig('Updating',true);

    try{
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
                'title' => 'string not null',  // varchar(255) in postgres
                'message' => 'text not null',
                'status' => "string(15) not null default 'new' ",
                'importance' => 'integer default 1',
                'display_class' => "string(31) default 'default'",
                'created' => 'datetime not null',
                'first_read' => 'datetime null'
            ));
            $oDB->createCommand()->createIndex('notif_index', '{{notifications}}', 'entity, entity_id, status', false);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>259),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 260) {
            $oTransaction = $oDB->beginTransaction();
            alterColumn('{{participant_attribute_names}}','defaultname',"string(255)",false);
            alterColumn('{{participant_attribute_names_lang}}','attribute_name',"string(255)",false);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>260),"stg_name='DBVersion'");
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
            $oDB->createCommand()->createIndex('notif_hash_index', '{{notifications}}', 'hash', false);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>261),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 262) {
            $oTransaction = $oDB->beginTransaction();
            alterColumn('{{settings_global}}','stg_value',"text",false);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>262),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 263) {
            $oTransaction = $oDB->beginTransaction();
            // Dummy version update for hash column in installation SQL.
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>263),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Add seed column in all active survey tables
         * Might take time to execute
         * @since 2016-09-01
         */
        if ($iOldDBVersion < 290) {

            $oTransaction = $oDB->beginTransaction();

            // Loop all surveys
            $surveys = Survey::model()->findAll();
            foreach ($surveys as $survey)
            {
                $prefix = Yii::app()->getDb()->tablePrefix;
                $tableName = $prefix . 'survey_' . $survey->sid;

                // If survey has active table, create seed column
                $table = Yii::app()->db->schema->getTable($tableName);
                if ($table)
                {
                    if (!isset($table->columns['seed']))
                    {
                        Yii::app()->db->createCommand()->addColumn($tableName, 'seed', 'string(31)');
                    }
                    else
                    {
                        continue;
                    }

                    // RAND is RANDOM in Postgres
                    switch (Yii::app()->db->driverName)
                    {
                        case 'pgsql':
                            Yii::app()->db->createCommand("UPDATE $tableName SET seed = ROUND(RANDOM() * 10000000)")->execute();
                            break;
                        default:
                            Yii::app()->db->createCommand("UPDATE $tableName SET seed = ROUND(RAND() * 10000000, 0)")->execute();
                            break;
                    }
                }
            }
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>290),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Plugin JSON config file
         * @since 2016-08-22
         */
        if ($iOldDBVersion < 291)
        {
            $oTransaction = $oDB->beginTransaction();

            addColumn('{{plugins}}', 'version', 'string(32)');

            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>291),"stg_name='DBVersion'");
            $oTransaction->commit();
        }


        /**
         * Survey menue table
         * @since 2017-07-03
         */
        if ($iOldDBVersion < 293) {
            $oTransaction = $oDB->beginTransaction();
            createSurveyMenuTable293($oDB);
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>293),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Survey menue table update
         * @since 2017-07-03
         */
        if ($iOldDBVersion < 294) {
            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->addColumn('{{surveymenu}}', 'position', "string(255) DEFAULT 'side'");

            $oDB->createCommand()->truncateTable('{{surveymenu_entries}}');
            $colsToAdd = array("menu_id","order","name","title","menu_title","menu_description","menu_icon","menu_icon_type","menu_class","menu_link","action","template","partial","classes","permission","permission_grade","data","getdatamethod","language","changed_at","changed_by","created_at","created_by");
            $rowsToAdd = array(
                array(1,1,'overview','Survey overview','Overview','Open general survey overview and quick action','list','fontawesome','','admin/survey/sa/view','','','','','','',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(1,2,'generalsettings','Edit survey general settings','General settings','Open general survey settings','gears','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',NULL,'_generalTabEditSurvey','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(1,3,'surveytexts','Edit survey text elements','Survey texts','Edit survey text elements','file-text-o','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/tab_edit_view','','surveylocale','read',NULL,'_getTextEditData','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(1,4,'participants','Survey participants','Survey participants','Go to survey participant and token settings','user','fontawesome','','admin/tokens/sa/index/','','','','','surveysettings','update',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(1,4,'presentation','Presentation &amp; navigation settings','Presentation','Edit presentation and navigation settings','eye-slash','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_presentation_panel','','surveylocale','read',NULL,'_tabPresentationNavigation','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(1,5,'publication','Publication and access control settings','Publication &amp; access','Edit settings for publicationa and access control','key','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_publication_panel','','surveylocale','read',NULL,'_tabPublicationAccess','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(1,6,'surveypermissions','Edit surveypermissions','Survey permissions','Edit permissions for this survey','lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(1,7,'tokens','Token handling','Participant tokens','Define how tokens should be treated or generated','users','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read',NULL,'_tabTokens','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(1,8,'quotas','Edit quotas','Survey quotas','Edit quotas for this survey.','tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(1,9,'assessments','Edit assessments','Assessments','Edit and look at the asessements for this survey.','comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(1,10,'notification','Notification and data management settings','Data management','Edit settings for notification and data management','feed','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_notification_panel','','surveylocale','read',NULL,'_tabNotificationDataManagement','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(1,11,'emailtemplates','Email templates','Email templates','Edit the templates for invitation, reminder and registration emails','envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','assessments','read',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(1,12,'panelintegration','Edit survey panel integration','Panel integration','Define panel integrations for your survey','link','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_integration_panel','','surveylocale','read',NULL,'_tabPanelIntegration','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(1,13,'ressources','Add/Edit ressources to the survey','Ressources','Add/Edit ressources to the survey','file','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_resources_panel','','surveylocale','read',NULL,'_tabResourceManagement','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0)
            );
            foreach($rowsToAdd as $row){
                $oDB->createCommand()->insert('{{surveymenu_entries}}', array_combine($colsToAdd,$row));
            }

            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>294),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Survey menue table update
         * @since 2017-07-12
         */
        if ($iOldDBVersion < 296) {
            $oTransaction = $oDB->beginTransaction();

            addColumn('{{surveymenu}}', 'user_id', "int DEFAULT NULL");
            addColumn('{{surveymenu_entries}}', 'user_id', "int DEFAULT NULL");

            $oDB->createCommand()->insert('{{surveymenu}}', array('id' => 2,'parent_id' => NULL,'survey_id' => NULL,'order' => 1,'level' => 0,'title' => 'quickmenu','description' => 'Quickmenu', 'position'=>'collapsed', 'changed_at' => date('Y-m-d H:i:s'),'changed_by' => 0,'created_at' => date('Y-m-d H:i:s'),'created_by' => 0));

            $colsToAdd = array("menu_id","user_id","order","name","title","menu_title","menu_description","menu_icon","menu_icon_type","menu_class","menu_link","action","template","partial","classes","permission","permission_grade","data","getdatamethod","language","changed_at","changed_by","created_at","created_by");
            $rowsToAdd = array(
                array(2,NULL,1,'activateSurvey','Activate survey','Activate survey','Activate survey','play','fontawesome','','admin/survey/sa/activate','','','','','surveyactivation','update','{\"render\": {\"isActive\": false}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(2,NULL,2,'deactivateSurvey','Stop this survey','Stop this survey','Stop this survey','stop','fontawesome','','admin/survey/sa/deactivate','','','','','surveyactivation','update','{\"render\": {\"isActive\": true}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(2,NULL,3,'testSurvey','Go to survey','Go to survey','Go to survey','cog','fontawesome','','survey/index','','','','','','','{\"render\": {\"link\": {\"external\": true, \"data\": {\"sid\": [\"this\",\"sid\"], \"newtest\": \"Y\", \"lang\": [\"this\",\"language\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(2,NULL,4,'listQuestions','List questions','List questions','List questions','list','fontawesome','','admin/survey/sa/listquestions','','','','','surveycontent','read',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(2,NULL,5,'listQuestionGroups','List question groups','List question groups','List question groups','th-list','fontawesome','','admin/survey/sa/listquestiongroups','','','','','surveycontent','read',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(2,NULL,6,'generalsettings','Edit survey general settings','General settings','Open general survey settings','gears','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',NULL,'_generalTabEditSurvey','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(2,NULL,7,'surveypermissions','Edit surveypermissions','Survey permissions','Edit permissions for this survey','lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(2,NULL,8,'quotas','Edit quotas','Survey quotas','Edit quotas for this survey.','tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(2,NULL,9,'assessments','Edit assessments','Assessments','Edit and look at the asessements for this survey.','comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(2,NULL,10,'emailtemplates','Email templates','Email templates','Edit the templates for invitation, reminder and registration emails','envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','surveylocale','read',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(2,NULL,11,'surveyLogicFile','Survey logic file','Survey logic file','Survey logic file','sitemap','fontawesome','','admin/expressions/sa/survey_logic_file/','','','','','surveycontent','read',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(2,NULL,12,'tokens','Token handling','Participant tokens','Define how tokens should be treated or generated','user','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read',NULL,'_tabTokens','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(2,NULL,13,'cpdb','Central participant database','Central participant database','Central participant database','users','fontawesome','','admin/participants/sa/displayParticipants','','','','','tokens','read','{render: {\"link\": {}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(2,NULL,14,'responses','Responses','Responses','Responses','icon-browse','iconclass','','admin/responses/sa/browse/','','','','','responses','read','{\"render\": {\"isActive\": true}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(2,NULL,15,'statistics','Statistics','Statistics','Statistics','bar-chart','fontawesome','','admin/statistics/sa/index/','','','','','statistics','read','{\"render\": {\"isActive\": true}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
                array(2,NULL,16,'reorder','Reorder questions/question groups','Reorder questions/question groups','Reorder questions/question groups','icon-organize','iconclass','','admin/survey/sa/organize/','','','','','surveycontent','update','{\"render\": {\"isActive\": false}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
            );
            foreach($rowsToAdd as $row){
                    $combined = array_combine($colsToAdd,$row);
                    $oDB->createCommand()->insert('{{surveymenu_entries}}', $combined);
            }



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
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>298),"stg_name='DBVersion'");
        }

        /**
         * Template tables
         * @since 2017-07-12
         */
        if ($iOldDBVersion < 304) {
            $oTransaction = $oDB->beginTransaction();
            upgradeTemplateTables304($oDB);
            $oTransaction->commit();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>304),"stg_name='DBVersion'");
        }

        /**
         * Update to sidemenu rendering
         */
        if ($iOldDBVersion < 305) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{surveymenu_entries}}',
                array('data'=> "{\"render\": {\"link\": {\"external\": true, \"data\": {\"sid\": [\"survey\",\"sid\"], \"newtest\": \"Y\", \"lang\": [\"survey\",\"language\"]}}}}"),
                    "id=17"
            );
            $oDB->createCommand()->update('{{surveymenu_entries}}',
                array('data'=> "{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}} } }"),
                    "id IN (1,4,7,9,10,12,18,19,21,22,23,24,25)"
            );
            $oDB->createCommand()->update('{{surveymenu_entries}}',
                array('data'=> "{\"render\": {\"isActive\": false, \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}"),
                    "id IN (15,30)"
            );
            $oDB->createCommand()->update('{{surveymenu_entries}}',
                array('data'=> "{\"render\": {\"isActive\": true, \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}"),
                    "id=16"
            );
            $oTransaction->commit();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>305),"stg_name='DBVersion'");
        }

        /**
         * Template tables
         * @since 2017-07-12
         */
        if ($iOldDBVersion < 306) {
            $oTransaction = $oDB->beginTransaction();
            createSurveyGroupTables306($oDB);
            $oTransaction->commit();
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>306),"stg_name='DBVersion'");
        }

        /**
         * User settings table
         * @since 2016-08-29
         */
        if ($iOldDBVersion < 307) {
            $oTransaction = $oDB->beginTransaction();
            if (tableExists('{settings_user}')) {
                $oDB->createCommand('DROP TABLE {{settings_user}} CASCADE')->execute();
            }
            $oDB->createCommand()->createTable('{{settings_user}}', array(
                'id' => 'pk',
                'uid' => 'integer NOT NULL',
                'entity' => 'string(15)',
                'entity_id' => 'string(31)',
                'stg_name' => 'string(63) not null',
                'stg_value' => 'text',
                
            ));
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>307),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /*
         * Change dbfieldnames to be more functional
         */
        if ($iOldDBVersion < 308) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->renameColumn('{{surveymenu_entries}}','order','ordering');
            $oDB->createCommand()->renameColumn('{{surveymenu}}','order','ordering');
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>308),"stg_name='DBVersion'");
            $oTransaction->commit();
        }
        /*
         * Add survey template editing to menu
         */
        if ($iOldDBVersion < 309) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->insert('{{surveymenu_entries}}',array_combine(
                array(
                    "menu_id","ordering","name","title","menu_title","menu_description","menu_icon","menu_icon_type",
                    "menu_link","permission","permission_grade",
                    "data",
                    "language","changed_at","changed_by","created_at","created_by"),
                array(
                    1,3,"template_options","Template options","Template options","Edit Template options for this survey","paint-brush","fontawesome",
                    "admin/templateoptions/sa/updatesurvey","surveysettings","read",
                    '{"render": {"link": { "pjaxed": false, "data": {"surveyid": ["survey","sid"], "gsid":["survey","gsid"]}}}}',
                    "en-GB",date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0
                    )
                )
            );
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>309),"stg_name='DBVersion'");
            $oTransaction->commit();
            SurveymenuEntries::reorderMenu(1);
        }

        /*
         * Reset all surveymenu tables, because there were too many errors
         */
        if ($iOldDBVersion < 310) {
            $oTransaction = $oDB->beginTransaction();
            reCreateSurveyMenuTable310($oDB);
            
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>310),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /*
         * Add template settings to survey groups
         */
        if ($iOldDBVersion < 311) {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{surveys_groups}}','template', "string(128) DEFAULT 'default'");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>311),"stg_name='DBVersion'");
            $oTransaction->commit();
        }


        /*
         * Add ltr/rtl capability to template configuration
         */
        if ($iOldDBVersion < 312) {
            $oTransaction = $oDB->beginTransaction();
            addColumn('{{template_configuration}}','packages_ltr', "text");
            addColumn('{{template_configuration}}','packages_rtl', "text");
            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>312),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /*
         * Add ltr/rtl capability to template configuration
         */
        if ($iOldDBVersion < 313) {
            $oTransaction = $oDB->beginTransaction();

            addColumn('{{surveymenu_entries}}','active', "boolean DEFAULT '0'");
            addColumn('{{surveymenu}}','active', "boolean DEFAULT '0'");
            $oDB->createCommand()->update('{{surveymenu_entries}}', array('active'=>1));
            $oDB->createCommand()->update('{{surveymenu}}', array('active'=>1));

            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>313),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /*
         * Add ltr/rtl capability to template configuration
         */
        if ($iOldDBVersion < 314) {
            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->update('{{surveymenu_entries}}',
                array('name'=>'resources', 'title'=>'Add/Edit resources to the survey', 'menu_title'=>'Resources', 'menu_description'=>'Add/Edit resources to the survey'),
                'id=15'
            );

            $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>314),"stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 315) {
            $oTransaction = $oDB->beginTransaction();
            
            $oDB->createCommand()->update('{{template_configuration}}', 
            array('packages_to_load'=>'["pjax"]'),
            'id=1'
        );
        
        $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>315),"stg_name='DBVersion'");
        $oTransaction->commit();
    }

    if ($iOldDBVersion < 316) {
        $oTransaction = $oDB->beginTransaction();
        
        $oDB->createCommand()->renameColumn('{{template_configuration}}', 'templates_name', 'template_name');

        $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>316),"stg_name='DBVersion'");
        $oTransaction->commit();
    }
    
    //Transition of the password field to a TEXT type

    if ($iOldDBVersion < 317) {
        $oTransaction = $oDB->beginTransaction();
        
        transferPasswordFieldToText($oDB);

        $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>317),"stg_name='DBVersion'");
        $oTransaction->commit();
    }
                        

    
    //Rename order to sortorder

    if ($iOldDBVersion < 318) {
        $oTransaction = $oDB->beginTransaction();
        
        $oDB->createCommand()->renameColumn('{{surveys_groups}}', 'order', 'sortorder');

        $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>318),"stg_name='DBVersion'");
        $oTransaction->commit();
    }

    //force panelintegration to a full reload

    if ($iOldDBVersion < 319) {
        $oTransaction = $oDB->beginTransaction();
        
        $oDB->createCommand()->update('{{surveymenu_entries}}',array('data'=>'{"render": {"link": { "pjaxed": false}}}'),"name='panelintegration'");

        $oDB->createCommand()->update('{{settings_global}}',array('stg_value'=>319),"stg_name='DBVersion'");
        $oTransaction->commit();
    }
                        


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

    // Load all tables of the application in the schema
    $oDB->schema->getTables();

    // clear the cache of all loaded tables
    $oDB->schema->refresh();
    $oDB->active = false;
    $oDB->active = true;

    // Force User model to refresh meta data (for updates from very old versions)
    User::model()->refreshMetaData();

    // Inform  superadmin about update
    $superadmins = User::model()->getSuperAdmins();
    $currentDbVersion = $oDB->createCommand()->select('stg_value')->from('{{settings_global}}')->where("stg_name=:stg_name", array('stg_name'=>'DBVersion'))->queryRow();

    Notification::broadcast(array(
        'title' => gT('Database update'),
        'message' => sprintf(gT('The database has been updated from version %s to version %s.'), $iOldDBVersion, $currentDbVersion['stg_value'])
    ), $superadmins);

    fixLanguageConsistencyAllSurveys();

    Yii::app()->setConfig('Updating',false);
    return true;
}

function transferPasswordFieldToText($oDB){
    switch($oDB->getDriverName()){
        case 'mysql':
        case 'mysqli':
            $oDB->createCommand()->alterColumn( '{{users}}','password', 'TEXT NOT NULL');
        break;
        case 'pgsql':
        
        $userPasswords = $oDB->createCommand()->select(['uid', "encode(password::bytea, 'escape') as password"])->from('{{users}}')->queryAll();
        
        $oDB->createCommand()->renameColumn('{{users}}', 'password', 'password_blob');
        $oDB->createCommand()->addColumn('{{users}}', 'password', "TEXT NOT NULL DEFAULT 'nopw'");

        foreach($userPasswords as $userArray){
            $oDB->createCommand()->update('{{users}}', ['password' => $userArray['password']], 'uid=:uid' , [':uid'=> $userArray['uid']] );
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

function createSurveyMenuTable293($oDB) {
        // Drop the old survey rights table.
    if (tableExists('{surveymenu_entries}')) {
        $oDB->createCommand('DROP TABLE {{surveymenu_entries}} CASCADE')->execute();
    }
        
    if (tableExists('{surveymenu}')) {
        $oDB->createCommand('DROP TABLE {{surveymenu}} CASCADE')->execute();
    }


    $oDB->createCommand()->createTable('{{surveymenu}}', array(
        "id" => "pk",
        "parent_id" => "int DEFAULT NULL",
        "survey_id" => "int DEFAULT NULL",
        "order" => "int DEFAULT '0'",
        "level" => "int DEFAULT '0'",
        "title" => "character varying(255)  NOT NULL DEFAULT ''",
        "description" => "text ",
        "changed_at" => "timestamp",
        "changed_by" => "int NOT NULL DEFAULT '0'",
        "created_at" => "datetime DEFAULT NULL",
        "created_by" => "int NOT NULL DEFAULT '0'",
        
    ));

    $oDB->createCommand()->insert(
        '{{surveymenu}}',
        array(
            'id' => 1,
            'parent_id' => NULL,
            'survey_id' => NULL,
            'order' => 0,
            'level' => 0,
            'title' => 'surveymenu',
            'description' => 'Main survey menu',
            'changed_at' => date('Y-m-d H:i:s'),
            'changed_by' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => 0
        )
    );

    $oDB->createCommand()->createTable('{{surveymenu_entries}}', array(
        "id" => "pk",
        "menu_id" => "int DEFAULT NULL",
        "order" => "int DEFAULT '0'",
        "name" => "character varying(255)  NOT NULL DEFAULT ''",
        "title" => "character varying(255)  NOT NULL DEFAULT ''",
        "menu_title" => "character varying(255)  NOT NULL DEFAULT ''",
        "menu_description" => "text ",
        "menu_icon" => "character varying(255)  NOT NULL DEFAULT ''",
        "menu_icon_type" => "character varying(255)  NOT NULL DEFAULT ''",
        "menu_class" => "character varying(255)  NOT NULL DEFAULT ''",
        "menu_link" => "character varying(255)  NOT NULL DEFAULT ''",
        "action" => "character varying(255)  NOT NULL DEFAULT ''",
        "template" => "character varying(255)  NOT NULL DEFAULT ''",
        "partial" => "character varying(255)  NOT NULL DEFAULT ''",
        "classes" => "character varying(255)  NOT NULL DEFAULT ''",
        "permission" => "character varying(255)  NOT NULL DEFAULT ''",
        "permission_grade" => "character varying(255)  DEFAULT NULL",
        "data" => "text ",
        "getdatamethod" => "character varying(255)  NOT NULL DEFAULT ''",
        "language" => "character varying(255)  NOT NULL DEFAULT 'en-GB'",
        "changed_at" => "timestamp",
        "changed_by" => "int NOT NULL DEFAULT '0'",
        "created_at" => "datetime DEFAULT NULL",
        "created_by" => "int NOT NULL DEFAULT '0'",
        "PRIMARY KEY (id)",
        "FOREIGN KEY (menu_id) REFERENCES  {{surveymenu}} (id) ON DELETE CASCADE"
    ));

    $colsToAdd = array("menu_id","order","name","title","menu_title","menu_description","menu_icon","menu_icon_type","menu_class","menu_link","action","template","partial","classes","permission","permission_grade","data","getdatamethod","language","changed_at","changed_by","created_at","created_by");
    $rowsToAdd = array(
        array(1,1,'overview','Survey overview','Overview','Open general survey overview and quick action','list','fontawesome','','admin/survey/sa/view','','','','','','',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,2,'generalsettings','Edit survey general settings','General settings','Open general survey settings','gears','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',NULL,'_generalTabEditSurvey','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,3,'surveytexts','Edit survey text elements','Survey texts','Edit survey text elements','file-text-o','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/tab_edit_view','','surveylocale','read',NULL,'_getTextEditData','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,4,'presentation','Presentation &amp; navigation settings','Presentation','Edit presentation and navigation settings','eye-slash','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_presentation_panel','','surveylocale','read',NULL,'_tabPresentationNavigation','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,5,'publication','Publication and access control settings','Publication &amp; access','Edit settings for publicationa and access control','key','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_publication_panel','','surveylocale','read',NULL,'_tabPublicationAccess','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,6,'surveypermissions','Edit surveypermissions','Survey permissions','Edit permissions for this survey','lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,7,'tokens','Token handling','Participant tokens','Define how tokens should be treated or generated','users','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read',NULL,'_tabTokens','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,8,'quotas','Edit quotas','Survey quotas','Edit quotas for this survey.','tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,9,'assessments','Edit assessments','Assessments','Edit and look at the asessements for this survey.','comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,10,'notification','Notification and data management settings','Data management','Edit settings for notification and data management','feed','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_notification_panel','','surveylocale','read',NULL,'_tabNotificationDataManagement','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,11,'emailtemplates','Email templates','Email templates','Edit the templates for invitation, reminder and registration emails','envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','assessments','read',NULL,'','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,12,'panelintegration','Edit survey panel integration','Panel integration','Define panel integrations for your survey','link','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_integration_panel','','surveylocale','read',NULL,'_tabPanelIntegration','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,13,'ressources','Add/Edit ressources to the survey','Ressources','Add/Edit ressources to the survey','file','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_resources_panel','','surveylocale','read',NULL,'_tabResourceManagement','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0)
    );
    foreach($rowsToAdd as $row){
        $oDB->createCommand()->insert('{{surveymenu_entries}}', array_combine($colsToAdd,$row));
    }
}

/**
 * @param CDbConnection $oDB
 * @return void
 */
function reCreateSurveyMenuTable310(CDbConnection $oDB)
{
    // NB: Need to refresh here, since surveymenu table is
    // created in earlier version in same script.
    $oDB->schema->getTables();
    $oDB->schema->refresh();

    // Drop the old surveymenu table.
    if (tableExists('{surveymenu}')) {
        $oDB->createCommand('DROP TABLE {{surveymenu}} CASCADE')->execute();
    }
    // Drop the old surveymenu_entries table.
    if (tableExists('{surveymenu_entries}')) {
        $oDB->createCommand('DROP TABLE {{surveymenu_entries}} CASCADE')->execute();
    }

    $oDB->createCommand()->createTable('{{surveymenu}}', array(
        "id" =>  "pk",
        "parent_id" =>  "integer DEFAULT NULL",
        "survey_id" =>  "integer DEFAULT NULL",
        "user_id" =>  "integer DEFAULT NULL",
        "ordering" =>  "integer DEFAULT '0'",
        "level" =>  "integer DEFAULT '0'",
        "title" =>  "varchar(255)  NOT NULL DEFAULT ''",
        "position" =>  "varchar(255)  NOT NULL DEFAULT 'side'",
        "description" =>  "text ",
        "changed_at" =>  "timestamp",
        "changed_by" =>  "integer NOT NULL DEFAULT '0'",
        "created_at" =>  "datetime DEFAULT NULL",
        "created_by" =>  "integer NOT NULL DEFAULT '0'",
    ));
    $oDB->createCommand()->createIndex('idx_ordering', '{{surveymenu}}', 'ordering');
    $oDB->createCommand()->createIndex('idx_title', '{{surveymenu}}', 'title');

    $oDB->createCommand()->insert(
        '{{surveymenu}}',
        array(
            "parent_id" =>NULL,
            "survey_id" =>NULL,
            "user_id" =>NULL,
            "ordering" =>1,
            "level" =>0,
            "title" =>'surveymenu',
            "position" =>'side',
            "description" =>'Main survey menu',
            "changed_at" => date('Y-m-d H:i:s'),
            "changed_by" =>0,
            "created_at" =>date('Y-m-d H:i:s'),
            "created_by" =>  0
        ));
    $oDB->createCommand()->insert(
        '{{surveymenu}}',
        array(
            "parent_id" =>NULL,
            "survey_id" =>NULL,
            "user_id" =>NULL,
            "ordering" =>1,
            "level" =>0,
            "title" =>'quickmenue',
            "position" =>'collapsed',
            "description" =>'Quickmenu',
            "changed_at" => date('Y-m-d H:i:s'),
            "changed_by" =>0,
            "created_at" =>date('Y-m-d H:i:s'),
            "created_by" =>  0
        ));


 $oDB->createCommand()->createTable('{{surveymenu_entries}}', array(
        "id" => "pk",
        "menu_id" => "integer DEFAULT NULL",
        "user_id" => "integer DEFAULT NULL",
        "ordering" => "integer DEFAULT '0'",
        "name" => "varchar(255)  NOT NULL DEFAULT ''",
        "title" => "varchar(255)  NOT NULL DEFAULT ''",
        "menu_title" => "varchar(255)  NOT NULL DEFAULT ''",
        "menu_description" => "text ",
        "menu_icon" => "varchar(255)  NOT NULL DEFAULT ''",
        "menu_icon_type" => "varchar(255)  NOT NULL DEFAULT ''",
        "menu_class" => "varchar(255)  NOT NULL DEFAULT ''",
        "menu_link" => "varchar(255)  NOT NULL DEFAULT ''",
        "action" => "varchar(255)  NOT NULL DEFAULT ''",
        "template" => "varchar(255)  NOT NULL DEFAULT ''",
        "partial" => "varchar(255)  NOT NULL DEFAULT ''",
        "classes" => "varchar(255)  NOT NULL DEFAULT ''",
        "permission" => "varchar(255)  NOT NULL DEFAULT ''",
        "permission_grade" => "varchar(255)  DEFAULT NULL",
        "data" => "text ",
        "getdatamethod" => "varchar(255)  NOT NULL DEFAULT ''",
        "language" => "varchar(255)  NOT NULL DEFAULT 'en-GB'",
        "changed_at" => "timestamp",
        "changed_by" => "integer NOT NULL DEFAULT '0'",
        "created_at" => "timestamp DEFAULT NULL",
        "created_by" => "integer NOT NULL DEFAULT '0'"
    ));
    $oDB->createCommand()->createIndex('idx_menu_id', '{{surveymenu_entries}}', 'menu_id');
    $oDB->createCommand()->createIndex('idx_ordering_entries', '{{surveymenu_entries}}', 'ordering');
    $oDB->createCommand()->createIndex('idx_title_entries', '{{surveymenu_entries}}', 'title');
    $oDB->createCommand()->createIndex('idx_menu_title', '{{surveymenu_entries}}', 'menu_title');

    $colsToAdd = array("menu_id","user_id","ordering","name","title","menu_title","menu_description","menu_icon","menu_icon_type","menu_class","menu_link","action","template","partial","classes","permission","permission_grade","data","getdatamethod","language","changed_at","changed_by","created_at","created_by");
    $rowsToAdd = array(
        array(1,NULL,1,'overview','Survey overview','Overview','Open general survey overview and quick action','list','fontawesome','','admin/survey/sa/view','','','','','','','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,NULL,2,'generalsettings','Edit survey general settings','General settings','Open general survey settings','gears','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',NULL,'_generalTabEditSurvey','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,NULL,3,'surveytexts','Edit survey text elements','Survey texts','Edit survey text elements','file-text-o','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/tab_edit_view','','surveylocale','read',NULL,'_getTextEditData','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,NULL,4,'template_options','Template options','Template options','Edit Template options for this survey','paint-brush','fontawesome','','admin/templateoptions/sa/updatesurvey','','','','','templates','read','{"render": {"link": { "pjaxed": false, "data": {"surveyid": ["survey","sid"], "gsid":["survey","gsid"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,NULL,5,'participants','Survey participants','Survey participants','Go to survey participant and token settings','user','fontawesome','','admin/tokens/sa/index/','','','','','surveysettings','update','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,NULL,6,'presentation','Presentation &amp; navigation settings','Presentation','Edit presentation and navigation settings','eye-slash','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_presentation_panel','','surveylocale','read',NULL,'_tabPresentationNavigation','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,NULL,7,'publication','Publication and access control settings','Publication &amp; access','Edit settings for publicationa and access control','key','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_publication_panel','','surveylocale','read',NULL,'_tabPublicationAccess','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,NULL,8,'surveypermissions','Edit surveypermissions','Survey permissions','Edit permissions for this survey','lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,NULL,9,'tokens','Token handling','Participant tokens','Define how tokens should be treated or generated','users','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read',NULL,'_tabTokens','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,NULL,10,'quotas','Edit quotas','Survey quotas','Edit quotas for this survey.','tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,NULL,11,'assessments','Edit assessments','Assessments','Edit and look at the asessements for this survey.','comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,NULL,12,'notification','Notification and data management settings','Data management','Edit settings for notification and data management','feed','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_notification_panel','','surveylocale','read',NULL,'_tabNotificationDataManagement','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,NULL,13,'emailtemplates','Email templates','Email templates','Edit the templates for invitation, reminder and registration emails','envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','assessments','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,NULL,14,'panelintegration','Edit survey panel integration','Panel integration','Define panel integrations for your survey','link','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_integration_panel','','surveylocale','read',NULL,'_tabPanelIntegration','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(1,NULL,15,'ressources','Add/Edit ressources to the survey','Ressources','Add/Edit ressources to the survey','file','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_resources_panel','','surveylocale','read',NULL,'_tabResourceManagement','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(2,NULL,1,'activateSurvey','Activate survey','Activate survey','Activate survey','play','fontawesome','','admin/survey/sa/activate','','','','','surveyactivation','update','{\"render\": {\"isActive\": false, \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(2,NULL,2,'deactivateSurvey','Stop this survey','Stop this survey','Stop this survey','stop','fontawesome','','admin/survey/sa/deactivate','','','','','surveyactivation','update','{\"render\": {\"isActive\": true, \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(2,NULL,3,'testSurvey','Go to survey','Go to survey','Go to survey','cog','fontawesome','','survey/index/','','','','','','','{\"render\"\: {\"link\"\: {\"external\"\: true, \"data\"\: {\"sid\"\: [\"survey\",\"sid\"], \"newtest\"\: \"Y\", \"lang\"\: [\"survey\",\"language\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(2,NULL,4,'listQuestions','List questions','List questions','List questions','list','fontawesome','','admin/survey/sa/listquestions','','','','','surveycontent','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(2,NULL,5,'listQuestionGroups','List question groups','List question groups','List question groups','th-list','fontawesome','','admin/survey/sa/listquestiongroups','','','','','surveycontent','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(2,NULL,6,'generalsettings','Edit survey general settings','General settings','Open general survey settings','gears','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',NULL,'_generalTabEditSurvey','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(2,NULL,7,'surveypermissions','Edit surveypermissions','Survey permissions','Edit permissions for this survey','lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(2,NULL,8,'quotas','Edit quotas','Survey quotas','Edit quotas for this survey.','tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(2,NULL,9,'assessments','Edit assessments','Assessments','Edit and look at the asessements for this survey.','comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(2,NULL,10,'emailtemplates','Email templates','Email templates','Edit the templates for invitation, reminder and registration emails','envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','surveylocale','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(2,NULL,11,'surveyLogicFile','Survey logic file','Survey logic file','Survey logic file','sitemap','fontawesome','','admin/expressions/sa/survey_logic_file/','','','','','surveycontent','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(2,NULL,12,'tokens','Token handling','Participant tokens','Define how tokens should be treated or generated','user','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','_tabTokens','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(2,NULL,13,'cpdb','Central participant database','Central participant database','Central participant database','users','fontawesome','','admin/participants/sa/displayParticipants','','','','','tokens','read','{render\: {\"link\"\: {}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(2,NULL,14,'responses','Responses','Responses','Responses','icon-browse','iconclass','','admin/responses/sa/browse/','','','','','responses','read','{\"render\"\: {\"isActive\"\: true}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(2,NULL,15,'statistics','Statistics','Statistics','Statistics','bar-chart','fontawesome','','admin/statistics/sa/index/','','','','','statistics','read','{\"render\"\: {\"isActive\"\: true}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0),
        array(2,NULL,16,'reorder','Reorder questions/question groups','Reorder questions/question groups','Reorder questions/question groups','icon-organize','iconclass','','admin/survey/sa/organize/','','','','','surveycontent','update','{\"render\": {\"isActive\": false, \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',date('Y-m-d H:i:s'),0,date('Y-m-d H:i:s'),0)
    );
    foreach($rowsToAdd as $row){
        $oDB->createCommand()->insert('{{surveymenu_entries}}', array_combine($colsToAdd,$row));
    }
}
/**
 * @param $oDB
 * @return void
 */
function createSurveyGroupTables306($oDB)
{
    // Drop the old survey rights table.
    if (tableExists('{surveys_groups}')) {
        $oDB->createCommand()->execute('DROP TABLE {{surveys_groups}}');
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

    $oDB->createCommand()->addColumn('{{surveys}}','gsid',"integer DEFAULT 1");


}

/**
 * @param $oDB
 * @return void
 */
function upgradeTemplateTables304($oDB)
{
    // Drop the old survey rights table.
    if (tableExists('{{templates}}')) {
        $oDB->createCommand('DROP TABLE {{templates}} CASCADE')->execute();
    }

    if (tableExists('{{template_configuration}}')) {
        $oDB->createCommand('DROP TABLE {{template_configuration}} CASCADE')->execute();
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
        'copyright'              => 'TEXT',
        'license'                => 'TEXT',
        'version'                => 'string(45) DEFAULT NULL',
        'api_version'            => 'string(45) NOT NULL',
        'view_folder'            => 'string(45) NOT NULL',
        'files_folder'           => 'string(45) NOT NULL',
        'description'            => 'TEXT',
        'last_update'            => 'datetime DEFAULT NULL',
        'owner_id'               => 'integer DEFAULT NULL',
        'extends_templates_name' => 'string(150) DEFAULT NULL',
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
        'extends_templates_name' => '',
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
        'extends_templates_name' => '',
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
        'extends_templates_name' => 'default',
    ));


    // Add template configuration table
    $oDB->createCommand()->createTable('{{template_configuration}}', array(
        'id'                => 'pk',
        'templates_name'    => 'string(150) NOT NULL',
        'sid'               => 'integer DEFAULT NULL',
        'gsid'              => 'integer DEFAULT NULL',
        'uid'               => 'integer DEFAULT NULL',
        'files_css'         => 'TEXT',
        'files_js'          => 'TEXT',
        'files_print_css'   => 'TEXT',
        'options'           => 'TEXT',
        'cssframework_name' => 'string(45) DEFAULT NULL',
        'cssframework_css'  => 'TEXT',
        'cssframework_js'   => 'TEXT',
        'packages_to_load'  => 'TEXT',
    ));

    // Add global configuration for Advanced Template
    $oDB->createCommand()->insert('{{template_configuration}}', array(
        'templates_name'    => 'default',
        'files_css'         => '{"add": ["css/template.css", "css/animate.css"]}',
        'files_js'          => '{"add": ["scripts/template.js"]}',
        'files_print_css'   => '{"add":"css/print_template.css",}',
        'options'           => '{"ajaxmode":"on","brandlogo":"on", "boxcontainer":"on", "backgroundimage":"on","animatebody":"on","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}',
        'cssframework_name' => 'bootstrap',
        'cssframework_css'  => '{"replace": [["css/bootstrap.css","css/flatly.css"]]}',
        'cssframework_js'   => '',
        'packages_to_load'  => 'template-core,',
    ));


    // Add global configuration for Minimal Template
    $oDB->createCommand()->insert('{{template_configuration}}', array(
        'templates_name'    => 'minimal',
        'files_css'         => '{"add": ["css/template.css"]}',
        'files_js'          => '{"add": ["scripts/template.js"]}',
        'files_print_css'   => '{"add":"css/print_template.css",}',
        'options'           => '{}',
        'cssframework_name' => 'bootstrap',
        'cssframework_css'  => '{}',
        'cssframework_js'   => '',
        'packages_to_load'  => 'template-core,',
    ));

    // Add global configuration for Material Template
    $oDB->createCommand()->insert('{{template_configuration}}', array(
        'templates_name'    => 'material',
        'files_css'         => '{"add": ["css/template.css", "css/bootstrap-material-design.css", "css/ripples.min.css"]}',
        'files_js'          => '{"add": ["scripts/template.js", "scripts/material.js", "scripts/ripples.min.js"]}',
        'files_print_css'   => '{"add":"css/print_template.css",}',
        'options'           => '{"ajaxmode":"on","brandlogo":"on", "animatebody":"on","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}',
        'cssframework_name' => 'bootstrap',
        'cssframework_css'  => '{"replace": [["css/bootstrap.css","css/bootstrap.css"]]}',
        'cssframework_js'   => '',
        'packages_to_load'  => 'template-core,',
    ));

}


/**
 * @param $oDB
 * @return void
 */
function upgradeTemplateTables298($oDB)
{
    // Add global configuration for Advanced Template
    $oDB->createCommand()->update('{{boxes}}',array(
        'url'=>'admin/templateoptions',
        'title'=>'Templates',
        'desc'=>'View templates list',
    ) ,"id=6");
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
 * @param string $sType
 */
function addColumn($sTableName, $sColumn, $sType)
{
    Yii::app()->db->createCommand()->addColumn($sTableName,$sColumn,$sType);
}
