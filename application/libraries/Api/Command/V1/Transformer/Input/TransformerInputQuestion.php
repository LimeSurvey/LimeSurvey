<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\{
    Transformer,
    TransformerException};

class TransformerInputQuestion extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'qid' => ['type' => 'int'],
            'parentQid' => ['key' => 'parent_qid', 'type' => 'int'],
            'sid' => ['type' => 'int'],
            'type' => [
                'required' => 'create',
                'length' => ['min' => 1, 'max' => 1]
            ],
            'title' => [
                'required' => 'create',
                'length' => ['min' => 1, 'max' => 20]
            ],
            'preg' => true,
            'other' => [
                'formatter' => ['ynToBool' => ['revert' => true]],
                'range' => [true, false]
            ],
            'mandatory' => ['formatter' => ['mandatory' => true]],
            'encrypted' => [
                'formatter' => ['ynToBool' => ['revert' => true]],
                'range' => [true, false]
            ],
            'questionOrder' => [
                'key' => 'question_order',
                'type' => 'int',
                'numerical'
            ],
            'sortOrder' => [
                'key' => 'question_order',
                'type' => 'int',
                'numerical'
            ],
            'scaleId' => ['key' => 'scale_id', 'type' => 'int', 'numerical'],
            'sameDefault' => [
                'key' => 'same_default',
                'formatter' => ['intToBool' => ['revert' => true]]
            ],
            'questionThemeName' => 'question_theme_name',
            'saveAsDefault' => [
                'key' => 'save_as_default',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'clearDefault' => [
                'key' => 'clear_default',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'moduleName' => ['key' => 'modulename', 'length' => ['max' => 255]],
            'gid' => ['type' => 'int'],
            'relevance' => ['filter' => 'trim'],
            'sameScript' => [
                'key' => 'same_script',
                'formatter' => ['intToBool' => ['revert' => true]]
            ],
            'tempId' => ['required' => 'create']
        ]);
    }

    public function transform($data, $options = [])
    {
        $options = is_array($options) ? $options : [];
        if (empty($data)) {
            throw new TransformerException('Data can not be empty');
        }
        $props = parent::transform($data, $options);
        // Set qid from op entity id
        if (
            is_array($props)
            && (
                !array_key_exists(
                    'qid',
                    $props
                )
                || $props['qid'] === null
            )
        ) {
            $props['qid'] = array_key_exists(
                'id',
                $options
            ) ? $options['id'] : null;
        }

        return $props;
    }
}
