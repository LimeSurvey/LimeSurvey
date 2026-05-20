<?php

namespace ls\tests;

/**
 * Unit tests for dateShift() in application/helpers/common_helper.php.
 */
class DateShiftTest extends TestBaseClass
{
    /** @var string|null Original displayTimezone config value */
    private $originalDisplayTimezone;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.helpers.common_helper', true);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalDisplayTimezone = \Yii::app()->getConfig('displayTimezone');
    }

    protected function tearDown(): void
    {
        \Yii::app()->setConfig('displayTimezone', $this->originalDisplayTimezone);
        parent::tearDown();
    }

    // ------------------------------------------------------------------ //
    // Basic conversion
    // ------------------------------------------------------------------ //

    public function testConvertsUtcToPositiveOffset(): void
    {
        // UTC 2024-01-15 10:00:00 → Europe/Paris (UTC+1 in winter) = 11:00:00
        $result = dateShift('2024-01-15 10:00:00', 'Y-m-d H:i:s', 'Europe/Paris', 'UTC');
        $this->assertSame('2024-01-30 11:00:00', $result);
    }

    public function testConvertsUtcToNegativeOffset(): void
    {
        // UTC 2024-01-15 10:00:00 → America/New_York (UTC-5 in winter) = 05:00:00
        $result = dateShift('2024-01-15 10:00:00', 'Y-m-d H:i:s', 'America/New_York', 'UTC');
        $this->assertSame('2024-01-30 05:00:00', $result);
    }

    public function testConvertsNonUtcSourceTimezone(): void
    {
        // America/New_York 2024-01-15 05:00:00 → UTC = 10:00:00
        $result = dateShift('2024-01-15 05:00:00', 'Y-m-d H:i:s', 'UTC', 'America/New_York');
        $this->assertSame('2024-01-15 10:00:00', $result);
    }

    public function testConvertsAcrossTwoNonUtcTimezones(): void
    {
        // Asia/Tokyo (UTC+9) 2024-06-01 09:00:00 → America/New_York (UTC-4 in summer) = previous day 20:00:00
        $result = dateShift('2024-06-01 09:00:00', 'Y-m-d H:i:s', 'America/New_York', 'Asia/Tokyo');
        $this->assertSame('2024-05-31 20:00:00', $result);
    }

    // ------------------------------------------------------------------ //
    // Output format
    // ------------------------------------------------------------------ //

    public function testRespectsCustomDateFormat(): void
    {
        $result = dateShift('2024-03-10 15:30:00', 'd/m/Y H:i', 'UTC', 'UTC');
        $this->assertSame('10/03/2024 15:30', $result);
    }

    public function testDateOnlyFormat(): void
    {
        $result = dateShift('2024-07-04 23:30:00', 'Y-m-d', 'America/New_York', 'UTC');
        // UTC-4 (EDT): 23:30 UTC → 19:30 NYC — same date
        $this->assertSame('2024-07-04', $result);
    }

    public function testTimeOnlyFormat(): void
    {
        $result = dateShift('2024-01-01 08:00:00', 'H:i:s', 'Europe/London', 'UTC');
        // UTC+0 in winter → same time
        $this->assertSame('08:00:00', $result);
    }

    // ------------------------------------------------------------------ //
    // Cross-day boundary
    // ------------------------------------------------------------------ //

    public function testCrossDayBoundaryForward(): void
    {
        // UTC 2024-01-15 23:30:00 → Asia/Tokyo (UTC+9) = 2024-01-16 08:30:00
        $result = dateShift('2024-01-15 23:30:00', 'Y-m-d H:i:s', 'Asia/Tokyo', 'UTC');
        $this->assertSame('2024-01-16 08:30:00', $result);
    }

    public function testCrossDayBoundaryBackward(): void
    {
        // UTC 2024-03-01 01:00:00 → America/Los_Angeles (UTC-8 in winter) = 2024-02-29 17:00:00
        $result = dateShift('2024-03-01 01:00:00', 'Y-m-d H:i:s', 'America/Los_Angeles', 'UTC');
        $this->assertSame('2024-02-29 17:00:00', $result);
    }

    // ------------------------------------------------------------------ //
    // Daylight Saving Time
    // ------------------------------------------------------------------ //

    public function testDstSummerOffset(): void
    {
        // New York is UTC-4 during EDT (summer)
        $result = dateShift('2024-07-01 12:00:00', 'H:i', 'America/New_York', 'UTC');
        $this->assertSame('08:00', $result);
    }

    public function testDstWinterOffset(): void
    {
        // New York is UTC-5 during EST (winter)
        $result = dateShift('2024-01-01 12:00:00', 'H:i', 'America/New_York', 'UTC');
        $this->assertSame('07:00', $result);
    }

    public function testDstTransitionSpringForward(): void
    {
        // US spring-forward: clocks jump 02:00 → 03:00 on 2024-03-10
        // UTC 07:00 = exactly when clocks spring forward in New York
        $result = dateShift('2024-03-10 07:00:00', 'Y-m-d H:i:s', 'America/New_York', 'UTC');
        $this->assertSame('2024-03-10 03:00:00', $result);
    }

    // ------------------------------------------------------------------ //
    // Yii config fallback — $toTimezone is null/empty
    // ------------------------------------------------------------------ //

    public function testReturnsOriginalDateWhenNoTimezoneAndNoYiiConfig(): void
    {
        \Yii::app()->setConfig('displayTimezone', '');

        $input = '2024-05-20 10:00:00';
        $result = dateShift($input, 'Y-m-d H:i:s', null, 'UTC');
        // No timezone configured → original string returned unchanged
        $this->assertSame($input, $result);
    }

    public function testUsesYiiConfigTimezoneWhenToTimezoneIsNull(): void
    {
        \Yii::app()->setConfig('displayTimezone', 'Europe/Berlin');

        // Europe/Berlin is UTC+1 in winter
        $result = dateShift('2024-01-10 12:00:00', 'Y-m-d H:i:s', null, 'UTC');
        $this->assertSame('2024-01-10 13:00:00', $result);
    }

    // ------------------------------------------------------------------ //
    // Data-provider: same-timezone round-trip
    // ------------------------------------------------------------------ //

    /**
     * @dataProvider provideSameTimezoneInputs
     */
    public function testSameTimezoneReturnsSameDatetime(string $date, string $timezone): void
    {
        $result = dateShift($date, 'Y-m-d H:i:s', $timezone, $timezone);
        $this->assertSame($date, $result, "Round-trip in {$timezone} should be identity.");
    }

    public static function provideSameTimezoneInputs(): array
    {
        return [
            'UTC identity'            => ['2024-06-15 08:30:00', 'UTC'],
            'Europe/London identity'  => ['2024-11-01 14:00:00', 'Europe/London'],
            'Asia/Tokyo identity'     => ['2024-03-20 22:45:00', 'Asia/Tokyo'],
            'America/Chicago identity'=> ['2024-08-05 09:15:00', 'America/Chicago'],
        ];
    }

    // ------------------------------------------------------------------ //
    // Data-provider: known UTC ↔ offset conversions
    // ------------------------------------------------------------------ //

    /**
     * @dataProvider provideUtcConversions
     */
    public function testKnownUtcConversions(
        string $inputDate,
        string $toTimezone,
        string $expected
    ): void {
        $result = dateShift($inputDate, 'Y-m-d H:i:s', $toTimezone, 'UTC');
        $this->assertSame($expected, $result);
    }

    public static function provideUtcConversions(): array
    {
        return [
            'UTC to UTC'                    => ['2024-09-01 00:00:00', 'UTC',              '2024-09-01 00:00:00'],
            'UTC to IST (+5:30)'            => ['2024-09-01 00:00:00', 'Asia/Kolkata',     '2024-09-01 05:30:00'],
            'UTC to AEST (+10)'             => ['2024-09-01 00:00:00', 'Australia/Sydney', '2024-09-01 10:00:00'],
            'UTC to Hawaii (-10)'           => ['2024-09-01 00:00:00', 'Pacific/Honolulu', '2024-08-31 14:00:00'],
            'UTC midnight to Paris winter'  => ['2024-12-25 00:00:00', 'Europe/Paris',     '2024-12-25 01:00:00'],
        ];
    }
}
