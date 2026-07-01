<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputQuestionGroupReorder extends Transformer
{
    public function __construct(
        TransformerInputQuestionGroup $transformerGroup,
        TransformerInputQuestion $transformerQuestion
    ) {
        $dataMap = $transformerGroup->getDataMap();
        $dataMap['sortOrder'] = [
            'key'      => 'group_order',
            'type'     => 'int',
            'numerical',
            'required' => 'update'
        ];
        $dataMap['gid'] = ['type' => 'int', 'required' => 'update'];

        $dataMapQuestions = $transformerQuestion->getDataMap();
        $dataMapQuestions['sortOrder'] = [
            'key'      => 'question_order',
            'type'     => 'int',
            'numerical',
            'required' => 'update'
        ];
        $dataMapQuestions['gid'] = [
            'type'     => 'int',
            'required' => 'update'
        ];
        $dataMapQuestions['qid'] = [
            'type'     => 'int',
            'required' => 'update'
        ];
        $tfQuestionClone = clone $transformerQuestion;
        $tfQuestionClone->setDataMap($dataMapQuestions);
        $dataMap['questions'] = [
            'transformer' => $tfQuestionClone,
            'required'    => false,
            'collection'  => true,
        ];

        $this->setDataMap($dataMap);
    }

    public function transformAll($collection, $options = [])
    {
        return parent::transformAll(
            $this->enhanceCollectionWithIds($collection),
            $options
        );
    }

    public function validateAll($collection, $options = [])
    {
        return parent::validateAll(
            $this->enhanceCollectionWithIds($collection),
            $options
        );
    }

    /**
     * Takes the indexes from the collection
     * and adds them as ids to the collection.
     * @param array $collection
     * @return array
     */
    private function enhanceCollectionWithIds(array $collection): array
    {
        foreach ($collection as $gid => $groupData) {
            if (is_numeric($gid) && $gid > 0) {
                $collection[$gid]['gid'] = $gid;
            }
            if (array_key_exists('questions', $groupData)) {
                foreach ($groupData['questions'] as $qid => $questionData) {
                    $collection[$gid]['questions'][$qid]['gid'] = $gid;
                    $collection[$gid]['questions'][$qid]['qid'] = $qid;
                }
            }
        }
        return $collection;
    }
}
