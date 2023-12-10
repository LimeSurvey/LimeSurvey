<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionGroupReorder;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestion;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionGroup;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use ls\tests\unit\services\QuestionGroup\QuestionGroupMockSetFactory;

/**
 * @testdox Op Handler Question Group Reorder
 */
class OpHandlerQuestionGroupReorderTest extends TestBaseClass
{
    protected OpInterface $op;

    /**
     * @testdox throws exception when no valid values are provided
     */
    public function testOpQuestionGroupReorderThrowsNoValuesException()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $this->initializePatcher(
            $this->getFullWrongParamsArray()
        );
        $opHandler = $this->getOpHandler();
        $opHandler->getGroupReorderData($this->op);
    }

    /**
     * @testdox throws exception when group parameters are missing
     */
    public function testOpQuestionGroupReorderThrowsMissingGroupValException()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $this->initializePatcher(
            $this->getMissingRequiredGroupParamsArray()
        );
        $opHandler = $this->getOpHandler();
        $opHandler->getGroupReorderData($this->op);
    }

    /**
     * @testdox throws exception when question parameters are missing
     */
    public function testOpQuestionGroupReorderThrowsMissingQuestionValException()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $this->initializePatcher(
            $this->getMissingRequiredQuestionParamsArray()
        );
        $opHandler = $this->getOpHandler();
        $opHandler->getGroupReorderData($this->op);
    }

    /**
     * @testdox getGroupReorderData() returns expected structure
     */
    public function testOpQuestionGroupReorderDataStructure()
    {
        $this->initializePatcher(
            $this->getStandardGroupParamsArray()
        );
        $opHandler = $this->getOpHandler();
        $transformedData = $opHandler->getGroupReorderData($this->op);
        $this->assertArrayHasKey('gid', $transformedData['1']);
        $this->assertArrayHasKey('group_order', $transformedData['1']);
        $this->assertArrayHasKey(
            'qid',
            $transformedData['1']['questions'][0]
        );
        $this->assertArrayHasKey(
            'gid',
            $transformedData['1']['questions'][0]
        );
        $this->assertArrayHasKey(
            'question_order',
            $transformedData['1']['questions'][0]
        );
    }

    /**
     * @param array $groupParams
     * @return void
     * @throws \LimeSurvey\ObjectPatch\ObjectPatchException
     */
    private function initializePatcher(array $groupParams)
    {
        $this->op = OpStandard::factory(
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
            new TransformerInputQuestionGroup(),
            new TransformerInputQuestion()
        );
    }
}
