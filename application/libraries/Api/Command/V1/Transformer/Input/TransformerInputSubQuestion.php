<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputSubQuestion extends Transformer
{
    public function __construct(
        TransformerInputQuestion $transformerInputQuestion,
        TransformerInputSubQuestionL10ns $transformerInputSubquestionL10n
    ) {
        $dataMap = $transformerInputQuestion->getDataMap();
        unset($dataMap['title']);
        $dataMap['type']['required'] = false;
        $dataMap['title'] = ['key' => 'code', 'required' => 'create'];
        $dataMap['qid'] = ['required' => 'update'];
        $dataMap['tempId'] = true;
        $dataMap['l10ns'] = [
            'key' => 'subquestionl10n',
            'collection' => true,
            'required',
            'transformer' => $transformerInputSubquestionL10n
        ];
        $this->setDataMap($dataMap);
    }

    public function transformAll($collection, $options = [])
    {
        return $this->prepareSubQuestions(
            parent::transformAll($collection, $options)
        );
    }

    /**
     * Converts the subquestions from the raw data to the expected format.
     *
     * @param array $data
     * @return array
     */
    private function prepareSubQuestions($data)
    {
        $preparedSubQuestions = [];
        foreach ($data as $index => $subQuestion) {
            $qid = $this->getQidFromData($index, $subQuestion);
            $scaleId = $this->getScaleIdFromData($subQuestion);
            $preparedSubQuestions[$qid][$scaleId] = $subQuestion;
        }
        return $preparedSubQuestions;
    }

    /**
     * @param int $index
     * @param array $questionData
     * @return int
     */
    private function getQidFromData(int $index, array $questionData)
    {
        return array_key_exists(
            'qid',
            $questionData
        ) && (int)$questionData['qid'] > 0 ? (int)$questionData['qid'] : $index;
    }

    /**
     * @param array $questionData
     * @return int
     */
    private function getScaleIdFromData(array $questionData)
    {
        return array_key_exists(
            'scale_id',
            $questionData
        ) ? (int)$questionData['scale_id'] : 0;
    }
}
