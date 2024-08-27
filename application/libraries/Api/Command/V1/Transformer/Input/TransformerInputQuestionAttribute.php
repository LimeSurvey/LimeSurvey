<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputQuestionAttribute extends Transformer
{
    /**
     * Converts the raw array to the expected format.
     */
    public function transformAll($collection, $options = [])
    {
        $attributes = [];
        $collection = $this->convertAttributes($collection);
        foreach ($collection as $attrName => $languages) {
            foreach ($languages as $lang => $value) {
                if ($lang !== '') {
                    $attributes[0][$attrName][$lang] = $value;
                } else {
                    $attributes[0][$attrName] = $value;
                }
            }
        }
        return $attributes;
    }

    private function convertAttributes($collection)
    {
        foreach ($this->converterMap() as $attrName => $convDataArray) {
            if (array_key_exists($attrName, $collection)) {
                foreach ($convDataArray as $convData) {
                    if (array_key_exists($attrName, $collection)) {
                        foreach ($collection[$attrName] as $language => $value) {
                            if (
                                (
                                    is_array($convData['triggerValue'])
                                    && in_array(
                                        $value,
                                        $convData['triggerValue']
                                    )
                                )
                                || $value === $convData['triggerValue']
                            ) {
                                $collection = $this->applyConversion(
                                    $collection,
                                    $language,
                                    $convData
                                );
                            }
                        }
                    }
                }
            }
        }
        return $collection;
    }

    /**
     * Applies the conversion to the collection based on the
     * conversion rules in convData.
     * A conversion to a languagebased attribute is only possible
     * if the triggering attribute is also languagebased
     * @param $collection
     * @param $language
     * @param $convData
     * @return array
     */
    private function applyConversion(
        $collection,
        $language,
        $convData
    ) {
        if ($convData['isLanguageBased']) {
            $collection[$convData['newAttribute']][$language] = $convData['newValue'];
        }else{
            $collection[$convData['newAttribute']][''] = $convData['newValue'];
        }

        return $collection;
    }

    /**
     * array organized by attribute name => array of conversion rules.
     * if attribute has exact same value as 'triggerValue'
     * or its value is one of those in the 'triggerValue'-array,
     * the value of 'newAttribute' is supposed to be set to 'newValue'.
     * @return array[]
     */
    private function converterMap()
    {
        return [
            'statistics_graphtype' => [
                [
                    'triggerValue' => -1,
                    'newAttribute' => 'statistics_showgraph',
                    'isLanguageBased' => false,
                    'newValue' => 0
                ],
                [
                    'triggerValue' => [0, 1, 2, 3, 4, 5],
                    'newAttribute' => 'statistics_showgraph',
                    'isLanguageBased' => false,
                    'newValue' => 1
                ]
            ],
        ];
    }
}
