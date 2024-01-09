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
            'qid' => ['type' => 'int'],
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
        $transformed = parent::transform($data, $options);

        // if mandatory was passed as null (soft mandatory) in the patch,
        // we have to re-add it as it was removed by parent::transform()
        if (
            array_key_exists('mandatory', $data) &&
            $data['mandatory'] === null
        ) {
            $formatterMandatory = new FormatterMandatory();
            $transformed['mandatory'] = $formatterMandatory->revert(null);
        }
        return $transformed;
    }
}
