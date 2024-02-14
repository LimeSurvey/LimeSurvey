<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputAnswerL10ns extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'id' => ['type' => 'int'],
            'aid' => ['type' => 'int'],
            'answer' => ['required' => true, 'type' => 'string'],
            'language' => ['required' => true, 'type' => 'string']
        ]);
    }

    public function transformAll($collection, $options = [])
    {
        $collection = parent::transformAll($collection, $options);
        $output = [];
        foreach ($collection as $l10n) {
            $lang = $l10n['language'];
            $output[$lang] =
                (
                    is_array($l10n)
                    && isset($l10n['answer'])
                ) ?
                    $l10n['answer'] : null;
        }
        return $output;
    }
}
