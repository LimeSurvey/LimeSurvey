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

    /**
     * @param array $collection
     * @return array
     *
     */
    private function convertAttributes(array $collection)
    {
        foreach ($this->converterMap() as $attrName => $convDataArray) {
            if (array_key_exists($attrName, $collection)) {
                foreach ($convDataArray as $convData) {
                    foreach ($collection[$attrName] as $language => $value) {
                        if (
                            (
                                is_array($convData['matchValue'])
                                && in_array(
                                    $value,
                                    $convData['matchValue']
                                )
                            )
                            || $value === $convData['matchValue']
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
        return $collection;
    }

    /**
     * Applies the conversion to the collection based on the
     * conversion rules in convData.
     * A conversion to a languagebased attribute is only possible
     * if the triggering attribute is also languagebased
     * @param array $collection
     * @param string $language
     * @param array $convData
     * @return array
     */
    private function applyConversion(
        array $collection,
        string $language,
        array $convData
    ) {
        if ($convData['isLanguageBased']) {
            $collection[$convData['targetAttribute']][$language] = $convData['newValue'];
        } else {
            $collection[$convData['targetAttribute']][''] = $convData['newValue'];
        }

        return $collection;
    }

    /**
     * array organized by attribute name => array of conversion rules.
     * if attribute has exact same value as 'matchValue'
     * or its value is one of those in the 'matchValue'-array,
     * the value of 'targetAttribute' is supposed to be set to 'newValue'.
     * @return array[]
     */
    private function converterMap()
    {
        return [
            'statistics_graphtype' => [
                [
                    'matchValue' => '-1',
                    'targetAttribute' => 'statistics_showgraph',
                    'isLanguageBased' => false,
                    'newValue' => '0'
                ],
                [
                    'matchValue' => ['0', '1', '2', '3', '4', '5'],
                    'targetAttribute' => 'statistics_showgraph',
                    'isLanguageBased' => false,
                    'newValue' => '1'
                ]
            ],
        ];
    }
}
