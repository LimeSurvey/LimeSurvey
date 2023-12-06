<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputSurveyLanguageSettings extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'sid' => ['key' => 'surveyls_survey_id', 'type' => 'int'],
            'language'  => ['key' => 'surveyls_language', 'type' => 'string'],
            'title' => ['key' => 'surveyls_title', 'type' =>'string', 'required' => true],
            'description' => 'surveyls_description',
            'welcomeText' => 'surveyls_welcometext',
            'endText' => 'surveyls_endtext',
            'policyNotice' => 'surveyls_policy_notice',
            'policyError' => 'surveyls_policy_error',
            'policyNoticeLabel' => 'surveyls_policy_notice_label',
            'url' => 'surveyls_url',
            'urlDescription' => 'surveyls_urldescription',
            'dateFormat' => ['key' => 'surveyls_dateformat', 'type' => 'int'],
            'numberFormat' => ['key' => 'surveyls_numberformat', 'type' => 'int'],
        ]);
    }
}
