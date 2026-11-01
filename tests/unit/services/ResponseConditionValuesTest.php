<?php

namespace ls\tests\unit\services;

use InvalidArgumentException;
use LimeSurvey\Models\Services\SurveyStatistics\ResponseConditionValues as V;
use PHPUnit\Framework\TestCase;

class ResponseConditionValuesTest extends TestCase
{
    public function testKindOfColumnTypes(): void
    {
        $this->assertSame(V::KIND_INTEGER, V::kindOf('integer', 'int(11)'));
        $this->assertSame(V::KIND_DECIMAL, V::kindOf('double', 'decimal(10,2)'));
        $this->assertSame(V::KIND_DATE, V::kindOf('string', 'date'));
        $this->assertSame(V::KIND_DATETIME, V::kindOf('string', 'timestamp without time zone'));
        $this->assertSame(V::KIND_DATETIME, V::kindOf('string', 'datetime'));
        $this->assertSame(V::KIND_TEXT, V::kindOf('string', 'varchar(255)'));
    }

    /**
     * @dataProvider invalidEqualProvider
     */
    public function testInvalidEqualValuesAreRejected($value, string $kind): void
    {
        $this->expectException(InvalidArgumentException::class);
        V::equalValue($value, $kind);
    }

    public function invalidEqualProvider(): array
    {
        return [
            'text in integer' => ['x', V::KIND_INTEGER],
            'json false in integer' => [false, V::KIND_INTEGER],
            'decimal in integer' => ['1.5', V::KIND_INTEGER],
            'exponent in integer' => ['1e5', V::KIND_INTEGER],
            'overflow int4' => ['2147483648', V::KIND_INTEGER],
            'trailing newline' => ["5\n", V::KIND_INTEGER],
            'text in decimal' => ['abc', V::KIND_DECIMAL],
            'exponent decimal' => ['1.0E+20', V::KIND_DECIMAL],
            'yes in datetime' => ['yes', V::KIND_DATETIME],
            'relative date' => ['+1 week', V::KIND_DATETIME],
            'locale date' => ['10/30/2026', V::KIND_DATE],
            'feb 31' => ['2026-02-31', V::KIND_DATE],
            'bad time' => ['2026-02-01 25:00:00', V::KIND_DATETIME],
            'boolean in text' => [true, V::KIND_TEXT],
        ];
    }

    public function testValidEqualValuesAreNormalizedStrings(): void
    {
        $this->assertSame('5', V::equalValue(5, V::KIND_INTEGER));
        $this->assertSame('-7', V::equalValue('-7', V::KIND_INTEGER));
        $this->assertSame('2147483647', V::equalValue('2147483647', V::KIND_INTEGER));
        $this->assertSame('9223372036854775807', V::equalValue('9223372036854775807', V::KIND_INTEGER, true));
        $this->assertSame('12345678901234567890', V::equalValue('12345678901234567890', V::KIND_DECIMAL));
        $this->assertSame('2.5', V::equalValue(2.5, V::KIND_DECIMAL));
        $this->assertSame('2026-02-28 10:00:00', V::equalValue('2026-02-28 10:00:00', V::KIND_DATETIME));
        $this->assertSame('free text', V::equalValue('free text', V::KIND_TEXT));
    }

    public function testRangeMustBeAList(): void
    {
        foreach ([['max' => 50, 'min' => 10], [1], [1, 2, 3], [1 => 1, 0 => 2], 'x'] as $bounds) {
            try {
                V::rangeBounds($bounds, V::KIND_INTEGER);
                $this->fail('Expected rejection');
            } catch (InvalidArgumentException $e) {
                $this->assertTrue(true);
            }
        }
    }

    public function testOpenBoundsAreNullOrEmpty(): void
    {
        $this->assertSame(['1', null, false], V::rangeBounds([1, null], V::KIND_INTEGER));
        $this->assertSame([null, '5', false], V::rangeBounds(['', 5], V::KIND_INTEGER));
        $this->expectException(InvalidArgumentException::class);
        V::rangeBounds([null, ''], V::KIND_INTEGER);
    }

    public function testNumericBoundsKeepTheValidatedString(): void
    {
        $this->assertSame(
            ['12345678901234567890', '1.50', false],
            V::rangeBounds(['12345678901234567890', '1.50'], V::KIND_TEXT)
        );
    }

    public function testIntegerColumnsRequireIntegerSyntax(): void
    {
        foreach ([['1.5', 10], [1, '1e20'], [false, 3]] as $bounds) {
            try {
                V::rangeBounds($bounds, V::KIND_INTEGER);
                $this->fail('Expected rejection');
            } catch (InvalidArgumentException $e) {
                $this->assertTrue(true);
            }
        }
    }

    public function testDateOnlyMaxOnDatetimeColumnIsExclusiveNextDay(): void
    {
        $this->assertSame(
            ['2026-10-01', '2026-10-31', true],
            V::rangeBounds(['2026-10-01', '2026-10-30'], V::KIND_DATETIME)
        );
        $this->assertSame(
            ['2026-10-01', '2027-01-01', true],
            V::rangeBounds(['2026-10-01', '2026-12-31'], V::KIND_DATETIME)
        );
    }

    public function testFullDatetimeMaxAndDateColumnsAreInclusive(): void
    {
        $this->assertSame(
            [null, '2026-10-30 12:00:00', false],
            V::rangeBounds([null, '2026-10-30 12:00:00'], V::KIND_DATETIME)
        );
        $this->assertSame(
            ['2026-10-01', '2026-10-30', false],
            V::rangeBounds(['2026-10-01', '2026-10-30'], V::KIND_DATE)
        );
    }

    public function testInvalidDateBoundsAreRejected(): void
    {
        foreach (['next monday', '2026-02-31', '10/30/2026', '2026-1-1', 20260101] as $bad) {
            try {
                V::rangeBounds([$bad, null], V::KIND_DATETIME);
                $this->fail("$bad should be rejected");
            } catch (InvalidArgumentException $e) {
                $this->assertTrue(true);
            }
        }
    }

    public function testKeyAllowlist(): void
    {
        foreach (['Q12', 'Q12_S3', '123X4X5', '123X4X5SQ001', 'quotaExit', 'id', 'submitdate', 'startdate', 'datestamp', 'lastpage'] as $key) {
            $this->assertTrue(V::isAllowedKey($key), $key);
        }
        foreach (['token', 'ipaddr', 'refurl', 'seed', 'startlanguage', 'Q12; DROP', '', 'q'] as $key) {
            $this->assertFalse(V::isAllowedKey($key), $key);
        }
        $this->assertTrue(V::isTokenKey(' Token '));
    }
}
