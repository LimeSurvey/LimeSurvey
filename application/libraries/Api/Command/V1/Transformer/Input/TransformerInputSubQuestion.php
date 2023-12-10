<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\{
    Transformer,
    Formatter\FormatterMandatory,
    Formatter\FormatterYnToBool
};

class TransformerInputSubQuestion extends Transformer
{
    public function __construct(TransformerInputQuestion $transformerInputQuestion)
    {
        $dataMap = $transformerInputQuestion->getDataMap();
        unset($dataMap['title']);
        $dataMap['type']['required'] = false;
        $dataMap['code'] = ['required' => 'create'];

        $this->setDataMap($dataMap);
    }
}
