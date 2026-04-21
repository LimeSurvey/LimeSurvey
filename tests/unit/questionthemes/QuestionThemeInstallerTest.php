<?php

namespace ls\tests;

use LimeSurvey\ExtensionInstaller\QuestionThemeInstaller;

class QuestionThemeInstallerTest extends TestBaseClass
{
    public function testValidateQuestionThemeNameAllowsSafeQuestionThemeNames()
    {
        $installer = new QuestionThemeInstaller();

        $this->assertTrue($installer->validateQuestionThemeName('Range-Slider'));
        $this->assertTrue($installer->validateQuestionThemeName('QuestionTheme_123'));
        $this->assertTrue($installer->validateQuestionThemeName(str_repeat('a', 150)));
    }

    public function testValidateQuestionThemeNameRejectsUnsafeOrTooLongNames()
    {
        $installer = new QuestionThemeInstaller();

        $cases = [
            '',
            '.',
            '..',
            '../twig/extensions',
            '..\\twig\\extensions',
            str_repeat('a', 151),
        ];

        foreach ($cases as $case) {
            $this->assertFalse(
                $installer->validateQuestionThemeName($case),
                'Expected invalid question theme name to be rejected: ' . json_encode($case)
            );
        }
    }
}
