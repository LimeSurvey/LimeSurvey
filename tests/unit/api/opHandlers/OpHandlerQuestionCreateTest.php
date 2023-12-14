<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\DI;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionCreate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAggregate;
use LimeSurvey\ObjectPatch\{
    Op\OpStandard,
    OpHandler\OpHandlerException
};
use ls\tests\TestBaseClass;
use ls\tests\unit\services\QuestionGroup\QuestionGroupMockSetFactory;

/**
 * @testdox OpHandlerQuestionCreate
 */
class OpHandlerQuestionCreateTest extends TestBaseClass
{
    /**
     * @testdox throws OpHandlerException if required entity is missing
     */
    public function testQuestionCreateThrowsMissingEntitiesException()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $op = $this->getOp(
            $this->getIncompleteProps()
        );
        $opHandler = $this->getOpHandler();
        $opHandler->handle($op);
    }

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
     * @param array $props
     * @param string $type
     * @return OpStandard
     * @throws \LimeSurvey\ObjectPatch\ObjectPatchException
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
                'title'               => 'G01Q01',
                'type'                => '1',
                'question_theme_name' => 'arrays\/dualscale',
                'gid'                 => '50',
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
                    '' => [
                        'value' => '1'
                    ]
                ],
                'dualscale_headerA' => [
                    'en' => [
                        'value' => 'Header Text'
                    ],
                    'de' => [
                        'value' => 'Kopf Text'
                    ]
                ]
            ],
            'answers'      => [
                '0' => [
                    'code'  => 'AO01',
                    'l10ns' => [
                        'en' => [
                            'answer' => 'answer'
                        ],
                        'de' => [
                            'answer' => 'answerger'
                        ]
                    ]
                ]
            ],
            'subquestions' => [
                '0' => [
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
