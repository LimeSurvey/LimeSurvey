<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputSurveyLanguageSettings extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'sid' => [
                'key' => 'surveyls_survey_id',
                'type' => 'int',
                'required'
            ],
            'language' => [
                'key' => 'surveyls_language',
                'type' => 'string',
                'required'
            ],
            'title' => [
                'key' => 'surveyls_title',
                'type' => 'string',
                'length' => ['min' => 0, 'max' => 200],
                'required' => 'create'
            ],
            'description' => 'surveyls_description',
            'welcomeText' => 'surveyls_welcometext',
            'endText' => 'surveyls_endtext',
            'policyNotice' => 'surveyls_policy_notice',
            'alias' => [
                'key' => 'surveyls_alias',
                'length' => ['min' => 0, 'max' => 100],
                'pattern' => '/^[^\d\W][\w\-]*$/u'
            ],
            'policyError' => 'surveyls_policy_error',
            'policyNoticeLabel' => [
                'key' => 'surveyls_policy_notice_label',
                'length' => ['min' => 0, 'max' => 192]
            ],
            'url' => ['key' => 'surveyls_url', 'filter' => 'trim'],
            'urlDescription' => [
                'key' => 'surveyls_urldescription',
                'length' => ['min' => 0, 'max' => 255]
            ],
            'dateFormat' => [
                'key' => 'surveyls_dateformat',
                'numerical' => ['min' => 1, 'max' => 12],
                'type' => 'int'
            ],
            'numberFormat' => [
                'key' => 'surveyls_numberformat',
                'numerical' => ['min' => 0, 'max' => 1],
                'type' => 'int'
            ],
        ]);
    }

    public function transformAll($collection, $options = [])
    {
        $options = is_array($options) ? $options : [];
        $collection = $this->reorganizeCollection($collection, $options);
        return parent::transformAll(
            $collection,
            $options
        );
    }

    public function validateAll($collection, $options = [])
    {
        $options = is_array($options) ? $options : [];
        $collection = $this->reorganizeCollection($collection, $options);
        return parent::validateAll(
            $collection,
            $options
        );
    }

    /**
     * @param array $collection
     * @param array $options
     * @return array
     */
    private function reorganizeCollection(
        array $collection,
        array $options
    ): array {
        $props = $collection;
        $surveyId = array_key_exists('sid', $options) ? $options['sid'] : null;
        foreach (array_keys($props) as $language) {
            $props[$language]['sid'] = $surveyId;
            $props[$language]['language'] = $language;
        }
        return $props;
    }
}
