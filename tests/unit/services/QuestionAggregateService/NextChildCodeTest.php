<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use PHPUnit\Framework\TestCase;
use LimeSurvey\Models\Services\QuestionAggregateService\NextChildCode;

/**
 * @group services
 */
class NextChildCodeTest extends TestCase
{
    /**
     * @return array<string, array{0: array, 1: string|null, 2: string}>
     */
    public static function nextCodeProvider(): array
    {
        return [
            'numeric, no padding'             => [['1', '2'], null, '3'],
            'numeric, padded to 2'            => [['01', '02'], null, '03'],
            'numeric, padded to 3'            => [['001', '002'], null, '003'],
            'numeric after a single 9'        => [['9'], null, '10'],
            'numeric keeps leading zero until width grows' => [['009'], null, '010'],
            'numeric 099 grows to 100'        => [['099'], null, '100'],
            'numeric 999 grows to 1000'       => [['999'], null, '1000'],
            'numeric 9999 grows to 10000'     => [['9999'], null, '10000'],
            'prefixed 99 grows to 100'        => [['SQ99'], null, 'SQ100'],
            'prefixed 999 grows to 1000'      => [['SQ999'], null, 'SQ1000'],
            'prefixed legacy'                 => [['SQ001', 'SQ002'], null, 'SQ003'],
            'prefixed AO'                     => [['AO01'], null, 'AO02'],
            'gaps are ignored, max + 1'       => [['1', '7', '3'], null, '8'],
            'zero'                            => [['0'], null, '1'],
            'double zero'                     => [['00'], null, '01'],
            'existing prefix wins'            => [['SQ001'], '1', 'SQ002'],
            'no digits uses first code'       => [[], '1', '1'],
            'empty list with prefix'          => [[], 'SQ001', 'SQ001'],
            'empty list, no first code'       => [[], null, '1'],
        ];
    }

    /**
     * @testdox calculate() derives the next code from existing codes
     * @dataProvider nextCodeProvider
     */
    public function testCalculate(array $existing, ?string $firstCode, string $expected): void
    {
        [$code] = NextChildCode::calculate($existing, $firstCode);
        $this->assertSame($expected, $code);
    }

    /**
     * @testdox generated numeric codes are unique, valid and within 5 chars for 1..99999 steps
     */
    public function testGeneratedNumericCodesAreUniqueAndValid(): void
    {
        $codes = ['1'];
        for ($i = 0; $i < 120; $i++) {
            [$code] = NextChildCode::calculate($codes);
            $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{1,5}$/', $code);
            $this->assertNotContains($code, $codes);
            $codes[] = $code;
        }
        $this->assertSame('121', end($codes));
    }

    /**
     * @testdox calculate() never changes the existing codes
     */
    public function testExistingCodesAreNotModified(): void
    {
        $codes = ['001', '002'];
        $copy = $codes;
        NextChildCode::calculate($codes);
        $this->assertSame($copy, $codes);
    }

    /**
     * @testdox leading zero codes survive as strings and stay distinct from numeric equivalents
     */
    public function testLeadingZeroCodesStayStrings(): void
    {
        [$code] = NextChildCode::calculate(['007']);
        $this->assertIsString($code);
        $this->assertSame('008', $code);
    }
}
