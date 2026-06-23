<?php

namespace LimeSurvey\Helpers\Update;

class Update_642 extends DatabaseUpdateBase
{
    /**
     * Add savequotaexit column to surveys and surveys_groupsettings tables
     */
    public function up()
    {
        // Get all active survey IDs (includes expired surveys)
        $command = \Yii::app()->db->createCommand();
        $activeSurveyIds = $command->select('sid')
            ->from('{{surveys}}')
            ->where("active='Y'")
            ->queryColumn();

        foreach ($activeSurveyIds as $surveyId) {
            $responseTableName = "{{survey_" . $surveyId . "}}";
            if (!tableExists($responseTableName)) {
                continue;
            }

            \Yii::app()->db->createCommand()->addColumn(
                $responseTableName,
                'quota_exit',
                'integer'
            );
        }

        // Add to surveys table if not exists
        $surveysTable = $this->db->schema->getTable('{{surveys}}');
        if (!isset($surveysTable->columns['savequotaexit'])) {
            $this->db->createCommand()->addColumn(
                '{{surveys}}',
                'savequotaexit',
                "string(1) NOT NULL DEFAULT 'N'"
            );
        }

        // Add to surveys_groupsettings table if not exists
        $surveysGroupsettingsTable = $this->db->schema->getTable('{{surveys_groupsettings}}');
        if (!isset($surveysGroupsettingsTable->columns['savequotaexit'])) {
            $this->db->createCommand()->addColumn(
                '{{surveys_groupsettings}}',
                'savequotaexit',
                "string(1) NOT NULL DEFAULT 'N'"
            );
        }
    }
}
