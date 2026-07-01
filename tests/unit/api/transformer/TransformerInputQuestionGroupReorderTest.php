<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionGroupReorder;
use LimeSurvey\DI;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;

/**
 * @testdox TransformerInputQuestionGroupReorder
 */
class TransformerInputQuestionGroupReorderTest extends TestBaseClass
{
    /**
     * @testdox transformAll() returns expected structure
     */
    public function testOpQuestionGroupReorderDataStructure()
    {
        $op = $this->getOp(
            $this->getStandardGroupParamsArray()
        );

        $transformer = DI::getContainer()->get(
            TransformerInputQuestionGroupReorder::class
        );
        $transformedData = $transformer->transformAll(
            (array)$op->getProps(),
            ['operation' => $op->getType()->getId()]
        );

        $this->assertArrayHasKey('gid', $transformedData['1']);
        $this->assertArrayHasKey('group_order', $transformedData['1']);

        $this->assertArrayHasKey(
            'qid',
            $transformedData['1']['questions']['2']
        );
        $this->assertArrayHasKey(
            'gid',
            $transformedData['1']['questions']['2']
        );
        $this->assertArrayHasKey(
            'question_order',
            $transformedData['1']['questions']['2']
        );
    }

    /**
     * @param array $groupParams
     * @return OpStandard
     * @throws \LimeSurvey\ObjectPatch\OpHandlerException
     */
    private function getOp(array $groupParams)
    {
        return OpStandard::factory(
            'questionGroupReorder',
            'update',
            "123456",
            [
                '1' => $groupParams,
                '2' => [
                    'sortOrder' => '10',
                    'questions' => [
                        '4' => [
                            'sortOrder' => '10'
                        ],
                        '5' => [
                            'sortOrder' => '20'
                        ]
                    ]
                ]
            ],
            [
                'id' => 123456
            ]
        );
    }

    /**
     * @return array
     */
    private function getStandardGroupParamsArray()
    {
        return [
            'sortOrder' => '10',
            'questions' => [
                '2' => [
                    'sortOrder' => '10',
                    'tempId'    => '1'
                ],
                '3' => [
                    'sortOrder' => '20',
                    'tempId'    => '2'
                ]
            ]
        ];
    }
}
