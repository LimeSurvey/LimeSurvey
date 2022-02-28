<?php

namespace LimeSurvey\Helpers\Update;

class Update_470 extends DatabaseUpdateBase
{
    public function up()
    {
            // Add the new column to questions table
            $this->db->createCommand()->addColumn('{{questions}}', 'question_theme_name', 'string(150) NULL');
        switch (\Yii::app()->db->driverName) {
            case 'sqlsrv':
            case 'dblib':
            case 'mssql':
                $updateExtendedQuery = "UPDATE q SET q.question_theme_name = qt.value
                        FROM {{questions}} q
                        LEFT JOIN {{question_attributes}} qt ON qt.qid = q.qid AND qt.attribute = 'question_template' 
                        WHERE qt.value IS NOT NULL AND qt.value <> 'core' AND qt.value <> ''";
                $updateCoreQuery = "UPDATE q SET q.question_theme_name = qt.name
                        FROM {{questions}} q
                        LEFT JOIN {{question_themes}} qt ON qt.question_type = q.type AND qt.core_theme = 1 AND qt.extends = ''
                        WHERE q.question_theme_name IS NULL";
                $updateUserSettingsQuery = "UPDATE su SET stg_value = qt.name
                        FROM {{settings_user}} su
                        JOIN {{settings_user}} su2 ON su2.uid = su.uid AND su2.stg_name = 'preselectquestiontype'
                        JOIN {{question_themes}} qt ON qt.question_type = su2.stg_value
                        WHERE su.stg_name = 'preselectquestiontheme' AND su.stg_value = 'core'";
                break;
            case 'pgsql':
                $updateExtendedQuery = "UPDATE {{questions}} q SET question_theme_name = qt.value
                        FROM {{questions}} q2
                        LEFT JOIN {{question_attributes}} qt ON qt.qid = q2.qid AND qt.attribute = 'question_template' 
                        WHERE qt.value IS NOT NULL AND qt.value <> 'core' AND qt.value <> '' AND q.qid = q2.qid";
                $updateCoreQuery = "UPDATE {{questions}} q SET question_theme_name = qt.name
                        FROM {{questions}} q2
                        LEFT JOIN {{question_themes}} qt ON qt.question_type = q2.type AND qt.core_theme = true AND qt.extends = ''
                        WHERE q.question_theme_name IS NULL AND q.qid = q2.qid";
                $updateUserSettingsQuery = "UPDATE {{settings_user}} su SET stg_value = qt.name
                        FROM {{settings_user}} su1
                        JOIN {{settings_user}} su2 ON su2.uid = su1.uid AND su2.stg_name = 'preselectquestiontype'
                        JOIN {{question_themes}} qt ON qt.question_type = su2.stg_value
                        WHERE su1.stg_name = 'preselectquestiontheme' AND su1.stg_value = 'core' AND su.id = su1.id";
                break;
            default:
                $updateExtendedQuery = "UPDATE {{questions}} q LEFT JOIN {{question_attributes}} qt ON qt.qid = q.qid AND qt.attribute = 'question_template'
                        SET q.question_theme_name = qt.value 
                        WHERE qt.value IS NOT NULL AND qt.value <> 'core' AND qt.value <> ''";
                $updateCoreQuery = "UPDATE {{questions}} q LEFT JOIN {{question_themes}} qt ON qt.question_type = q.type AND qt.core_theme = 1 AND qt.extends = ''
                        SET q.question_theme_name = qt.name 
                        WHERE q.question_theme_name IS NULL";
                $updateUserSettingsQuery = "UPDATE {{settings_user}} su
                        JOIN {{settings_user}} su2 ON su2.uid = su.uid AND su2.stg_name = 'preselectquestiontype'
                        JOIN {{question_themes}} qt ON qt.question_type = su2.stg_value
                        SET su.stg_value = qt.name
                        WHERE su.stg_name = 'preselectquestiontheme' AND su.stg_value = 'core'";
        }

            // Fill column from question_attributes when it's not null or 'core'
            $this->db->createCommand($updateExtendedQuery)->execute();
            // Fill null question_theme_name values using the proper theme name
            $this->db->createCommand($updateCoreQuery)->execute();
            // Also update 'preselectquestiontheme' user settings where the value is 'core'
            $this->db->createCommand($updateUserSettingsQuery)->execute();
    }
}
