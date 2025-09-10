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
        self::assertArrayHasKey(0, $preparedData['temp__123']);
        self::assertArrayHasKey(1, $preparedData['temp__456']);
        self::assertArrayHasKey('code', $preparedData['temp__123'][0]);
        self::assertEquals('AO01', $preparedData['temp__123'][0]['code']);
        self::assertArrayHasKey('answeroptionl10n', $preparedData['temp__123'][0]);
        self::assertIsArray($preparedData['temp__123'][0]['answeroptionl10n']);
        self::assertArrayHasKey('en', $preparedData['temp__123'][0]['answeroptionl10n']);
        self::assertArrayHasKey('de', $preparedData['temp__123'][0]['answeroptionl10n']);
        self::assertEquals(
            'answer',
            $preparedData['temp__123'][0]['answeroptionl10n']['en']
        );
        self::assertEquals(
            'answerger',
            $preparedData['temp__123'][0]['answeroptionl10n']['de']
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
                    'aid' => 'temp__123',
                    'tempId'  => 'temp__123',
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
                    'aid'  => 'temp__456',
                    'tempId'  => 'temp__456',
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
