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
            'required' => 'update'
        ];
        $dataMap['questions'] = [
            'transformer' => $transformerQuestion,
            'required'    => false,
            'collection'  => true,
        ];
        $dataMap['questions']['sortOrder'] = [
            'key'      => 'question_order',
            'type'     => 'int',
            'required' => 'update'
        ];

        $this->setDataMap($dataMap);
    }

    public function transformAll($collection, $options = [])
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
        $asd = $collection;
        return parent::transformAll($collection, $options);
    }
}
