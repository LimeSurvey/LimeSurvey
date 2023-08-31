<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionGroupReorder;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionGroup;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionGroupL10ns;
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
     * @testdox getGroupReorderData() is expected to return a certain data structure
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
        //DEBUG
        fwrite(STDERR, print_r($transformedData, true));
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
                    'gid'        => '2',
                    'groupOrder' => '10',
                    'questions'  => [
                        [
                            'qid'           => '4',
                            'gid'           => '2',
                            'questionOrder' => '10'
                        ],
                        [
                            'qid'           => '5',
                            'gid'           => '2',
                            'questionOrder' => '20'
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
            'gid'        => '1',
            'groupOrder' => '10',
            'questions'  => [
                [
                    'qid'           => '2',
                    'gid'           => '1',
                    'questionOrder' => '10'
                ],
                [
                    'qid'           => '3',
                    'gid'           => '1',
                    'questionOrder' => '20'
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
            'gid'       => '1',
            'questions' => [
                [
                    'qid'           => '2',
                    'gid'           => '1',
                    'questionOrder' => '10'
                ],
                [
                    'qid'           => '3',
                    'gid'           => '1',
                    'questionOrder' => '20'
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
            'gid'        => '1',
            'groupOrder' => '10',
            'questions'  => [
                [
                    'gid'           => '1',
                    'questionOrder' => '10'
                ],
                [
                    'qid'           => '3',
                    'gid'           => '1',
                    'questionOrder' => '20'
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
            'x' => '1',
            'y' => '10',
            'z' => [
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
            'questionGroupReorder',
            $mockSet->modelQuestionGroup,
            new TransformerInputQuestionGroup(),
            new TransformerInputQuestionGroupL10ns()
        );
    }
}
