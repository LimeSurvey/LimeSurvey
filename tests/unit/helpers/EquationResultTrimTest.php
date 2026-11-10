<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Test that Equation question results are trimmed like on the JavaScript side.
 *
 * @see https://bugs.limesurvey.org/view.php?id=18770
 * @group em
 */
class EquationResultTrimTest extends TestBaseClass
{
    /**
     * Each entry: [flattened equation result, expected trimmed result]
     * @return array
     */
    public function equationResultProvider(): array
    {
        return [
            'leading non-breaking space'      => ["\u{00A0}2", '2'],
            'trailing non-breaking space'     => ["2\u{00A0}", '2'],
            'several Unicode spaces'          => ["\u{00A0} \u{2002}\u{3000}2\u{202F}\u{FEFF}", '2'],
            'nbsp entity'                     => ['&nbsp;2&nbsp;', '2'],
            'ASCII whitespace'                => [" \t2 ", '2'],
            'inner spaces are kept'           => ["\u{00A0}a\u{00A0}b c\u{00A0}", "a\u{00A0}b c"],
            'inner nbsp entity is kept'       => ['a&nbsp;b', 'a&nbsp;b'],
            'empty result'                    => ['', ''],
            'only whitespace'                 => ["\u{00A0}&nbsp; ", ''],
            'plain number'                    => ['10.5', '10.5'],
        ];
    }

    /**
     * @dataProvider equationResultProvider
     * @param string $result
     * @param string $expected
     * @return void
     */
    public function testTrimEquationResult($result, $expected)
    {
        $this->assertSame($expected, \LimeExpressionManager::trimEquationResult($result));
    }

    /**
     * A leading non-breaking space must not turn a numeric result into a string, otherwise
     * relevance equations like Q1.NAOK > 1 are false in PHP while true in JavaScript.
     * @return void
     */
    public function testTrimmedResultIsNumeric()
    {
        $this->assertFalse(is_numeric("\u{00A0}2"));
        $this->assertTrue(is_numeric(\LimeExpressionManager::trimEquationResult("\u{00A0}2")));
    }

    /**
     * Invalid UTF-8 can't be handled by the regular expression and must be returned unchanged.
     * @return void
     */
    public function testInvalidUtf8IsReturnedUnchanged()
    {
        $this->assertSame("\xC3\x28", \LimeExpressionManager::trimEquationResult("\xC3\x28"));
    }
}
