<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAggregate;
use LimeSurvey\DI;
use LimeSurvey\ObjectPatch\ObjectPatchException;
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
        self::assertArrayHasKey('temp__123', $answers);
        self::assertArrayHasKey(0, $answers['temp__123']);
        self::assertArrayHasKey('code', $answers['temp__123'][0]);
        self::assertEquals('AO01', $answers['temp__123'][0]['code']);
        self::assertArrayHasKey('answeroptionl10n', $answers['temp__123'][0]);
        $answerL10n = $answers['temp__123'][0]['answeroptionl10n'];
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
        $this->assertIsArray($subQuestions['XXX125']);
        $this->assertArrayHasKey('code', $subQuestions['XXX125'][0]);
    }

    /**
     * @testdox test data structure of question
     */
    public function testQuestionDataStructure()
    {
        $preparedData = $this->transform('update');
        $this->assertArrayHasKey('question', $preparedData);
        $question = $preparedData['question'];
        $this->assertArrayHasKey('qid', $question);
        $this->assertEquals(77, $question['qid']);
    }

    /**
     * @param string $operation
     * @return OpStandard
     * @throws ObjectPatchException
     */
    private function getOp($operation = 'create'): OpStandard
    {
        return OpStandard::factory(
            'question',
            $operation,
            77,
            [
                'question'     => $this->getQuestion($operation),
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
                        '' => '1'
                    ],
                    'dualscale_headerA' => [
                        'en' => 'Header Text',
                        'de' => 'Kopf Text'
                    ]
                ],
                'answers'      => [
                    '0' => $this->getAnswers($operation),
                ],
                'subquestions' => [
                    '0' => $this->getSubQuestions($operation)
                ]
            ],
            [
                'id' => 123456,
            ]
        );
    }

    private function getQuestion($operation = 'create'): array
    {
        $question = [
            'tempId'              => 'XXX123',
            'title'               => 'G01Q01',
            'type'                => '1',
            'question_theme_name' => 'arrays\/dualscale',
            'gid'                 => '50',
            'mandatory'           => null
        ];
        if ($operation === 'update') {
            unset($question['tempId']);
            $question['qid'] = 77;
        }

        return $question;
    }

    private function getAnswers($operation = 'create'): array
    {
        $answer = [
            'tempId' => 'temp__123',
            'aid' => 'temp__123',
            'code'   => 'AO01',
            'l10ns'  => [
                'en' => [
                    'answer'   => 'answer',
                    'language' => 'en'
                ],
                'de' => [
                    'answer'   => 'answerger',
                    'language' => 'de'
                ]
            ]
        ];
        if ($operation === 'update') {
            unset($answer['tempId']);
            $answer['aid'] = 888;
        }

        return $answer;
    }

    private function getSubQuestions($operation = 'create'): array
    {
        $subQuestion = [
            'tempId' => 'XXX125',
            'qid'    => 'XXX125',
            'title'  => 'SQ001',
            'l10ns'  => [
                'en' => [
                    'question' => 'sub 1',
                    'language' => 'en'
                ],
                'de' => [
                    'question' => 'subger 1',
                    'language' => 'de'
                ]
            ]
        ];
        if ($operation === 'update') {
            unset($subQuestion['tempId']);
            $subQuestion['qid'] = 999;
        }
        return $subQuestion;
    }

    private function transform($operation = 'create'): array
    {
        $op = $this->getOp($operation);
        $transformer = DI::getContainer()->get(
            TransformerInputQuestionAggregate::class
        );

        return $transformer->transform(
            (array)$op->getProps(),
            ['operation' => $operation]
        );
    }
}
