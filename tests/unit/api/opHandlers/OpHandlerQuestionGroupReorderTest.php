<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionGroupReorder;
use LimeSurvey\Api\Command\V1\Transformer\{Input\TransformerInputQuestion,
    Input\TransformerInputQuestionGroup,
    Input\TransformerInputQuestionGroupReorder};
use LimeSurvey\ObjectPatch\{
    OpHandler\OpHandlerException,
    Op\OpStandard
};
use ls\tests\TestBaseClass;
use ls\tests\unit\services\QuestionGroup\QuestionGroupMockSetFactory;

/**
 * @testdox Op Handler Question Group Reorder
 */
class OpHandlerQuestionGroupReorderTest extends TestBaseClass
{
    /**
     * @testdox transformAll() returns expected structure
     */
    public function testOpQuestionGroupReorderDataStructure()
    {
        $op = $this->getOp(
            $this->getStandardGroupParamsArray()
        );

        $transformer = new TransformerInputQuestionGroupReorder(
            new TransformerInputQuestionGroup(),
            new TransformerInputQuestion()
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
                    'questions'  => [
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
            'questions'  => [
                '2' => [
                    'sortOrder' => '10',
                    'tempId' => '1'
                ],
                '3' => [
                    'sortOrder' => '20',
                    'tempId' => '2'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getMissingRequiredGroupParamsArray()
    {
        return [
            'questions' => [
                '2' => [
                    'sortOrder' => '10'
                ],
                '3' => [
                    'sortOrder' => '20'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getMissingRequiredQuestionParamsArray()
    {
        return [
            'sortOrder' => '10',
            'questions'  => [
                '2' => [
                    'foo' => '10'
                ],
                '3' => [
                    'sortOrder' => '20'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getFullWrongParamsArray()
    {
        return [
            'sortOrder' => '1',
            'questions' => [
                [
                    'a' => '2',
                    'b' => '1',
                    'c' => '20'
                ],
                [
                    'a' => '3',
                    'b' => '1',
                    'c' => '20'
                ]
            ]
        ];
    }

    /**
     * @return OpHandlerQuestionGroupReorder
     */
    private function getOpHandler()
    {
        $mockSet = (new QuestionGroupMockSetFactory())->make();
        return new OpHandlerQuestionGroupReorder(
            $mockSet->modelQuestionGroup,
            new TransformerInputQuestionGroupReorder(
                new TransformerInputQuestionGroup(),
                new TransformerInputQuestion()
            )
        );
    }
}
