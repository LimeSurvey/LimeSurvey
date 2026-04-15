<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputAnswer extends Transformer
{
    public function __construct(
        TransformerInputAnswerL10ns $transformerInputAnswerL10ns
    ) {
        $this->setDataMap([
            'aid' => ['required' => 'update'], // can be a string temp id or an int
            'qid' => ['type' => 'int'],
            'oldCode' => 'oldcode',
            'code' => [
                'required' => 'create',
                'length' => ['min' => 1, 'max' => 5],
                'pattern' => '/^[[:alnum:]]*$/',
            ],
            'sortOrder' => ['key' => 'sortorder', 'type' => 'int', 'numerical'],
            'assessmentValue' => [
                'key' => 'assessment_value',
                'type' => 'int',
                'numerical'
            ],
            'scaleId' => ['key' => 'scale_id', 'type' => 'int', 'numerical'],
            'tempId' => true,
            'l10ns' => [
                'key' => 'answeroptionl10n',
                'collection' => true,
                'transformer' => $transformerInputAnswerL10ns
            ]
        ]);
    }

    public function transformAll($collection, $options = [])
    {
        $collection = parent::transformAll($collection, $options);
        $output = [];
        foreach ($collection as $answer) {
            // second array index needs to be the scaleId
            $scaleId = array_key_exists(
                'scale_id',
                $answer
            ) ? $answer['scale_id'] : 0;
            $index = array_key_exists(
                'aid',
                $answer
            ) ? $answer['aid'] : 'notFound';
            $output[$index][$scaleId] = $answer;
        }
        return $output;
    }
}
