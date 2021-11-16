<?php

namespace LimeSurvey\Helpers\Update;

class Update_156 extends DatabaseUpdateBase
{
    public function run()
    {
        try {
            $oDB->createCommand()->dropTable('{{survey_url_parameters}}');
        } catch (Exception $e) {
            // do nothing
        }
            $oDB->createCommand()->createTable(
                '{{survey_url_parameters}}',
                array(
                    'id' => 'pk',
                    'sid' => 'integer NOT NULL',
                    'parameter' => 'string(50) NOT NULL',
                    'targetqid' => 'integer',
                    'targetsqid' => 'integer'
                )
            );

        try {
            $oDB->createCommand()->dropTable('{{sessions}}');
        } catch (Exception $e) {
            // do nothing
        }
        if (Yii::app()->db->driverName == 'mysql') {
            $oDB->createCommand()->createTable(
                '{{sessions}}',
                array(
                    'id' => 'string(32) NOT NULL',
                    'expire' => 'integer',
                    'data' => 'longtext'
                )
            );
        } else {
            $oDB->createCommand()->createTable(
                '{{sessions}}',
                array(
                    'id' => 'string(32) NOT NULL',
                    'expire' => 'integer',
                    'data' => 'text'
                )
            );
        }

            addPrimaryKey('sessions', array('id'));
            addColumn('{{surveys_languagesettings}}', 'surveyls_attributecaptions', "text");
            addColumn('{{surveys}}', 'sendconfirmation', "string(1) default 'Y'");

            upgradeSurveys156();

            // If a survey has an deleted owner, re-own the survey to the superadmin
            $sSurveyQuery = "SELECT sid, uid  from {{surveys}} LEFT JOIN {{users}} ON uid=owner_id WHERE uid IS null";
            $oSurveyResult = $oDB->createCommand($sSurveyQuery)->queryAll();
        foreach ($oSurveyResult as $row) {
            $oDB->createCommand("UPDATE {{surveys}} SET owner_id=1 WHERE sid={$row['sid']}")->execute();
        }
    }
}
