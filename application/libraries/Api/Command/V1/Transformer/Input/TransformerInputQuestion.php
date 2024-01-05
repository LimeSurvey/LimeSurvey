<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\{
    Transformer,
    TransformerException,
    Formatter\FormatterMandatory,
    Formatter\FormatterYnToBool
};

class TransformerInputQuestion extends Transformer
{
    public function __construct()
    {
        $formatterYn = new FormatterYnToBool(true);
        $formatterMandatory = new FormatterMandatory();

        $this->setDataMap([
            'qid' => ['type' => 'int', 'required' => 'update'],
            'parentQid' => ['key' => 'parent_qid', 'type' => 'int'],
            'sid' => ['type' => 'int', 'required' => 'create'],
            'type' => ['required' => 'create'],
            'title' => ['required' => 'create'],
            'preg' => true,
            'other' => ['formatter' => $formatterYn],
            'mandatory' => ['formatter' => $formatterMandatory],
            'encrypted' => ['formatter' => $formatterYn],
            'questionOrder' => ['key' => 'question_order', 'type' => 'int'],
            'sortOrder' => ['key' => 'question_order', 'type' => 'int'],
            'scaleId' => ['key' => 'scale_id', 'type' => 'int'],
            'sameDefault' => ['key' => 'same_default', 'formatter' => $formatterYn],
            'questionThemeName' => 'question_theme_name',
            'saveAsDefault' => 'save_as_default',
            'clearDefault' => 'clear_default',
            'moduleName' => 'modulename',
            'gid' => ['type' => 'int'],
            'relevance' => true,
            'sameScript' => ['key' => 'same_script', 'formatter' => $formatterYn],
            'tempId' => ['required' => 'create']
        ]);
    }

    public function transform($data, $options = [])
    {
        if (empty($data)) {
            throw new TransformerException('Data can not be empty');
        }
        return parent::transform($data, $options);
    }
}
