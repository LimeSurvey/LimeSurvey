<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputAnswer;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAttribute;
use LimeSurvey\DI;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;

/**
 * @testdox TransformerInputAnswer
 */
class TransformerInputQuestionAttributeTest extends TestBaseClass
{
    /**
     * @testdox test attribute conversion
     */
    public function testQuestionAttributeConversion()
    {
        $op = $this->getOp(false);
        $transformer = DI::getContainer()->get(
            TransformerInputQuestionAttribute::class
        );
        $preparedData = $transformer->transformAll(
            (array)$op->getProps(),
            ['operation' => 'update']
        );

        self::assertIsArray($preparedData);
        self::assertArrayHasKey('statistics_graphtype', $preparedData);
        self::assertArrayHasKey('statistics_showgraph', $preparedData);
        self::assertArrayHasKey('', $preparedData['statistics_graphtype']);
        self::assertArrayHasKey('', $preparedData['statistics_showgraph']);
        self::assertEquals(
            '-1',
            $preparedData['statistics_graphtype']['']
        );
        self::assertEquals(
            '0',
            $preparedData['statistics_showgraph']['']
        );

        $op = $this->getOp();
        $preparedData = $transformer->transformAll(
            (array)$op->getProps(),
            ['operation' => 'update']
        );

        self::assertEquals(
            '1',
            $preparedData['statistics_graphtype']['']
        );
        self::assertEquals(
            '1',
            $preparedData['statistics_showgraph']['']
        );
    }

    /**
     * @return OpStandard
     * @throws \LimeSurvey\ObjectPatch\ObjectPatchException
     */
    private function getOp($hasGraphType = true): OpStandard
    {
        return OpStandard::factory(
            'questionAttribute',
            'update',
            0,
            $this->getProps($hasGraphType),
            [
                'id' => 123456,
            ]
        );
    }

    private function getProps($hasGraphType = true)
    {
        $value = $hasGraphType ? '-1' : '1';
        return [
            'statistics_graphtype' => [
                '' => $value,
            ],
        ];
    }
}
