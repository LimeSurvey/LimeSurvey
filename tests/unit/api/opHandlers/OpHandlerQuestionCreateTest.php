<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\DI;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionCreate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAggregate;
use LimeSurvey\ObjectPatch\{
    Op\OpStandard,
};
use ls\tests\TestBaseClass;
use ls\tests\unit\services\QuestionGroup\QuestionGroupMockSetFactory;

/**
 * @testdox OpHandlerQuestionCreate
 */
class OpHandlerQuestionCreateTest extends TestBaseClass
{
    /**
     * @testdox can handle correct patch
     */
    public function testQuestionCreateCanHandle()
    {
        $op = $this->getOp(
            $this->getCorrectProps()
        );
        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($op));
    }

    /**
     * @testdox can not handle incorrect patch
     */
    public function testQuestionCreateCanNotHandle()
    {
        $op = $this->getOp(
            $this->getCorrectProps(),
            'update'
        );
        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($op));
    }

    /**
     * @testdox validation hits if required values are not provided
     */
    public function testQuestionCreateValidation()
    {
        $op = $this->getOp(
            $this->getIncompleteProps(),
            'create'
        );
        $opHandler = $this->getOpHandler();
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertNotEmpty($validation);
    }

    /**
     * @testdox validation doesn't hit when everything's fine
     */
    public function testQuestionCreateValidationSuccess()
    {
        $op = $this->getOp(
            $this->getCorrectProps(),
        );
        $opHandler = $this->getOpHandler();
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertEmpty($validation);
    }

    /**
     * @param array $props
     * @param string $type
     * @return OpStandard
     * @throws \LimeSurvey\ObjectPatch\OpHandlerException
     */
    private function getOp(array $props, string $type = 'create')
    {
        return OpStandard::factory(
            'question',
            $type,
            0,
            $props,
            [
                'id' => 123456,
            ]
        );
    }

    /**
     * @return array
     */
    private function getCorrectProps()
    {
        return [
            'question'     => [
                'tempId'              => 123456,
                'title'               => 'G01Q01',
                'type'                => '1',
                'question_theme_name' => 'arrays\/dualscale',
                'gid'                 => 50,
                'mandatory'           => false
            ],
            'questionL10n' => [
                'en' => [
                    'question' => 'foo',
                    'help'     => 'bar'
                ]
            ],
            'attributes'   => [
                "public_statistics" => [
                    '' => '1'
                ],
                'dualscale_headerA' => [
                    'en' => 'Header Text',
                    'de' => 'Kopf Text'
                ]
            ],
            'answers'      => [
                '0' => [
                    'tempId' => 456,
                    'code'  => 'AO01',
                    'l10ns' => [
                        'en' => [
                            'answer' => 'answer',
                            'language' => 'en'
                        ],
                        'de' => [
                            'answer' => 'answerger',
                            'language' => 'de'
                        ]
                    ]
                ]
            ],
            'subquestions' => [
                '0' => [
                    'tempId' => 789,
                    'title' => 'SQ001',
                    'l10ns' => [
                        'en' => [
                            'question' => 'sub 1'
                        ],
                        'de' => [
                            'question' => 'subger 1'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array[]
     */
    private function getIncompleteProps()
    {
        return [
            'question' => [
                'title'               => 'G01Q01',
                'type'                => '1',
                'question_theme_name' => 'arrays\/dualscale',
                'gid'                 => '50',
                'mandatory'           => false
            ]
        ];
    }

    /**
     * @return OpHandlerQuestionCreate
     */
    private function getOpHandler()
    {
        $mockSet = (new QuestionGroupMockSetFactory())->make();
        return new OpHandlerQuestionCreate(
            $mockSet->modelQuestion,
            DI::getContainer()->get(
                TransformerInputQuestionAggregate::class
            )
        );
    }
}
