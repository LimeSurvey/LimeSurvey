<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;

class TransformerInputQuestionAttribute extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'qaid' => ['type' => 'int'],
            'qid' => ['type' => 'int'],
            'attribute' => true,
            'value' => true,
            'language' => true
        ]);
    }

    /**
     * Converts the raw array to the expected format.
     */
    public function transformAll($collection, $options = [])
    {
        $preparedSettings = [];
        foreach ($collection as $attrName => $languages) {
            foreach ($languages as $lang => $advancedSetting) {
                $transformedSetting = $this->transform(
                    $advancedSetting,
                    $options
                );
                if (
                    is_array($transformedSetting) && array_key_exists(
                        'value',
                        $transformedSetting
                    )
                ) {
                    $value = $transformedSetting['value'];
                    if ($lang !== '') {
                        $preparedSettings[0][$attrName][$lang] = $value;
                    } else {
                        $preparedSettings[0][$attrName] = $value;
                    }
                } else {
                    throw new OpHandlerException(
                        'Required parameter "value" is missing in entity "questionAttribute"'
                    );
                }
            }
        }
        return $preparedSettings;
    }
}
