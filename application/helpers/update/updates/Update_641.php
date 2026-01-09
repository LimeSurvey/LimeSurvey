<?php

namespace LimeSurvey\Helpers\Update;

class Update_641 extends DatabaseUpdateBase
{
    /**
     * Update existing response tables to add quota_exit column
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
    }
}
