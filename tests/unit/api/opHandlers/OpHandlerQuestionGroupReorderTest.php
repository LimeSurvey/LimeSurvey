<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionGroupReorder;
use LimeSurvey\Api\Command\V1\Transformer\{Input\TransformerInputQuestion,
    Input\TransformerInputQuestionGroupReorder};
use LimeSurvey\DI;
use LimeSurvey\ObjectPatch\{
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
     * @testdox validation doesn't hit when everything's fine
     */
    public function testValidationSuccess() {
        $op = $this->getOp(
            $this->getStandardGroupParamsArray()
        );
        $opHandler = $this->getOpHandler();
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertEmpty($validation);
    }

    /**
     * @testdox validation hits
     */
    public function testValidationFailure() {
        $op1 = $this->getOp(
            $this->getFullWrongParamsArray()
        );
        $op2 = $this->getOp(
            $this->getMissingRequiredGroupParamsArray()
        );
        $op3 = $this->getOp(
            $this->getMissingRequiredQuestionParamsArray()
        );
        $opHandler = $this->getOpHandler();
        $validation = $opHandler->validateOperation($op1);
        $this->assertIsArray($validation);
        $this->assertNotEmpty($validation);
        $validation = $opHandler->validateOperation($op2);
        $this->assertIsArray($validation);
        $this->assertNotEmpty($validation);
        $validation = $opHandler->validateOperation($op3);
        $this->assertIsArray($validation);
        $this->assertNotEmpty($validation);
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
            DI::getContainer()->get(TransformerInputQuestionGroupReorder::class)
        );
    }
}
