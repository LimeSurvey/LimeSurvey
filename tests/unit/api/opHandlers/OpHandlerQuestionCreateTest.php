<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionCreate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputAnswer;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputAnswerL10ns;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestion;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAggregate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAttribute;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionL10ns;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use ls\tests\TestBaseClass;
use ls\tests\unit\services\QuestionGroup\QuestionGroupMockSetFactory;

/**
 * @testdox OpHandlerQuestionCreate
 */
class OpHandlerQuestionCreateTest extends TestBaseClass
{
    protected OpInterface $op;

    /**
     * @testdox throws OpHandlerException if required entity is missing
     */
    public function testQuestionCreateThrowsMissingEntitiesException()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $this->initializePatcher(
            $this->getIncompleteProps()
        );
        $opHandler = $this->getOpHandler();

        $opHandler->handle($this->op);
    }

    /**
     * @testdox can handle correct patch
     */
    public function testQuestionCreateCanHandle()
    {
        $this->initializePatcher(
            $this->getCorrectProps()
        );

        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($this->op));
    }

    /**
     * @testdox can not handle incorrect patch
     */
    public function testQuestionCreateCanNotHandle()
    {
        $this->initializePatcher(
            $this->getCorrectProps(),
            'update'
        );

        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($this->op));
    }

    /**
     * @testdox converts "advanced settings" to correct structure
     */
    public function testQuestionCreateAttributesStructure()
    {
        $preparedData = $this->getPreparedData();
        $this->assertArrayHasKey('advancedSettings', $preparedData);
        $this->assertIsArray($preparedData['advancedSettings']);
        $this->assertArrayHasKey(
            'dualscale_headerA',
            $preparedData['advancedSettings'][0]
        );
        $this->assertIsArray(
            $preparedData['advancedSettings'][0]['dualscale_headerA']
        );
        $this->assertArrayHasKey(
            'de',
            $preparedData['advancedSettings'][0]['dualscale_headerA']
        );
        $this->assertArrayHasKey(
            'public_statistics',
            $preparedData['advancedSettings'][0]
        );
        $this->assertIsNotArray(
            $preparedData['advancedSettings'][0]['public_statistics']
        );
    }

    /**
     * @testdox converts "answers" to correct structure
     */
    public function testQuestionCreateAnswersStructure()
    {
        $preparedData = $this->getPreparedData();
        $this->assertArrayHasKey('answeroptions', $preparedData);
        $this->assertIsArray($preparedData['answeroptions']);
        $this->assertIsArray($preparedData['answeroptions'][0]);
        $this->assertArrayHasKey('code', $preparedData['answeroptions'][0][0]);
//        @TODO index
    }

    /**
     * @testdox converts "subquestions" to correct structure
     */
    public function testQuestionCreateSuQuestionsStructure()
    {
        $preparedData = $this->getPreparedData();
        $this->assertArrayHasKey('subquestions', $preparedData);
        $this->assertIsArray($preparedData['subquestions']);
        $this->assertIsArray($preparedData['subquestions'][0]);
        $this->assertArrayHasKey('code', $preparedData['subquestions'][0][0]);
    }

    private function getPreparedData()
    {
        $this->initializePatcher(
            $this->getCorrectProps(),
            'update'
        );

        $opHandler = $this->getOpHandler();
        return $opHandler->prepareData($this->op);
    }

    /**
     * @param array $props
     * @param string $type
     * @return void
     * @throws \LimeSurvey\ObjectPatch\ObjectPatchException
     */
    private function initializePatcher(array $props, string $type = 'create')
    {
        $this->op = OpStandard::factory(
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
            new TransformerInputQuestion(),
            new TransformerInputQuestionL10ns(),
            new TransformerInputQuestionAttribute(),
            new TransformerInputAnswer(),
            new TransformerInputAnswerL10ns(),
            new TransformerInputQuestionAggregate()
        );
    }
}
