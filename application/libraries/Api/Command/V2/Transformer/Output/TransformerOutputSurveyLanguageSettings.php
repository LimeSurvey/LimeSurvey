<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputSurveyLanguageSettings extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $this->setDataMap([
            'surveyls_survey_id' => ['key' => 'sid', 'type' => 'int'],
            'surveyls_language'  => 'language',
            'surveyls_title' => 'title',
            'surveyls_description' => 'description',
            'surveyls_welcometext' => 'welcometext',
            'surveyls_endtext' => 'endtext',
            'surveyls_policy_notice' => 'policy_notice',
            'surveyls_policy_error' => 'policy_error',
            'surveyls_policy_notice_label' => 'policy_notice_label',
            'surveyls_url' => 'url',
            'surveyls_urldescription' => 'urldescription',
            'surveyls_dateformat' => ['key' => 'dateformat', 'type' => 'int'],
            'surveyls_numberformat' => ['key' => 'numberformat', 'type' => 'int'],
        ]);
    }
}
