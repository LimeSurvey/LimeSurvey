<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\DateProcessor;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use PHPUnit\Framework\TestCase;
use Question;

class DateProcessorTest extends TestCase
{
    use ResponseAggregateBatchTestHelper;

    private function makeProcessor(array $question = []): array
    {
        $processor = new DateProcessor();
        $processor->setQuestion(array_merge([
            'sid' => 1,
            'gid' => 1,
            'qid' => 5,
            'type' => Question::QT_D_DATE,
            'title' => 'Q5',
            'question' => 'When did you start your current job?',
            'encrypted' => 'N',
            'attributes' => [],
        ], $question));
        $batch = new ResponseAggregateBatch(1);
        $processor->setBatch($batch);

        return [$processor, $batch];
    }

    /**
     * @testdox process() reports the answered count and the answers per day once the batch resolves
     */
    public function testProcessReportsAnsweredCountAndDays()
    {
        [$processor, $batch] = $this->makeProcessor();

        $plan = $processor->process();

        $this->assertSame('When did you start your current job?', $plan['title']);
        $this->assertSame([], $plan['legend']);
        $this->assertSame('Q5', $plan['data'][0]['key']);

        $days = ['2023-01-08' => 3, '2026-01-12' => 27, '2026-10-27' => 1];
        $this->injectBatchResults($batch, [
            // Numeric-column mode: MySQL 8 rejects comparing datetime to ''.
            $batch->countNonEmpty('Q5', true) => 31,
            $batch->countByDay('Q5') => $days,
        ]);

        $this->assertSame(31, $plan['data'][0]['value']());
        $this->assertSame(31, $plan['total']());
        $this->assertSame([
            'earliest' => '2023-01-08',
            'latest' => '2026-10-27',
            'days' => $days,
        ], $plan['data'][0]['stats']());
    }

    /**
     * @testdox process() reports null stats when no day was answered
     */
    public function testProcessReportsNullStatsWhenUnanswered()
    {
        [$processor, $batch] = $this->makeProcessor();

        $plan = $processor->process();

        $this->injectBatchResults($batch, [
            $batch->countNonEmpty('Q5', true) => 0,
            $batch->countByDay('Q5') => [],
        ]);

        $this->assertSame(0, $plan['data'][0]['value']());
        $this->assertNull($plan['data'][0]['stats']());
    }

    /**
     * @testdox process() only counts encrypted answers, as text, and plans no day query
     */
    public function testProcessSkipsDaysForEncryptedAnswers()
    {
        [$processor, $batch] = $this->makeProcessor(['encrypted' => 'Y']);

        $plan = $processor->process();

        $this->assertArrayNotHasKey('stats', $plan['data'][0]);

        $this->injectBatchResults($batch, [$batch->countNonEmpty('Q5') => 4]);
        $this->assertSame(4, $plan['data'][0]['value']());
    }

    /**
     * @testdox hasTime() is true only when the question's own format has hours or minutes
     * @dataProvider dateFormatProvider
     */
    public function testHasTime(?string $format, bool $expected)
    {
        $question = $format === null ? [] : ['attributes' => ['date_format' => $format]];

        $this->assertSame($expected, DateProcessor::hasTime($question));
    }

    public static function dateFormatProvider(): array
    {
        return [
            'no format (survey default is date-only)' => [null, false],
            'empty format' => ['', false],
            'date only, lowercase mm is the month' => ['yyyy-mm-dd', false],
            'date + time' => ['yyyy-mm-dd HH:MM', true],
            'hours only' => ['dd/mm/yyyy H', true],
        ];
    }
}
