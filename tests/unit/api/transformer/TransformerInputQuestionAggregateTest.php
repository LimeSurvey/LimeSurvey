<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAggregate;
use LimeSurvey\DI;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;

/**
 * @testdox TransformerInputQuestionAggregate
 */
class TransformerInputQuestionAggregateTest extends TestBaseClass
{
    /**
     * @testdox test data structure of advanced settings
     */
    public function testAdvancedSettingsDataStructure()
    {
        $preparedData = $this->transform();
        $this->assertArrayHasKey('advancedSettings', $preparedData);
        $attributes = $preparedData['advancedSettings'];
        $this->assertIsArray($attributes);
        $this->assertArrayHasKey(
            'dualscale_headerA',
            $attributes[0]
        );
        $this->assertIsArray(
            $attributes[0]['dualscale_headerA']
        );
        $this->assertArrayHasKey(
            'de',
            $attributes[0]['dualscale_headerA']
        );
        $this->assertArrayHasKey(
            'public_statistics',
            $attributes[0]
        );
        $this->assertIsNotArray(
            $attributes[0]['public_statistics']
        );
    }

    /**
     * @testdox test data structure of answers
     */
    public function testAnswerDataStructure()
    {
        $preparedData = $this->transform();

        self::assertIsArray($preparedData);
        self::assertArrayHasKey('answeroptions', $preparedData);
        $answers = $preparedData['answeroptions'];
        self::assertArrayHasKey(0, $answers);
        self::assertArrayHasKey(0, $answers[0]);
        self::assertArrayHasKey('code', $answers[0][0]);
        self::assertEquals('AO01', $answers[0][0]['code']);
        self::assertArrayHasKey('answeroptionl10n', $answers[0][0]);
        $answerL10n = $answers[0][0]['answeroptionl10n'];
        self::assertIsArray($answerL10n);
        self::assertArrayHasKey('en', $answerL10n);
        self::assertArrayHasKey('de', $answerL10n);
        self::assertEquals('answer', $answerL10n['en']);
        self::assertEquals('answerger', $answerL10n['de']);
    }
    /**
     * @testdox test data structure of subquestions
     */
    public function testSubQuestionDataStructure()
    {
        $preparedData = $this->transform();
        $this->assertArrayHasKey('subquestions', $preparedData);
        $subQuestions = $preparedData['subquestions'];
        $this->assertIsArray($subQuestions);
        $this->assertIsArray($subQuestions[0]);
        $this->assertArrayHasKey('code', $subQuestions[0][0]);
    }

    /**
     * @return OpStandard
     * @throws \LimeSurvey\ObjectPatch\ObjectPatchException
     */
    private function getOp(): OpStandard
    {
        return OpStandard::factory(
            'question',
            'create',
            0,
            [
                'question'     => [
                    'tempId'              => 'XXX123',
                    'title'               => 'G01Q01',
                    'type'                => '1',
                    'question_theme_name' => 'arrays\/dualscale',
                    'gid'                 => '50',
                    'mandatory'           => null
                ],
                'questionL10n' => [
                    'en' => [
                        'question' => 'foo',
                        'help'     => 'bar'
                    ],
                    'de' => [
                        'question' => 'fooger',
                        'help'     => 'barger'
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
                        'tempId' => 'XXX124',
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
                        'tempId' => 'XXX125',
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
            ],
            [
                'id' => 123456,
            ]
        );
    }

    private function transform()
    {
        $op = $this->getOp();
        $transformer = DI::getContainer()->get(TransformerInputQuestionAggregate::class);

        return $transformer->transform(
            (array)$op->getProps(),
            ['operation' => 'create']
        );
    }
}
