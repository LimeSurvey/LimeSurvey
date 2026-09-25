<?php

namespace LimeSurvey\Helpers\Update;

class Update_715 extends DatabaseUpdateBase
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
            $responseTableName = "{{responses_" . $surveyId . "}}";
            if (!tableExists($responseTableName)) {
                continue;
            }

            try {
                setTransactionBookmark();
                \Yii::app()->db->createCommand()->addColumn(
                    $responseTableName,
                    'quota_exit',
                    'integer'
                );
            } catch (\Exception $e) {
                rollBackToTransactionBookmark();
            }
        }

        // Add to surveys table if not exists
        $surveysTable = $this->db->schema->getTable('{{surveys}}');
        if (!isset($surveysTable->columns['savequotaexit'])) {
            try {
                setTransactionBookmark();
                $this->db->createCommand()->addColumn(
                    '{{surveys}}',
                    'savequotaexit',
                    "string(1) NOT NULL DEFAULT 'N'"
                );
            } catch (\Exception $e) {
                rollBackToTransactionBookmark();
            }
        }

        // Add to surveys_groupsettings table if not exists
        $surveysGroupsettingsTable = $this->db->schema->getTable('{{surveys_groupsettings}}');
        if (!isset($surveysGroupsettingsTable->columns['savequotaexit'])) {
            $this->db->createCommand()->addColumn(
                '{{surveys_groupsettings}}',
                'savequotaexit',
                "string(1) NOT NULL DEFAULT 'N'"
            );
            // Existing survey groups should inherit from the global setting
            $this->db->createCommand()->update('{{surveys_groupsettings}}', ['savequotaexit' => 'I'], 'gsid<>0');
        }
    }
}
