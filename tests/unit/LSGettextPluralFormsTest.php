<?php

namespace ls\tests;

use LSGettextPluralForms;

/**
 * Tests the evaluation of gettext Plural-Forms headers
 */
class LSGettextPluralFormsTest extends TestBaseClass
{
    /**
     * Plural-Forms headers as exported by translate.limesurvey.org,
     * with the expected form index for each count
     *
     * @return array
     */
    public function pluralFormsProvider(): array
    {
        return [
            'German' => [
                'nplurals=2; plural=n != 1;',
                [0 => 1, 1 => 0, 2 => 1, 5 => 1, 21 => 1],
            ],
            'Polish' => [
                'nplurals=3; plural=(n == 1) ? 0 : ((n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 12 || n % 100 > 14)) ? 1 : 2);',
                [0 => 2, 1 => 0, 2 => 1, 4 => 1, 5 => 2, 12 => 2, 14 => 2, 21 => 2, 22 => 1, 112 => 2, 122 => 1],
            ],
            'Czech' => [
                'nplurals=3; plural=(n == 1) ? 0 : ((n >= 2 && n <= 4) ? 1 : 2);',
                [0 => 2, 1 => 0, 2 => 1, 4 => 1, 5 => 2, 22 => 2],
            ],
            'Arabic' => [
                'nplurals=6; plural=(n == 0) ? 0 : ((n == 1) ? 1 : ((n == 2) ? 2 : ((n % 100 >= 3 && n % 100 <= 10) ? 3 : ((n % 100 >= 11 && n % 100 <= 99) ? 4 : 5))));',
                [0 => 0, 1 => 1, 2 => 2, 3 => 3, 10 => 3, 11 => 4, 99 => 4, 100 => 5, 103 => 3],
            ],
            'Japanese' => [
                'nplurals=1; plural=0;',
                [0 => 0, 1 => 0, 5 => 0],
            ],
            'French, without spaces' => [
                'nplurals=2;plural=(n>1);',
                [0 => 0, 1 => 0, 2 => 1],
            ],
            'Arithmetic and unary operators' => [
                'nplurals=3; plural=!(n - 1) ? 0 : n * 2 / 4 - -1 >= 3 ? 2 : 1;',
                [1 => 0, 2 => 1, 3 => 1, 4 => 2],
            ],
            'Full catalog header' => [
                "Content-Type: text/plain; charset=UTF-8\nPlural-Forms: nplurals=3; plural=(n == 1) ? 0 : ((n >= 2 && n <= 4) ? 1 : 2);\nLanguage: cs\n",
                [1 => 0, 3 => 1, 7 => 2],
            ],
        ];
    }

    /**
     * @dataProvider pluralFormsProvider
     * @param string $header
     * @param array<int,int> $expectedIndexes Expected form index by count
     * @return void
     */
    public function testGetFormIndex(string $header, array $expectedIndexes): void
    {
        foreach ($expectedIndexes as $count => $expectedIndex) {
            $this->assertSame(
                $expectedIndex,
                LSGettextPluralForms::getFormIndex($header, $count),
                "Wrong plural form for n = $count"
            );
        }
    }

    /**
     * Headers without a valid expression fall back to the English rule
     *
     * @return array
     */
    public function invalidHeaderProvider(): array
    {
        return [
            'empty' => [''],
            'no plural expression' => ['nplurals=2;'],
            'unknown variable' => ['nplurals=2; plural=x != 1;'],
            'function call' => ['nplurals=2; plural=strlen(n);'],
            'unbalanced parenthesis' => ['nplurals=2; plural=(n != 1;'],
            'incomplete ternary' => ['nplurals=2; plural=n == 1 ? 0;'],
            'trailing operator' => ['nplurals=2; plural=n !=;'],
        ];
    }

    /**
     * @dataProvider invalidHeaderProvider
     * @param string $header
     * @return void
     */
    public function testInvalidHeaderFallsBackToEnglish(string $header): void
    {
        $this->assertSame(1, LSGettextPluralForms::getFormIndex($header, 0));
        $this->assertSame(0, LSGettextPluralForms::getFormIndex($header, 1));
        $this->assertSame(1, LSGettextPluralForms::getFormIndex($header, 2));
    }

    /**
     * A division by zero in the expression must not throw
     *
     * @return void
     */
    public function testDivisionByZero(): void
    {
        $this->assertSame(0, LSGettextPluralForms::getFormIndex('nplurals=2; plural=n / 0 + n % 0;', 5));
    }
}
