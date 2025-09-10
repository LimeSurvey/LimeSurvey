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
        $dataMap['gid'] = ['type' => 'int'];
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
        foreach ($data as $subQuestion) {
            $qid = $this->getQidFromData($subQuestion);
            $scaleId = $this->getScaleIdFromData($subQuestion);
            $preparedSubQuestions[$qid][$scaleId] = $subQuestion;
        }
        return $preparedSubQuestions;
    }

    /**
     * @param array $questionData
     * @return mixed
     */
    private function getQidFromData(array $questionData)
    {
        return array_key_exists(
            'qid',
            $questionData
        ) ? $questionData['qid'] : 'notFound';
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
