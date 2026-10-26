<?php

namespace ls\tests\unit\services\SurveyStatistics;

use LimeSurvey\Models\Services\SurveyStatistics\DateTimeValues;
use PHPUnit\Framework\TestCase;

class DateTimeValuesTest extends TestCase
{
    /**
     * @testdox groupByDay() groups stored values by day and then time, sorted, with the total
     */
    public function testGroupByDayGroupsByDayAndTime()
    {
        $result = DateTimeValues::groupByDay(true, [
            '2026-01-12 10:00:00' => 2,
            '2026-01-12 09:00:00' => 12,
            '2026-01-06 08:30:00' => 1,
        ]);

        $this->assertSame([
            'hasTime' => true,
            'total' => 15,
            'days' => [
                '2026-01-06' => ['08:30:00' => 1],
                '2026-01-12' => ['09:00:00' => 12, '10:00:00' => 2],
            ],
        ], $result);
    }

    /**
     * @testdox groupByDay() reads decrypted answers, which have no seconds, like stored datetimes
     */
    public function testGroupByDayReadsDecryptedAnswers()
    {
        $result = DateTimeValues::groupByDay(true, [
            '2026-10-16 23:59' => 1,
            '2026-10-16 23:59:00' => 2,
        ]);

        $this->assertSame(['2026-10-16' => ['23:59:00' => 3]], $result['days']);
        $this->assertSame(3, $result['total']);
    }

    /**
     * @testdox groupByDay() keeps values that only differ in seconds apart
     */
    public function testGroupByDayKeepsStoredPrecision()
    {
        $result = DateTimeValues::groupByDay(true, [
            '2026-01-12 09:00:00' => 1,
            '2026-01-12 09:00:30' => 1,
        ]);

        $this->assertSame(['09:00:00' => 1, '09:00:30' => 1], $result['days']['2026-01-12']);
    }

    /**
     * @testdox groupByDay() keeps midnight as a time and lists values without a time under ''
     */
    public function testGroupByDaySeparatesMidnightFromNoTime()
    {
        $result = DateTimeValues::groupByDay(true, [
            '2026-10-02 00:00:00' => 1,
            '2026-10-02' => 4,
        ]);

        $this->assertSame(['' => 4, '00:00:00' => 1], $result['days']['2026-10-02']);
    }

    /**
     * @testdox groupByDay() skips values that are no real date
     */
    public function testGroupByDaySkipsInvalidDates()
    {
        $result = DateTimeValues::groupByDay(false, [
            '0000-00-00 00:00:00' => 3,
            '2026-02-30 10:00:00' => 1,
            'not a date' => 1,
            '2024-02-29 00:00:00' => 2,
        ]);

        $this->assertSame([
            'hasTime' => false,
            'total' => 2,
            'days' => ['2024-02-29' => ['00:00:00' => 2]],
        ], $result);
    }
}
