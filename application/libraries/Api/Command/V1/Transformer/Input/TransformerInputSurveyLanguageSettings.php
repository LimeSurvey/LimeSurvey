<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputSurveyLanguageSettings extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'sid' => ['key' => 'surveyls_survey_id', 'type' => 'int', 'required' => true],
            'language'  => ['key' => 'surveyls_language', 'type' => 'string', 'required' => true],
            'title' => ['key' => 'surveyls_title', 'type' => 'string', 'required' => 'create'],
            'description' => 'surveyls_description',
            'welcomeText' => 'surveyls_welcometext',
            'endText' => 'surveyls_endtext',
            'policyNotice' => 'surveyls_policy_notice',
            'surveyAlias' => 'surveyls_alias',
            'policyError' => 'surveyls_policy_error',
            'policyNoticeLabel' => 'surveyls_policy_notice_label',
            'url' => 'surveyls_url',
            'urlDescription' => 'surveyls_urldescription',
            'dateFormat' => ['key' => 'surveyls_dateformat', 'type' => 'int'],
            'numberFormat' => ['key' => 'surveyls_numberformat', 'type' => 'int'],
        ]);
    }

    public function transformAll($collection, $options = [])
    {
        $collection = $this->reorganizeCollection($collection, $options);
        return parent::transformAll(
            $collection,
            $options
        );
    }

    public function validateAll($collection, $options = [])
    {
        $collection = $this->reorganizeCollection($collection, $options);
        return parent::validateAll(
            $collection,
            $options
        );
    }

    /**
     * @param $collection
     * @param $options
     * @return array
     */
    private function reorganizeCollection($collection, $options): array
    {
        $props = [];
        $entityId = $options['entityId'];
        if (!empty($entityId)) {
            // indicator for variant 1
            $props[$entityId] = $collection;
        } else {
            // variant 2
            $props = $collection;
        }
        if (is_array($props[array_key_first($props)])) {
            foreach (array_keys($props) as $language) {
                $props[$language]['sid'] = $options['sid'];
                $props[$language]['language'] = $language;
            }
        }
        return $props;
    }
}
