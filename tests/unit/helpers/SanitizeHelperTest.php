<?php

namespace ls\tests;

class SanitizeHelperTest extends TestBaseClass
{
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
