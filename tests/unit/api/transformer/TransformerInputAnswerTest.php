<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputAnswer;
use LimeSurvey\DI;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;

/**
 * @testdox TransformerInputAnswer
 */
class TransformerInputAnswerTest extends TestBaseClass
{
    /**
     * @testdox test data structure
     */
    public function testAnswerDataStructure()
    {
        $op = $this->getOp();

        $transformer = DI::getContainer()->get(TransformerInputAnswer::class);
        $preparedData = $transformer->transformAll(
            (array)$op->getProps(),
            ['operation' => 'create']
        );

        self::assertIsArray($preparedData);
        self::assertArrayHasKey(0, $preparedData);
        self::assertArrayHasKey(1, $preparedData);
        self::assertArrayHasKey(0, $preparedData[0]);
        self::assertArrayHasKey(1, $preparedData[1]);
        self::assertArrayHasKey('code', $preparedData[0][0]);
        self::assertEquals('AO01', $preparedData[0][0]['code']);
        self::assertArrayHasKey('answeroptionl10n', $preparedData[0][0]);
        self::assertIsArray($preparedData[0][0]['answeroptionl10n']);
        self::assertArrayHasKey('en', $preparedData[0][0]['answeroptionl10n']);
        self::assertArrayHasKey('de', $preparedData[0][0]['answeroptionl10n']);
        self::assertEquals(
            'answer',
            $preparedData[0][0]['answeroptionl10n']['en']
        );
        self::assertEquals(
            'answerger',
            $preparedData[0][0]['answeroptionl10n']['de']
        );
    }

    /**
     * @return OpStandard
     * @throws \LimeSurvey\ObjectPatch\ObjectPatchException
     */
    private function getOp(): OpStandard
    {
        return OpStandard::factory(
            'answer',
            'create',
            0,
            [
                '0' => [
                    'tempId'  => '222',
                    'code'    => 'AO01',
                    'scaleId' => '0',
                    'l10ns'   => [
                        'en' => [
                            'answer' => 'answer',
                            'language' => 'en'
                        ],
                        'de' => [
                            'answer' => 'answerger',
                            'language' => 'de'
                        ]
                    ]
                ],
                '1' => [
                    'tempId'  => '333',
                    'code'    => 'AO01',
                    'scaleId' => '1',
                    'l10ns'   => [
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
            [
                'id' => 123456,
            ]
        );
    }
}
