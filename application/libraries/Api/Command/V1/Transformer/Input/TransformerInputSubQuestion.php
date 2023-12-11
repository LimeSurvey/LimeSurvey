<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputSubQuestion extends Transformer
{
    public function __construct(TransformerInputQuestion $transformerInputQuestion)
    {
        $dataMap = $transformerInputQuestion->getDataMap();
        unset($dataMap['title']);
        $dataMap['type']['required'] = false;
        $dataMap['code'] = ['required' => 'create'];
        $dataMap['l10ns'] = 'subquestionl10n';

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
     * @param OpInterface $op
     * @param TransformerInputQuestion $transformerQuestion
     * @param TransformerInputQuestionL10ns $transformerL10n
     * @param array|null $data
     * @param array|null $additionalRequiredEntities
     * @return array
     * @throws OpHandlerException
     */
    private function prepareSubQuestions(
        array $data
    ): array {
        $preparedSubQuestions = [];
        if (is_array($data)) {
            foreach ($data as $index => $subQuestion) {
                $qid = $this->getQidFromData($index, $subQuestion);
                $scaleId = $this->getScaleIdFromData($subQuestion);
                $preparedSubQuestions[$qid][$scaleId] = $subQuestion;
            }
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
        ) ? (int)$questionData['qid'] : $index;
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
