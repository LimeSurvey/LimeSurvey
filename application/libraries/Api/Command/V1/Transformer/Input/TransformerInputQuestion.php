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
            'qid'               => ['type' => 'int'],
            'parentQid'         => ['key' => 'parent_qid', 'type' => 'int'],
            'sid'               => ['type' => 'int'],
            'type'              => ['required' => 'create'],
            'title'             => ['required' => 'create'],
            'preg'              => true,
            'other'             => ['formatter' => ['ynToBool' => ['revert' => true]]],
            'mandatory'         => ['formatter' => ['mandatory' => true]],
            'encrypted'         => ['formatter' => ['ynToBool' => ['revert' => true]]],
            'questionOrder'     => ['key' => 'question_order', 'type' => 'int'],
            'sortOrder'         => ['key' => 'question_order', 'type' => 'int'],
            'scaleId'           => ['key' => 'scale_id', 'type' => 'int'],
            'sameDefault'       => [
                'key'       => 'same_default',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'questionThemeName' => 'question_theme_name',
            'saveAsDefault'     => 'save_as_default',
            'clearDefault'      => 'clear_default',
            'moduleName'        => 'modulename',
            'gid'               => ['type' => 'int'],
            'relevance'         => true,
            'sameScript'        => [
                'key'       => 'same_script',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'tempId'            => ['required' => 'create']
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
