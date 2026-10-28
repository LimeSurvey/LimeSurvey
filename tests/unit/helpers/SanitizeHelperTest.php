<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

class SanitizeHelperTest extends TestBaseClass
{
    /**
     * Decimals and text must not throw when the GMP extension is loaded (gmp_init() only accepts whole numbers).
     */
    public function testSanitizeFloatHandlesDecimalsAndText()
    {
        \Yii::import('application.helpers.sanitize_helper', true);

        $this->assertEquals(2, sanitize_float('2'));
        $this->assertEquals(-7, sanitize_float(' -7 '));
        $this->assertEquals(1.5, sanitize_float('1.5'));
        $this->assertEquals(1.5, sanitize_float('1,5'));
        $this->assertEquals(0, sanitize_float('abc'));
        $this->assertEquals(1, sanitize_float('1abc.txt'));
        $this->assertFalse(sanitize_float('1.5', 2));
        $this->assertFalse(sanitize_float('5', '', 4));
    }

    public function testValidatePathComponentAllowsSinglePathComponent()
    {
        \Yii::import('application.helpers.sanitize_helper', true);

        $this->assertTrue(validate_path_component('Range-Slider'));
    }

    public function testValidatePathComponentRejectsUnsafeNames()
    {
        \Yii::import('application.helpers.sanitize_helper', true);

        $cases = [
            '',
            '.',
            '..',
            '.git',
            'foo/bar',
            'foo\\bar',
            '../twig/extensions',
            '..\\twig\\extensions',
            "bad\0name",
            "\x7F",
        ];

        foreach ($cases as $case) {
            $this->assertFalse(
                validate_path_component($case),
                'Expected invalid path component to be rejected: ' . json_encode($case)
            );
        }
    }
}
