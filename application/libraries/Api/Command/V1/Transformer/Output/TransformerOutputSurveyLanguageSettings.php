<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

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
            'surveyls_welcometext' => 'welcomeText',
            'surveyls_endtext' => 'endText',
            'surveyls_policy_notice' => 'policyNotice',
            'surveyls_policy_error' => 'policyError',
            'surveyls_alias' => 'surveyAlias',
            'surveyls_policy_notice_label' => 'policyNoticeLabel',
            'surveyls_url' => 'url',
            'surveyls_urldescription' => 'urlDescription',
            'surveyls_dateformat' => ['key' => 'dateFormat', 'type' => 'int'],
            'surveyls_numberformat' => ['key' => 'numberFormat', 'type' => 'int'],
        ]);
    }
}
