<?php

namespace LimeSurvey\Helpers\Update;

use LsDefaultDataSets;
use SurveymenuEntries;

class Update_489 extends DatabaseUpdateBase
{
    /**
     * This table is needed to collect failed emails.
     */
    public function up()
    {
        $this->db->createCommand()->createTable(
            '{{failed_emails}}',
            [
                'id' => "pk",
                'surveyid' => "integer NOT NULL",
                'responseid' => "integer NOT NULL",
                'email_type' => "string(200) NOT NULL",
                'recipient' => "string(320) NOT NULL",
                'language' => "string(20) NOT NULL DEFAULT 'en'",
                'error_message'  => "text",
                'created' => "datetime NOT NULL",  //this one has always to be set to delete after x days ...
                'status' => "string(20) NULL DEFAULT 'SEND FAILED'",
                'updated' => "datetime NULL",
                'resend_vars' => "text NOT NULL"
            ]
        );
        $aDefaultSurveyMenuEntries = LsDefaultDataSets::getSurveyMenuEntryData();
        foreach ($aDefaultSurveyMenuEntries as $aSurveymenuentry) {
            if ($aSurveymenuentry['name'] === 'failedemail') {
                $aSurveymenuentry['ordering'] = 5;
                if (SurveymenuEntries::model()->findByAttributes(['name' => $aSurveymenuentry['name']]) === null) {
                    $this->db->createCommand()->insert('{{surveymenu_entries}}', $aSurveymenuentry);
                    SurveymenuEntries::reorderMenu(2);
                }
                break;
            }
        }
    }
}
