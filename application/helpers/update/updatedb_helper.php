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
- Do not use fancy database field types (like mediumtext, timestamp, etc) - only use the ones provided by Yii
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
    If ($iOldDBVersion < 184) {
        return false;
    }

    /// This function does anything necessary to upgrade
    /// older versions to match current functionality
    global $modifyoutput;

    Yii::app()->loadHelper('database');
    $sUserTemplateRootDir       = Yii::app()->getConfig('userthemerootdir');
    $sStandardTemplateRootDir   = Yii::app()->getConfig('standardthemerootdir');
    $oDB                        = Yii::app()->getDb();
    $oDB->schemaCachingDuration = 0; // Deactivate schema caching
    Yii::app()->setConfig('Updating', true);

    try {
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
            dropPrimaryKey('templates');
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
            reCreateSurveyMenuTable310($oDB);

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

            addColumn('{{surveymenu_entries}}', 'active', "boolean NOT NULL DEFAULT '0'");
            addColumn('{{surveymenu}}', 'active', "boolean NOT NULL DEFAULT '0'");
            $oDB->createCommand()->update('{{surveymenu_entries}}', array('active'=>1));
            $oDB->createCommand()->update('{{surveymenu}}', array('active'=>1));

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>313), "stg_name='DBVersion'");
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

            $oDB->createCommand()->update('{{surveymenu_entries}}', array('data'=>'{"render": {"link": { "pjaxed": false}}}'), "name='panelintegration'");

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
            $oDB->createCommand()->update('{{surveymenu_entries}}', array('action'=>'updatesurveylocalesettings_generalsettings'), "name='generalsettings'");
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>320), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 321) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array('data' => '{"render": {"isActive": true, "link": {"data": {"surveyid": ["survey", "sid"]}}}}'),
                "name = 'statistics' OR name = 'responses'"
            );
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
                    'active' =>  'int DEFAULT 0',
                    'settings' => 'text',
                    'permission' =>  'string(128) NOT NULL',
                    'permission_grade' =>  'string(128) NOT NULL'
                ]
            );
            $oDB->createCommand()->createTable(
                '{{tutorial_entries}}', [
                    'teid' =>  'pk',
                    'tid' =>  'int NOT NULL',
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

            $oDB->createCommand()->insert('{{surveymenu_entries}}',
            array(
                'menu_id' => 1,
                'ordering' => 16,
                'name' => 'plugins',
                'title' => 'Plugin settings',
                'menu_title' => 'Plugins',
                'menu_description' => 'Edit plugin settings',
                'menu_icon' => 'plug',
                'menu_icon_type' => 'fontawesome',
                'action' => 'updatesurveylocalesettings',
                'template' => 'editLocalSettings_main_view',
                'partial' => '/admin/survey/subview/accordion/_plugin_panel',
                'permission' => 'surveysettings',
                'permission_grade' => 'read',
                'data' => '',
                'getdatamethod' => '_pluginTabSurvey',
                'changed_at' => date('Y-m-d H:i:s'),
                'changed_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => 1,
                'active' => 0
            ));
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
            $oDB->createCommand()->insert("{{template_configuration}}", array_combine($headerArray, ['default', null, null, null, '{"add": ["css/animate.css","css/template.css"]}', '{"add": ["scripts/template.js", "scripts/ajaxify.js"]}', '{"add":"css/print_template.css"}', '{"ajaxmode":"on","brandlogo":"on", "brandlogofile": "./files/logo.png", "boxcontainer":"on", "backgroundimage":"off","animatebody":"off","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}', 'bootstrap', '{"replace": [["css/bootstrap.css","css/flatly.css"]]}', '', '["pjax"]', '', '']));

            $oDB->createCommand()->insert("{{template_configuration}}", array_combine($headerArray, ['material', null, null, null, '{"add": ["css/bootstrap-material-design.css", "css/ripples.min.css", "css/template.css"]}', '{"add": ["scripts/template.js", "scripts/material.js", "scripts/ripples.min.js", "scripts/ajaxify.js"]}', '{"add":"css/print_template.css"}', '{"ajaxmode":"on","brandlogo":"on", "brandlogofile": "./files/logo.png", "animatebody":"off","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}', 'bootstrap', '{"replace": [["css/bootstrap.css","css/bootstrap.css"]]}', '', '["pjax"]', '', '']));

            $oDB->createCommand()->insert("{{template_configuration}}", array_combine($headerArray, ['monochrome', null, null, null, '{"add":["css/animate.css","css/ajaxify.css","css/sea_green.css", "css/template.css"]}', '{"add":["scripts/template.js","scripts/ajaxify.js"]}', '{"add":"css/print_template.css"}', '{"ajaxmode":"on","brandlogo":"on","brandlogofile":".\/files\/logo.png","boxcontainer":"on","backgroundimage":"off","animatebody":"off","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}', 'bootstrap', '{}', '', '["pjax"]', '', '']));

            $oDB->createCommand()->update('{{surveymenu_entries}}', array('data'=>'{"render": {"link": { "data": {"surveyid": ["survey","sid"], "gsid":["survey","gsid"]}}}}'), "name='template_options'");

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
            $oDB->createCommand()->alterColumn('{{surveymenu_entries}}', 'name', 'string(168)');
            $oDB->createCommand()->update('{{surveymenu_entries}}', array('name' => 'generalsettings_collapsed'), "name = 'generalsettings' AND menu_id = 2");
            $oDB->createCommand()->update('{{surveymenu_entries}}', array('name' => 'surveypermissions_collapsed'), "name = 'surveypermissions' AND menu_id = 2");
            $oDB->createCommand()->update('{{surveymenu_entries}}', array('name' => 'quotas_collapsed'), "name = 'quotas' AND menu_id = 2");
            $oDB->createCommand()->update('{{surveymenu_entries}}', array('name' => 'assessments_collapsed'), "name = 'assessments' AND menu_id = 2");
            $oDB->createCommand()->update('{{surveymenu_entries}}', array('name' => 'emailtemplates_collapsed'), "name = 'emailtemplates' AND menu_id = 2");
            $oDB->createCommand()->update('{{surveymenu_entries}}', array('name' => 'tokens_collapsed'), "name = 'tokens' AND menu_id = 2");
            $oDB->createCommand()->createIndex('{{surveymenu_entries_name}}', '{{surveymenu_entries}}', "name", true);

            $oDB->createCommand()->addColumn('{{surveymenu}}', 'name', 'string(128) NULL');
            $oDB->createCommand()->createIndex('{{surveymenu_name}}', '{{surveymenu}}', 'name', true);
            $oDB->createCommand()->update('{{surveymenu}}', array('name' => 'mainmenu'), 'id = 1');
            $oDB->createCommand()->update('{{surveymenu}}', array('name' => 'quickmenu'), 'id = 2');
            $oDB->createCommand()->alterColumn('{{surveymenu}}', 'name', 'string(128)');

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
            $oDB->createCommand()->insert(
                '{{surveymenu}}',
                array(
                    'parent_id' => 1,
                    'survey_id' => null,
                    'ordering' => 0,
                    'level' => 1,
                    'name' => 'pluginmenu',
                    'title' => 'Plugin menu',
                    'description' => 'Plugins menu',
                    'changed_at' => date('Y-m-d H:i:s'),
                    'changed_by' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => 0
                    )
                );
            $pluginMenuId = getLastInsertID('{{surveymenu}}');
            $oDB->createCommand()->update('{{surveymenu_entries}}', array(
                'menu_id' => $pluginMenuId,
                'title' => 'Simple plugins',
                'menu_title' => 'Simple plugins',
                'menu_description' => 'Edit simple plugin settings',
            ), "name='plugins'");
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
            $oDB->createCommand()->update('{{tutorials}}', [
                    'settings' => json_encode(array(
                    'debug' => true,
                    'orphan' => true,
                    'keyboard' => false,
                    'template' => "<div class='popover tour lstutorial__template--mainContainer'> <div class='arrow'></div> <h3 class='popover-title lstutorial__template--title'></h3> <div class='popover-content lstutorial__template--content'></div> <div class='popover-navigation lstutorial__template--navigation'>     <div class='btn-group col-xs-8' role='group' aria-label='...'>         <button class='btn btn-default col-xs-6' data-role='prev'>".gT('Previous')."</button>         <button class='btn btn-primary col-xs-6' data-role='next'>".gT('Next')."</button>     </div>     <div class='col-xs-4'>         <button class='btn btn-warning' data-role='end'>".gT('End tour')."</button>     </div> </div></div>",
                    'onShown' => "(function(tour){ console.ls.log($('#notif-container').children()); $('#notif-container').children().remove(); })",                   
                    'onStart' => "(function(){var domaintobe=LS.data.baseUrl+(LS.data.urlFormat == 'path' ? '/admin/index' : '?r=admin/index'); if(window.location.href!=domaintobe){window.location.href=domaintobe;} })"
                    )),
                    'title' => 'Take beginner tour',
                    'icon' => 'fa-rocket'
            ]);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>334), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 335) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{tutorial_entries}}', [
                'settings' => json_encode(array(
                    'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}']],
                    'element' => '#sidebar',
                    'placement' => 'right',
                    'redirect' => false,
                    'prev' => '-1',
                    'onShow' => "(function(tour){
                                    return Promise.resolve(tour);
                                })"
                ))
                ], 'teid=9');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>335), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        if ($iOldDBVersion < 336) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{tutorials}}', [
                'settings' => json_encode(array(
                    'keyboard' => false,
                    'template' => "<div class='popover tour lstutorial__template--mainContainer'> <div class='arrow'></div> <h3 class='popover-title lstutorial__template--title'></h3> <div class='popover-content lstutorial__template--content'></div> <div class='popover-navigation lstutorial__template--navigation'>     <div class='btn-group col-xs-8' role='group' aria-label='...'>         <button class='btn btn-default col-xs-6' data-role='prev'>".gT('Previous')."</button>         <button class='btn btn-primary col-xs-6' data-role='next'>".gT('Next')."</button>     </div>     <div class='col-xs-4'>         <button class='btn btn-warning' data-role='end'>".gT('End tour')."</button>     </div> </div></div>",
                    'onShown' => "(function(tour){ console.ls.log($('#notif-container').children()); $('#notif-container').children().remove(); })",                   
                    'onStart' => "(function(){var domaintobe=LS.data.baseUrl+(LS.data.urlFormat == 'path' ? '/admin/index' : '?r=admin/index'); if(window.location.href!=domaintobe){window.location.href=domaintobe;} })"
                    )),
                ], 'tid=1');
            $oDB->createCommand()->update('{{tutorial_entries}}', [
                'settings' => json_encode(array(
                    'element' => '#lime-logo',
                    'path' => '/admin/index',
                    'placement' => 'bottom',
                    'redirect' => false,
                    'onShow' => "(function(tour){ $('#welcomeModal').modal('hide'); })"
                    ))
                ], 'teid=1');
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
            // NB: Needed since Postgres id seq might not work.
            $maxId = $oDB->createCommand()->select('max(id)')->from("{{boxes}}")->queryScalar();

            $oDB->createCommand()->insert(
                "{{boxes}}",
                [
                    'id' => $maxId + 1,
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

            $oDB->createCommand()->update("{{tutorials}}", [
                    'settings' => json_encode(array(
                        'keyboard' => false,
                        'orphan' => true,
                        'template' => ""
                        ."<div class='popover tour lstutorial__template--mainContainer'>" 
                            ."<div class='arrow'></div>"
                            ."<h3 class='popover-title lstutorial__template--title'></h3>"
                            ."<div class='popover-content lstutorial__template--content'></div>"
                            ."<div class='popover-navigation lstutorial__template--navigation'>"
                                ."<div class='row'>"
                                    ."<div class='btn-group col-xs-12' role='group' aria-label='...'>"
                                        ."<button class='btn btn-default col-md-6' data-role='prev'>".gT('Previous')."</button>"
                                        ."<button class='btn btn-primary col-md-6' data-role='next'>".gT('Next')."</button>"
                                    ."</div>"
                                ."</div>"
                                ."<div class='row ls-space margin top-5'>"
                                    ."<div class='text-left col-sm-12'>"
                                        ."<button class='pull-left btn btn-warning col-sm-6' data-role='end'>".gT('End tour')."</button>"
                                    ."</div>"
                                ."</div>"
                            ."</div>"
                        ."</div>",
                        'onShown' => "(function(tour){ console.ls.log($('#notif-container').children()); $('#notif-container').children().remove(); })",
                        'onEnd' => "(function(tour){window.location.reload();})",
                        'endOnOrphan' => true,
                    )), 
                ], 
                "tid=1"
            );

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>339), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Rename 'First start tour' to 'Take beginner tour'.
         */
        If ($iOldDBVersion < 340) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{tutorials}}', array('title'=>'Beginner tour'), "name='firstStartTour'");
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>340), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Recreate basic tour again from DefaultDataSet
         */
        If ($iOldDBVersion < 341) {
            $oTransaction = $oDB->beginTransaction();
            
            $oDB->createCommand()->truncateTable('{{tutorials}}');
            foreach($tutorialsData=LsDefaultDataSets::getTutorialData() as $tutorials){
                $oDB->createCommand()->insert('{{tutorials}}', $tutorials);
            }
            
            $oDB->createCommand()->truncateTable('{{tutorial_entries}}');
            $oDB->createCommand()->truncateTable('{{tutorial_entry_relation}}');

            foreach($tutorialEntryData=LsDefaultDataSets::getTutorialEntryData() as $tutorialEntry) {
                $teid =  $tutorialEntry['teid'];
                unset($tutorialEntry['teid']);
                $oDB->createCommand()->insert('{{tutorial_entries}}', $tutorialEntry);
                $oDB->createCommand()->insert('{{tutorial_entry_relation}}', array('tid' => 1, 'teid' => $teid));
            }

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value'=>341), "stg_name='DBVersion'");
            $oTransaction->commit();
        }

        /**
         * Url parameter "surveyid" should be "sid" for this link.
         */
        If ($iOldDBVersion < 342) {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{surveymenu_entries}}', array('data'=>'{"render": { "link": {"data": {"sid": ["survey","sid"]}}}}'), "name='surveyLogicFile'");
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
                    'sid'               =>  NULL,
                    'gsid'              =>  NULL,
                    'uid'               =>  NULL,
                    'files_css'         => '{"add":["css/ajaxify.css","css/theme.css","css/custom.css"]}',
                    'files_js'          =>  '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
                    'files_print_css'   => '{"add":["css/print_theme.css"]}',
                    'options'           => '{"ajaxmode":"on","brandlogo":"on","container":"on","brandlogofile":"./files/logo.png","font":"noto"}',
                    'cssframework_name' => 'bootstrap',
                    'cssframework_css'  => '{}',
                    'cssframework_js'   => '',
                    'packages_to_load'  => '{"add":["pjax","font-noto"]}',
                    'packages_ltr'      => NULL,
                    'packages_rtl'      => NULL
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
                        'options'           => '{"ajaxmode":"off","brandlogo":"on","brandlogofile":"./files/logo.png","container":"on","backgroundimage":"off","backgroundimagefile":"./files/pattern.png","animatebody":"off","bodyanimation":"fadeInRight","bodyanimationduration":"1.0","animatequestion":"off","questionanimation":"flipInX","questionanimationduration":"1.0","animatealert":"off","alertanimation":"shake","alertanimationduration":"1.0","font":"noto","bodybackgroundcolor":"#ffffff","fontcolor":"#444444","questionbackgroundcolor":"#ffffff","questionborder":"on","questioncontainershadow":"on","checkicon":"f00c","animatecheckbox":"on","checkboxanimation":"rubberBand","checkboxanimationduration":"0.5","animateradio":"on","radioanimation":"zoomIn","radioanimationduration":"0.3"}',
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
                    'sid'               =>  NULL,
                    'gsid'              =>  NULL,
                    'uid'               =>  NULL,
                    'files_css'         => '{"add":["css/ajaxify.css","css/animate.css","css/variations/sea_green.css","css/theme.css","css/custom.css"]}',
                    'files_js'          => '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
                    'files_print_css'   => '{"add":["css/print_theme.css"]}',
                    'options'           => '{"ajaxmode":"off","brandlogo":"on","brandlogofile":"./files/logo.png","container":"on","backgroundimage":"off","backgroundimagefile":"./files/pattern.png","animatebody":"off","bodyanimation":"fadeInRight","bodyanimationduration":"1.0","animatequestion":"off","questionanimation":"flipInX","questionanimationduration":"1.0","animatealert":"off","alertanimation":"shake","alertanimationduration":"1.0","font":"noto","bodybackgroundcolor":"#ffffff","fontcolor":"#444444","questionbackgroundcolor":"#ffffff","questionborder":"on","questioncontainershadow":"on","checkicon":"f00c","animatecheckbox":"on","checkboxanimation":"rubberBand","checkboxanimationduration":"0.5","animateradio":"on","radioanimation":"zoomIn","radioanimationduration":"0.3"}',
                    'cssframework_name' => 'bootstrap',
                    'cssframework_css'  => '{}',
                    'cssframework_js'   => '',
                    'packages_to_load'  => '{"add":["pjax","font-noto","moment"]}',
                    'packages_ltr'      => NULL,
                    'packages_rtl'      => NULL
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
                        'options'           => '{"ajaxmode":"on","brandlogo":"on","container":"on","brandlogofile":"./files/logo.png"}',
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
                    'sid'               =>  NULL,
                    'gsid'              =>  NULL,
                    'uid'               =>  NULL,
                    'files_css'         => '{"add":["css/ajaxify.css","css/theme.css","css/custom.css"]}',
                    'files_js'          =>  '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
                    'files_print_css'   => '{"add":["css/print_theme.css"]}',
                    'options'           => '{"ajaxmode":"on","brandlogo":"on","container":"on","brandlogofile":"./files/logo.png"}',
                    'cssframework_name' => 'bootstrap',
                    'cssframework_css'  => '{"replace":[["css/bootstrap.css","css/variations/flatly.min.css"]]}',
                    'cssframework_js'   => '',
                    'packages_to_load'  => '{"add":["pjax","font-noto"]}',
                    'packages_ltr'      => NULL,
                    'packages_rtl'      => NULL
                ];
                $oDB->createCommand()->insert('{{template_configuration}}', $bootswatchConfData);
            }
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value'=>345], "stg_name='DBVersion'");
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
            . sprintf(gT('File %s, line %s.'),$file,$trace[1]['line'])
        );
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
    Survey::model()->refreshMetaData();
    Notification::model()->refreshMetaData();

    // Inform  superadmin about update
    $superadmins = User::model()->getSuperAdmins();
    $currentDbVersion = $oDB->createCommand()->select('stg_value')->from('{{settings_global}}')->where("stg_name=:stg_name", array('stg_name'=>'DBVersion'))->queryRow();

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
    $oDB->createCommand()->insert('{{tutorials}}', array(
        'tid' => 1,
        'name' => 'firstStartTour',
        'title' => 'Take beginner tour',
        'icon' => 'fa-rocket',
        'description' => 'The first start tour to get your first feeling into LimeSurvey',
        'active' => 1,
        'settings' => json_encode(array(
            'keyboard' => false,
            'orphan' => true,
            'template' => "<div class='popover tour lstutorial__template--mainContainer'> <div class='arrow'></div> <h3 class='popover-title lstutorial__template--title'></h3> <div class='popover-content lstutorial__template--content'></div> <div class='popover-navigation lstutorial__template--navigation'>     <div class='btn-group col-xs-8' role='group' aria-label='...'>         <button class='btn btn-default col-xs-6' data-role='prev'>".gT('Previous')."</button>         <button class='btn btn-primary col-xs-6' data-role='next'>".gT('Next')."</button>     </div>     <div class='col-xs-4'>         <button class='btn btn-warning' data-role='end'>".gT('End tour')."</button>     </div> </div></div>",
            'onShown' => "(function(tour){ console.ls.log($('#notif-container').children()); $('#notif-container').children().remove(); })",
            'onEnd' => "(function(tour){window.location.reload();})",
            'endOnOrphan' => true,
        )),
        'permission' => 'survey',
        'permission_grade' => 'create'
    ));
    $oDB->createCommand()->truncateTable('{{tutorial_entries}}');
    $oDB->createCommand()->truncateTable('{{tutorial_entry_relation}}');
    $contentArrays = array(
        array(
            'teid' => 1,
            'ordering' => 1,
            'title' => 'Welcome to LimeSurvey!',
            'content' => "This tour will help you to easily get a basic understanding of LimeSurvey."."<br/>"
                ."We would like to help you with a quick tour of the most essential functions and features.",
            'settings' => json_encode(array(
                'element' => '#lime-logo',
                'path' => ['/admin/index'],
                'placement' => 'bottom',
                'redirect' => true,
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
                'path' => ['/admin/index'],
                'reflex' => true,
                'onShow' => "(function(tour){ $('#welcomeModal').modal('hide'); })",
                'onNext' => "(function(tour){
                    tour.setCurrentStep(2);
                    return new Promise(function(res,rej){});
                })",
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
                'path' => ['/admin/survey/sa/newsurvey'],
                'element' => '#surveyls_title',
                'redirect' => true,
                'prev' => '-1',
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
                'path' => ['/admin/survey/sa/newsurvey'],
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
                'path' => ['/admin/survey/sa/newsurvey'],
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
                'path' => ['/admin/survey/sa/newsurvey'],
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
                'path' => ['/admin/survey/sa/newsurvey'],
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
                'path' => ['/admin/survey/sa/newsurvey'],
                'placement' => 'bottom',
                'reflex' => true,
                'redirect' => false,
                'onNext' => "(function(tour){
                                tour.setCurrentStep(8);
                                $('#save-form-button').trigger('click');
                                return new Promise(function(res,rej){});
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
                'placement' => 'top',
                'redirect' => false,
                'prev' => '-1',
                'onShow' => "(function(tour){
                    $('#adminpanel__sidebar--selectorSettingsButton').trigger('click');
                })",
            ))
        ),
        array(
            'teid' => 10,
            'ordering' => 10,
            'title' => 'The settings tab with the survey menu',
            'content' => 'If you click on this tab, the survey settings menu will be displayed.'.' '
            .'The most important settings of your survey are accessible from this menu.'.'<br/>'
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
                                tour.setCurrentStep(13);
                                return new Promise(function(res,rej){});
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
                'prev' => '-1',
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
                                tour.setCurrentStep(17);
                                return new Promise(function(res,rej){});
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
                'prev' => '-1',
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
                                tour.setCurrentStep(22);
                                return new Promise(function(res,rej){});
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
                'backdrop' => false,
                'redirect' => false,
                'prev' => '-1',
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
                                tour.setCurrentStep(24);
                                return new Promise(function(res,rej){});
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
                'prev' => '-1',
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
                                tour.setCurrentStep(27);
                                return new Promise(function(res,rej){});
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
                'prev' => '-1',
                'onNext' => "(function(tour){
                                document.location.href = $('#adminpanel__topbar--selectorAddAnswerOptions').attr('href');
                                tour.setCurrentStep(28);
                                return new Promise(function(res,rej){});
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
                'prev' => '-1',
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
                                tour.setCurrentStep(30);
                                return new Promise(function(res,rej){});
                            })"
            ))
        ),
        array(
            'teid' => 31,
            'ordering' => 31,
            'title' => 'Preview survey',
            'content' => "Let's have a look at your first survey.".'<br/>'
            ."Just click on this button and a new window will open, where you can test run your survey.".'<br/>'
            ."Please be aware that your answers will not be saved, because the survey isn't active yet."
            .'<p class="alert bg-warning">'."Click on 'Preview survey' and return to this window when you are done testing.".'</p>',
            'settings' => json_encode(array(
                'element' => '.selector__topbar--previewSurvey',
                'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                'placement' => 'bottom',
                'redirect' => false,
                'prev' => '-1',
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
                'element' => '#breadcrumb__survey--overview',
                'path' => ['/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}']],
                'placement' => 'bottom',
                'reflex' => true,
                'redirect' => false,
                'onNext' => "(function(tour){
                                tour.setCurrentStep(32);
                                document.location.href = $('#breadcrumb__survey--overview').attr('href');
                                return new Promise(function(res,rej){});
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
                'prev' => '-1',
                'onNext' => "(function(tour){
                        document.location.href = $('#ls-activate-survey').attr('href');
                        tour.setCurrentStep(33);
                        return new Promise(function(res,rej){});
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
                'placement' => '',
                'reflex' => true,
                'redirect' => false,
                'prev' => '-1',
                'onNext' => "(function(tour){
                        $('#activateSurvey__basicSettings--proceed').trigger('click');
                        tour.setCurrentStep(34);
                        return new Promise(function(res,rej){});
                    })",
            ))
        ),
        array(
            'teid' => 35,
            'ordering' => 35,
            'title' => ('Activate survey participants table'),
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
                'prev' => '-1',
                'onNext' => "(function(tour){
                        $('#activateTokenTable__selector--no').trigger('click');
                        tour.setCurrentStep(35);
                        return new Promise(function(res,rej){});
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
                'redirect' => false,
                'prev' => '-1',
                'onHide' => '(function(){window.location.reload()})'
            ))
        ),
    );

    foreach ($contentArrays as $contentArray) {
        $oDB->createCommand()->insert('{{tutorial_entries}}', $contentArray);
        $oDB->createCommand()->insert('{{tutorial_entry_relation}}', array('tid' => 1, 'teid' => $contentArray['teid']));
    }

}

/**
* @param CDbConnection $oDB
* @return void
*/
function upgrade333($oDB)
{

    $oDB->createCommand()->createTable('{{map_tutorial_users}}', array(
        'tid' => 'int NOT NULL',
        'uid' => 'int DEFAULT NULL',
        'taken' => 'int DEFAULT 1',
    ));

    $oDB->createCommand()->addPrimaryKey('{{map_tutorial_users_pk}}', '{{map_tutorial_users}}', ['uid', 'tid']);

    $oDB->createCommand()->createTable('{{tutorial_entry_relation}}', array(
        'teid' => 'int NOT NULL',
        'tid' => 'int NOT NULL',
        'uid' => 'int DEFAULT NULL',
        'sid' => 'int DEFAULT NULL',
    ));

    $oDB->createCommand()->addPrimaryKey('{{tutorial_entry_relation_pk}}', '{{tutorial_entry_relation}}', ['teid', 'tid']);
    $oDB->createCommand()->createIndex('{{idx1_tutorial_entry_relation}}', '{{tutorial_entry_relation}}', 'uid', false);
    $oDB->createCommand()->createIndex('{{idx2_tutorial_entry_relation}}', '{{tutorial_entry_relation}}', 'sid', false);

    $oDB->createCommand()->createIndex('{{idx1_tutorials}}', '{{tutorials}}', 'name', true);

    $oDB->createCommand()->insert('{{tutorials}}', array(
        'tid' => 1,
        'name' => 'firstStartTour',
        'description' => 'The first start tour to get your first feeling into LimeSurvey',
        'active' => 1,
        'settings' => json_encode(array(
            'debug' => true,
            'orphan' => true,
            'keyboard' => false,
            'template' => "<div class='popover tour lstutorial__template--mainContainer'> <div class='arrow'></div> <h3 class='popover-title lstutorial__template--title'></h3> <div class='popover-content lstutorial__template--content'></div> <div class='popover-navigation lstutorial__template--navigation'>     <div class='btn-group col-xs-8' role='group' aria-label='...'>         <button class='btn btn-default col-xs-6' data-role='prev'>".gT('Previous')."</button>         <button class='btn btn-primary col-xs-6' data-role='next'>".gT('Next')."</button>     </div>     <div class='col-xs-4'>         <button class='btn btn-warning' data-role='end'>".gT('End tour')."</button>     </div> </div></div>",
            'onShown' => "(function(tour){ console.ls.log($('#notif-container').children()); $('#notif-container').children().remove(); })",
        )),
        'permission' => 'survey',
        'permission_grade' => 'create'

    ));

    $oDB->createCommand()->dropColumn('{{tutorial_entries}}', 'tid');
    $oDB->createCommand()->addColumn('{{tutorial_entries}}', 'ordering', 'int');

    $contentArrays = array(
        array(
            'teid' => 1,
            'ordering' => 1,
            'title' => 'Welcome to LimeSurvey!',
            'content' => "This tour will help you to easily get a basic understanding of LimeSurvey."."<br/>"
                ."We would like to help you with a quick tour of the most essential functions and features.",
            'settings' => json_encode(array(
                'element' => '#lime-logo',
                'orphan' => true,
                'path' => ['/admin/index'],
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
                'path' => ['/admin/index'],
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
                'path' => ['/admin/survey/sa/newsurvey'],
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
                'path' => ['/admin/survey/sa/newsurvey'],
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
                'path' => ['/admin/survey/sa/newsurvey'],
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
                'path' => ['/admin/survey/sa/newsurvey'],
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
                'path' => ['/admin/survey/sa/newsurvey'],
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
                'path' => ['/admin/survey/sa/newsurvey'],
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
            .'The most important settings of your survey are accessible from this menu.'.'<br/>'
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
            'title' => ('Activate survey participants table'),
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

    foreach ($contentArrays as $contentArray) {
        $oDB->createCommand()->insert('{{tutorial_entries}}', $contentArray);
        $oDB->createCommand()->insert('{{tutorial_entry_relation}}', array('tid' => 1, 'teid' => $contentArray['teid']));
        $combined = array();
    }

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


    // Update the survey menu so it uses the themeoptions controller
    $oDB->createCommand()->update('{{surveymenu_entries}}', array(
        'menu_id'          => 1,
        'user_id'          => null,
        'ordering'         => 4,
        'name'             => 'theme_options',
        'title'            => 'Theme options',
        'menu_title'       => 'Theme options',
        'menu_description' => 'Edit theme options for this survey',
        'menu_icon'        =>  'paint-brush',
        'menu_icon_type'   =>  'fontawesome',
        'menu_class'       =>  '',
        'menu_link'        => 'admin/themeoptions/sa/updatesurvey',
        'action'           =>  '',
        'partial'          => '',
        'classes'          =>  '',
        'permission'       =>  'templates', // TODO: change permission from template to theme
        'permission_grade' =>  'read',
        'data'             =>  '{"render": {"link": { "data": {"surveyid": ["survey","sid"], "gsid":["survey","gsid"]}}}}',
        'getdatamethod'    =>  '',
        'language'         =>  'en-GB',
        'active'           =>  1,
        'changed_at'       =>  date('Y-m-d H:i:s'),
        'changed_by'       =>  0,
        'created_at'       =>  date('Y-m-d H:i:s'),
        'created_by'       =>  0
    ), "name='template_options'");

}

/**
 * @param CDbConnection $oDB
 */
function transferPasswordFieldToText($oDB)
{
    switch ($oDB->getDriverName()) {
        case 'mysql':
        case 'mysqli':
            $oDB->createCommand()->alterColumn('{{users}}', 'password', 'TEXT NOT NULL');
            break;
        case 'pgsql':

            $userPasswords = $oDB->createCommand()->select(['uid', "encode(password::bytea, 'escape') as password"])->from('{{users}}')->queryAll();

            $oDB->createCommand()->renameColumn('{{users}}', 'password', 'password_blob');
            $oDB->createCommand()->addColumn('{{users}}', 'password', "TEXT NOT NULL DEFAULT 'nopw'");

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

function createSurveyMenuTable293($oDB)
{
    // Drop the old survey rights table.
    if (tableExists('{surveymenu_entries}')) {
        $oDB->createCommand()->dropTable('{{surveymenu_entries}}');
    }

    if (tableExists('{surveymenu}')) {
        $oDB->createCommand()->dropTable('{{surveymenu}}');
    }


    $oDB->createCommand()->createTable('{{surveymenu}}', array(
        "id" => "pk",
        "parent_id" => "int DEFAULT NULL",
        "survey_id" => "int DEFAULT NULL",
        "order" => "int DEFAULT '0'",
        "level" => "int DEFAULT '0'",
        "title" => "string(168) NOT NULL DEFAULT ''",
        "description" => "text ",
        "changed_at" => "datetime NULL",
        "changed_by" => "int NOT NULL DEFAULT '0'",
        "created_at" => "datetime DEFAULT NULL",
        "created_by" => "int NOT NULL DEFAULT '0'",

    ));

    $oDB->createCommand()->insert(
        '{{surveymenu}}',
        array(
            'parent_id' => null,
            'survey_id' => null,
            'order' => 0,
            'level' => 0,
            'title' => 'Survey menu',
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
        "title" => "character varying(168)  NOT NULL DEFAULT ''",
        "menu_title" => "character varying(168)  NOT NULL DEFAULT ''",
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
        "changed_at" => "datetime DEFAULT NULL",
        "changed_by" => "int NOT NULL DEFAULT '0'",
        "created_at" => "datetime DEFAULT NULL",
        "created_by" => "int NOT NULL DEFAULT '0'",
        "FOREIGN KEY (menu_id) REFERENCES  {{surveymenu}} (id) ON DELETE CASCADE"
    ));

    $colsToAdd = array("menu_id", "order", "name", "title", "menu_title", "menu_description", "menu_icon", "menu_icon_type", "menu_class", "menu_link", "action", "template", "partial", "classes", "permission", "permission_grade", "data", "getdatamethod", "language", "changed_at", "changed_by", "created_at", "created_by");
    $rowsToAdd = array(
        array(1, 1, 'overview', 'Survey overview', 'Overview', 'Open general survey overview and quick action', 'list', 'fontawesome', '', 'admin/survey/sa/view', '', '', '', '', '', '', null, '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, 2, 'generalsettings', 'General survey settings', 'General settings', 'Open general survey settings', 'gears', 'fontawesome', '', '', 'updatesurveylocalesettings_generalsettings', 'editLocalSettings_main_view', '/admin/survey/subview/accordion/_generaloptions_panel', '', 'surveysettings', 'read', null, '_generalTabEditSurvey', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, 3, 'surveytexts', 'Survey text elements', 'Text elements', 'Survey text elements', 'file-text-o', 'fontawesome', '', '', 'updatesurveylocalesettings', 'editLocalSettings_main_view', '/admin/survey/subview/tab_edit_view', '', 'surveylocale', 'read', null, '_getTextEditData', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, 4, 'presentation', 'Presentation &amp; navigation settings', 'Presentation', 'Edit presentation and navigation settings', 'eye-slash', 'fontawesome', '', '', 'updatesurveylocalesettings', 'editLocalSettings_main_view', '/admin/survey/subview/accordion/_presentation_panel', '', 'surveylocale', 'read', null, '_tabPresentationNavigation', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, 5, 'publication', 'Publication and access control settings', 'Publication &amp; access', 'Edit settings for publicationa and access control', 'key', 'fontawesome', '', '', 'updatesurveylocalesettings', 'editLocalSettings_main_view', '/admin/survey/subview/accordion/_publication_panel', '', 'surveylocale', 'read', null, '_tabPublicationAccess', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, 6, 'surveypermissions', 'Edit surveypermissions', 'Survey permissions', 'Edit permissions for this survey', 'lock', 'fontawesome', '', 'admin/surveypermission/sa/view/', '', '', '', '', 'surveysecurity', 'read', null, '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, 7, 'tokens', 'Survey participant settings', 'Participant settings', 'Set additional options for survey participants', 'users', 'fontawesome', '', '', 'updatesurveylocalesettings', 'editLocalSettings_main_view', '/admin/survey/subview/accordion/_tokens_panel', '', 'surveylocale', 'read', null, '_tabTokens', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, 8, 'quotas', 'Edit quotas', 'Quotas', 'Edit quotas for this survey.', 'tasks', 'fontawesome', '', 'admin/quotas/sa/index/', '', '', '', '', 'quotas', 'read', null, '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, 9, 'assessments', 'Edit assessments', 'Assessments', 'Edit and look at the assessements for this survey.', 'comment-o', 'fontawesome', '', 'admin/assessments/sa/index/', '', '', '', '', 'assessments', 'read', null, '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, 10, 'notification', 'Notification and data management settings', 'Data management', 'Edit settings for notification and data management', 'feed', 'fontawesome', '', '', 'updatesurveylocalesettings', 'editLocalSettings_main_view', '/admin/survey/subview/accordion/_notification_panel', '', 'surveylocale', 'read', null, '_tabNotificationDataManagement', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, 11, 'emailtemplates', 'Email templates', 'Email templates', 'Edit the templates for invitation, reminder and registration emails', 'envelope-square', 'fontawesome', '', 'admin/emailtemplates/sa/index/', '', '', '', '', 'assessments', 'read', null, '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, 12, 'panelintegration', 'Edit survey panel integration', 'Panel integration', 'Define panel integrations for your survey', 'link', 'fontawesome', '', '', 'updatesurveylocalesettings', 'editLocalSettings_main_view', '/admin/survey/subview/accordion/_integration_panel', '', 'surveylocale', 'read', null, '_tabPanelIntegration', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, 13, 'ressources', 'Add/Edit ressources to the survey', 'Ressources', 'Add/Edit ressources to the survey', 'file', 'fontawesome', '', '', 'updatesurveylocalesettings', 'editLocalSettings_main_view', '/admin/survey/subview/accordion/_resources_panel', '', 'surveylocale', 'read', null, '_tabResourceManagement', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0)
    );
    foreach ($rowsToAdd as $row) {
        $oDB->createCommand()->insert('{{surveymenu_entries}}', array_combine($colsToAdd, $row));
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

    // Drop the old surveymenu_entries table.
    if (tableExists('{surveymenu_entries}')) {
        $oDB->createCommand()->dropTable('{{surveymenu_entries}}');
    }

    // Drop the old surveymenu table.
    if (tableExists('{surveymenu}')) {
        $oDB->createCommand()->dropTable('{{surveymenu}}');
    }

    $oDB->createCommand()->createTable('{{surveymenu}}', array(
        "id" =>  "pk",
        "parent_id" =>  "integer DEFAULT NULL",
        "survey_id" =>  "integer DEFAULT NULL",
        "user_id" =>  "integer DEFAULT NULL",
        "ordering" =>  "integer DEFAULT '0'",
        "level" =>  "integer DEFAULT '0'",
        "title" =>  "string(168)  NOT NULL DEFAULT ''",
        "position" =>  "string(192)  NOT NULL DEFAULT 'side'",
        "description" =>  "text ",
        "changed_at" =>  "datetime",
        "changed_by" =>  "integer NOT NULL DEFAULT '0'",
        "created_at" =>  "datetime",
        "created_by" =>  "integer NOT NULL DEFAULT '0'",
    ));
    $oDB->createCommand()->createIndex('{{idx_ordering}}', '{{surveymenu}}', 'ordering');
    $oDB->createCommand()->createIndex('{{idx_title}}', '{{surveymenu}}', 'title');

    $oDB->createCommand()->insert(
        '{{surveymenu}}',
        array(
            "parent_id" =>null,
            "survey_id" =>null,
            "user_id" =>null,
            "ordering" =>1,
            "level" =>0,
            "title" =>'Survey Menu',
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
            "parent_id" =>null,
            "survey_id" =>null,
            "user_id" =>null,
            "ordering" =>1,
            "level" =>0,
            "title" =>'Quick menu',
            "position" =>'collapsed',
            "description" =>'Quick menu',
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
        "name" => "string(168)  NOT NULL DEFAULT ''",
        "title" => "string(168)  NOT NULL DEFAULT ''",
        "menu_title" => "string(168)  NOT NULL DEFAULT ''",
        "menu_description" => "text ",
        "menu_icon" => "string(192)  NOT NULL DEFAULT ''",
        "menu_icon_type" => "string(192)  NOT NULL DEFAULT ''",
        "menu_class" => "string(192)  NOT NULL DEFAULT ''",
        "menu_link" => "string(192)  NOT NULL DEFAULT ''",
        "action" => "string(192)  NOT NULL DEFAULT ''",
        "template" => "string(192)  NOT NULL DEFAULT ''",
        "partial" => "string(192)  NOT NULL DEFAULT ''",
        "classes" => "string(192)  NOT NULL DEFAULT ''",
        "permission" => "string(192)  NOT NULL DEFAULT ''",
        "permission_grade" => "string(192)  DEFAULT NULL",
        "data" => "text ",
        "getdatamethod" => "string(192)  NOT NULL DEFAULT ''",
        "language" => "string(32)  NOT NULL DEFAULT 'en-GB'",
        "changed_at" => "datetime NULL",
        "changed_by" => "integer NOT NULL DEFAULT '0'",
        "created_at" => "datetime DEFAULT NULL",
        "created_by" => "integer NOT NULL DEFAULT '0'"
    ));
    $oDB->createCommand()->createIndex('{{idx_menu_id}}', '{{surveymenu_entries}}', 'menu_id');
    $oDB->createCommand()->createIndex('{{idx_menu_title}}', '{{surveymenu_entries}}', 'menu_title');

    $colsToAdd = array("menu_id", "user_id", "ordering", "name", "title", "menu_title", "menu_description", "menu_icon", "menu_icon_type", "menu_class", "menu_link", "action", "template", "partial", "classes", "permission", "permission_grade", "data", "getdatamethod", "language", "changed_at", "changed_by", "created_at", "created_by");
    $rowsToAdd = array(
        array(1, null, 1, 'overview', 'Survey overview', 'Overview', 'Open general survey overview and quick action', 'list', 'fontawesome', '', 'admin/survey/sa/view', '', '', '', '', '', '', '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, null, 2, 'generalsettings', 'General survey settings', 'General settings', 'Open general survey settings', 'gears', 'fontawesome', '', '', 'updatesurveylocalesettings_generalsettings', 'editLocalSettings_main_view', '/admin/survey/subview/accordion/_generaloptions_panel', '', 'surveysettings', 'read', null, '_generalTabEditSurvey', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, null, 3, 'surveytexts', 'Survey text elements', 'Text elements', 'Survey text elements', 'file-text-o', 'fontawesome', '', '', 'updatesurveylocalesettings', 'editLocalSettings_main_view', '/admin/survey/subview/tab_edit_view', '', 'surveylocale', 'read', null, '_getTextEditData', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, null, 4, 'template_options', 'Template options', 'Template options', 'Edit Template options for this survey', 'paint-brush', 'fontawesome', '', 'admin/templateoptions/sa/updatesurvey', '', '', '', '', 'templates', 'read', '{"render": {"link": { "pjaxed": true, "data": {"surveyid": ["survey","sid"], "gsid":["survey","gsid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, null, 5, 'participants', 'Survey participants', 'Survey participants', 'Go to survey participant and token settings', 'user', 'fontawesome', '', 'admin/tokens/sa/index/', '', '', '', '', 'surveysettings', 'update', '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, null, 6, 'presentation', 'Presentation &amp; navigation settings', 'Presentation', 'Edit presentation and navigation settings', 'eye-slash', 'fontawesome', '', '', 'updatesurveylocalesettings', 'editLocalSettings_main_view', '/admin/survey/subview/accordion/_presentation_panel', '', 'surveylocale', 'read', null, '_tabPresentationNavigation', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, null, 7, 'publication', 'Publication and access control settings', 'Publication &amp; access', 'Edit settings for publicationa and access control', 'key', 'fontawesome', '', '', 'updatesurveylocalesettings', 'editLocalSettings_main_view', '/admin/survey/subview/accordion/_publication_panel', '', 'surveylocale', 'read', null, '_tabPublicationAccess', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, null, 8, 'surveypermissions', 'Edit surveypermissions', 'Survey permissions', 'Edit permissions for this survey', 'lock', 'fontawesome', '', 'admin/surveypermission/sa/view/', '', '', '', '', 'surveysecurity', 'read', '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, null, 9, 'tokens', 'Survey participant settings', 'Participant settings', 'Set additional options for survey participants', 'users', 'fontawesome', '', '', 'updatesurveylocalesettings', 'editLocalSettings_main_view', '/admin/survey/subview/accordion/_tokens_panel', '', 'surveylocale', 'read', null, '_tabTokens', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, null, 10, 'quotas', 'Edit quotas', 'Quotas', 'Edit quotas for this survey.', 'tasks', 'fontawesome', '', 'admin/quotas/sa/index/', '', '', '', '', 'quotas', 'read', '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, null, 11, 'assessments', 'Edit assessments', 'Assessments', 'Edit and look at the assessements for this survey.', 'comment-o', 'fontawesome', '', 'admin/assessments/sa/index/', '', '', '', '', 'assessments', 'read', '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, null, 12, 'notification', 'Notification and data management settings', 'Data management', 'Edit settings for notification and data management', 'feed', 'fontawesome', '', '', 'updatesurveylocalesettings', 'editLocalSettings_main_view', '/admin/survey/subview/accordion/_notification_panel', '', 'surveylocale', 'read', null, '_tabNotificationDataManagement', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, null, 13, 'emailtemplates', 'Email templates', 'Email templates', 'Edit the templates for invitation, reminder and registration emails', 'envelope-square', 'fontawesome', '', 'admin/emailtemplates/sa/index/', '', '', '', '', 'assessments', 'read', '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, null, 14, 'panelintegration', 'Edit survey panel integration', 'Panel integration', 'Define panel integrations for your survey', 'link', 'fontawesome', '', '', 'updatesurveylocalesettings', 'editLocalSettings_main_view', '/admin/survey/subview/accordion/_integration_panel', '', 'surveylocale', 'read', null, '_tabPanelIntegration', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(1, null, 15, 'ressources', 'Add/Edit ressources to the survey', 'Ressources', 'Add/Edit ressources to the survey', 'file', 'fontawesome', '', '', 'updatesurveylocalesettings', 'editLocalSettings_main_view', '/admin/survey/subview/accordion/_resources_panel', '', 'surveylocale', 'read', null, '_tabResourceManagement', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(2, null, 1, 'activateSurvey', 'Activate survey', 'Activate survey', 'Activate survey', 'play', 'fontawesome', '', 'admin/survey/sa/activate', '', '', '', '', 'surveyactivation', 'update', '{"render": {"isActive": false, "link": {"data": {"surveyid": ["survey","sid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(2, null, 2, 'deactivateSurvey', 'Stop this survey', 'Stop this survey', 'Stop this survey', 'stop', 'fontawesome', '', 'admin/survey/sa/deactivate', '', '', '', '', 'surveyactivation', 'update', '{"render": {"isActive": true, "link": {"data": {"surveyid": ["survey","sid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(2, null, 3, 'testSurvey', 'Go to survey', 'Go to survey', 'Go to survey', 'cog', 'fontawesome', '', 'survey/index/', '', '', '', '', '', '', '{"render": {"link": {"external": true, "data": {"sid": ["survey","sid"], "newtest": "Y", "lang": ["survey","language"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(2, null, 4, 'listQuestions', 'List questions', 'List questions', 'List questions', 'list', 'fontawesome', '', 'admin/survey/sa/listquestions', '', '', '', '', 'surveycontent', 'read', '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(2, null, 5, 'listQuestionGroups', 'List question groups', 'List question groups', 'List question groups', 'th-list', 'fontawesome', '', 'admin/survey/sa/listquestiongroups', '', '', '', '', 'surveycontent', 'read', '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(2, null, 6, 'generalsettings', 'General survey settings', 'General settings', 'Open general survey settings', 'gears', 'fontawesome', '', '', 'updatesurveylocalesettings_generalsettings', 'editLocalSettings_main_view', '/admin/survey/subview/accordion/_generaloptions_panel', '', 'surveysettings', 'read', null, '_generalTabEditSurvey', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(2, null, 7, 'surveypermissions', 'Edit surveypermissions', 'Survey permissions', 'Edit permissions for this survey', 'lock', 'fontawesome', '', 'admin/surveypermission/sa/view/', '', '', '', '', 'surveysecurity', 'read', '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(2, null, 8, 'quotas', 'Edit quotas', 'Quotas', 'Edit quotas for this survey.', 'tasks', 'fontawesome', '', 'admin/quotas/sa/index/', '', '', '', '', 'quotas', 'read', '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(2, null, 9, 'assessments', 'Edit assessments', 'Assessments', 'Edit and look at the assessements for this survey.', 'comment-o', 'fontawesome', '', 'admin/assessments/sa/index/', '', '', '', '', 'assessments', 'read', '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(2, null, 10, 'emailtemplates', 'Email templates', 'Email templates', 'Edit the templates for invitation, reminder and registration emails', 'envelope-square', 'fontawesome', '', 'admin/emailtemplates/sa/index/', '', '', '', '', 'surveylocale', 'read', '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(2, null, 11, 'surveyLogicFile', 'Survey logic file', 'Survey logic file', 'Survey logic file', 'sitemap', 'fontawesome', '', 'admin/expressions/sa/survey_logic_file/', '', '', '', '', 'surveycontent', 'read', '{"render": { "link": {"data": {"sid": ["survey","sid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(2, null, 12, 'tokens', 'Survey participant settings', 'Participant settings', 'Set additional options for survey participants', 'user', 'fontawesome', '', '', 'updatesurveylocalesettings', 'editLocalSettings_main_view', '/admin/survey/subview/accordion/_tokens_panel', '', 'surveylocale', 'read', '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}', '_tabTokens', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(2, null, 13, 'cpdb', 'Central participant database', 'Central participant database', 'Central participant database', 'users', 'fontawesome', '', 'admin/participants/sa/displayParticipants', '', '', '', '', 'tokens', 'read', '{"render": {"link": {}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(2, null, 14, 'responses', 'Responses', 'Responses', 'Responses', 'icon-browse', 'iconclass', '', 'admin/responses/sa/browse/', '', '', '', '', 'responses', 'read', '{"render": {"isActive": true}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(2, null, 15, 'statistics', 'Statistics', 'Statistics', 'Statistics', 'bar-chart', 'fontawesome', '', 'admin/statistics/sa/index/', '', '', '', '', 'statistics', 'read', '{"render": {"isActive": true}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0),
        array(2, null, 16, 'reorder', 'Reorder questions/question groups', 'Reorder questions/question groups', 'Reorder questions/question groups', 'icon-organize', 'iconclass', '', 'admin/survey/sa/organize/', '', '', '', '', 'surveycontent', 'update', '{"render": {"isActive": false, "link": {"data": {"surveyid": ["survey","sid"]}}}}', '', 'en-GB', date('Y-m-d H:i:s'), 0, date('Y-m-d H:i:s'), 0)
    );
    foreach ($rowsToAdd as $row) {
        $oDB->createCommand()->insert('{{surveymenu_entries}}', array_combine($colsToAdd, $row));
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
        'copyright'              => 'TEXT',
        'license'                => 'TEXT',
        'version'                => 'string(45) DEFAULT NULL',
        'api_version'            => 'string(45) NOT NULL',
        'view_folder'            => 'string(45) NOT NULL',
        'files_folder'           => 'string(45) NOT NULL',
        'description'            => 'TEXT',
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
        'files_print_css'   => '{"add":"css/print_template.css"}',
        'options'           => '{"ajaxmode":"on","brandlogo":"on", "brandlogofile":"./files/logo.png", "boxcontainer":"on", "backgroundimage":"off","animatebody":"off","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}',
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
        'options'           => '{"ajaxmode":"on","brandlogo":"on", "brandlogofile":"./files/logo.png", "animatebody":"off","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}',
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


function fixLanguageConsistencyAllSurveys()
{
    $surveyidquery = "SELECT sid,additional_languages FROM ".App()->db->quoteColumnName('{{surveys}}');
    $surveyidresult = Yii::app()->db->createCommand($surveyidquery)->queryAll();
    foreach ($surveyidresult as $sv) {
        fixLanguageConsistency($sv['sid'], $sv['additional_languages']);
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
