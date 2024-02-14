<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputSurveyLanguageSettings extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'sid'               => [
                'key'      => 'surveyls_survey_id',
                'type'     => 'int',
                'required' => true
            ],
            'language'          => [
                'key'      => 'surveyls_language',
                'type'     => 'string',
                'required' => true
            ],
            'title'             => [
                'key'      => 'surveyls_title',
                'type'     => 'string',
                'required' => 'create'
            ],
            'description'       => 'surveyls_description',
            'welcomeText'       => 'surveyls_welcometext',
            'endText'           => 'surveyls_endtext',
            'policyNotice'      => 'surveyls_policy_notice',
            'surveyAlias'       => 'surveyls_alias',
            'policyError'       => 'surveyls_policy_error',
            'policyNoticeLabel' => 'surveyls_policy_notice_label',
            'url'               => 'surveyls_url',
            'urlDescription'    => 'surveyls_urldescription',
            'dateFormat'        => [
                'key'  => 'surveyls_dateformat',
                'type' => 'int'
            ],
            'numberFormat'      => [
                'key'  => 'surveyls_numberformat',
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
        $props = [];
        $entityId = array_key_exists(
            'entityId',
            $options
        ) ? $options['entityId'] : null;
        if (!empty($entityId)) {
            // indicator for variant 1
            $props[$entityId] = $collection;
        } else {
            // variant 2
            $props = $collection;
        }

        $surveyId = array_key_exists('sid', $options) ? $options['sid'] : null;
        foreach (array_keys($props) as $language) {
            $props[$language]['sid'] = $surveyId;
            $props[$language]['language'] = $language;
        }
        return $props;
    }
}
