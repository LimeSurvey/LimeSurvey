<?php

namespace ls\tests\unit\api\command;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;
use ls\tests\TestBaseClass;
use Mockery;
use ReflectionClass;
use Survey;

class SurveyResponsesTest extends TestBaseClass
{
    /**
     * @dataProvider timingsDisabledProvider
     */
    public function testAppendTimingDataRequiresSettingAndTable(
        bool $isSaveTimings,
        bool $hasTimingsTable
    ): void {
        $survey = Mockery::mock(Survey::class)->makePartial();
        $survey->shouldReceive('getIsSaveTimings')->andReturn($isSaveTimings);
        $survey->shouldReceive('getHasTimingsTable')->andReturn($hasTimingsTable);

        $reflection = new ReflectionClass(SurveyResponses::class);
        $command = $reflection->newInstanceWithoutConstructor();
        $surveyProperty = $reflection->getProperty('survey');
        $surveyProperty->setAccessible(true);
        $surveyProperty->setValue($command, $survey);

        $responses = [['id' => 1]];
        $method = $reflection->getMethod('appendTimingData');
        $method->setAccessible(true);
        $arguments = [&$responses];

        $this->assertSame([], $method->invokeArgs($command, $arguments));
        $this->assertSame([['id' => 1]], $responses);
    }

    public function timingsDisabledProvider(): array
    {
        return [
            'setting disabled while table exists' => [false, true],
            'setting enabled while table is missing' => [true, false],
        ];
    }
}
