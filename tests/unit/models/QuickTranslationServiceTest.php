<?php

namespace ls\tests;

use LimeSurvey\Models\Services\QuickTranslation;

class QuickTranslationServiceTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_161359_quickTranslation.lss';
        self::importSurvey($surveyFile);
    }

    public function testGetTranslationsNoTranslation()
    {
        $qt = new QuickTranslation(self::$testSurvey);
        $ts = $qt->getTranslations('title', 'aa');
        $this->assertEmpty($ts);
    }

    public function testGetTranslationsEnglishTitle()
    {
        $qt = new QuickTranslation(self::$testSurvey);
        $ts = $qt->getTranslations('title', 'en');
        $this->assertNotEmpty($ts);
        $this->assertEquals($ts[0]->surveyls_title, 'translation');
    }

    public function testGetTranslationsQuestion()
    {
        $qt = new QuickTranslation(self::$testSurvey);
        $ts = $qt->getTranslations('question', 'en');
        $this->assertNotEmpty($ts[0]->questionl10ns);
        $this->assertEquals($ts[0]->questionl10ns['en']->help, 'This is a question help text.');
    }

    public function testUpdateTranslationUnknownField()
    {
        $qt = new QuickTranslation(self::$testSurvey);
        $ts = $qt->updateTranslations('wrongField', 'en', 'newstring');
        $this->assertNull($ts);
    }

    public function testUpdateTranslationEnglishTitle()
    {
        $qt = new QuickTranslation(self::$testSurvey);
        $ts = $qt->updateTranslations('title', 'en', 'new-title');
        $this->assertNotNull($ts);
        $this->assertEquals(1, $ts);
    }

    /* --> something wrong with imported test-survey and the groupid, that should be 2
    public function testUpdateTranslationArabicGroup()
    {
        $qt = new QuickTranslation(self::$testSurvey);
        $ts = $qt->updateTranslations('group', 'de', 'newGroupTitle', 2);
        $this->assertNotNull($ts);
        $this->assertEquals(1, $ts);
    }*/

    /**
     * Expressions are replaced by markers, the HTML around them stays in one piece.
     */
    public function testProtectExpressionsKeepsHtmlTogether()
    {
        $protected = QuickTranslation::protectExpressions('<p>Dear {TOKEN:FIRSTNAME},</p><p>Age: <b>{age}</b> years</p>');

        $this->assertSame(
            '<p>Dear <span class="notranslate" translate="no">LSEXPR0</span>,</p>'
            . '<p>Age: <b><span class="notranslate" translate="no">LSEXPR1</span></b> years</p>',
            $protected['text']
        );
        $this->assertSame(['{TOKEN:FIRSTNAME}', '{age}'], $protected['expressions']);
        $this->assertTrue($protected['hasText']);
    }

    /**
     * A text without expressions is returned as it is.
     */
    public function testProtectExpressionsWithoutExpressions()
    {
        $protected = QuickTranslation::protectExpressions('First line<br>Second line');

        $this->assertSame('First line<br>Second line', $protected['text']);
        $this->assertSame([], $protected['expressions']);
        $this->assertTrue($protected['hasText']);
    }

    /**
     * A text made of tags and expressions only has nothing to translate.
     */
    public function testProtectExpressionsWithoutText()
    {
        $protected = QuickTranslation::protectExpressions('<p>{TOKEN:FIRSTNAME} </p>');

        $this->assertFalse($protected['hasText']);
    }

    /**
     * Expressions are put back exactly, also when the translation dropped the marker's wrapper.
     */
    public function testRestoreExpressions()
    {
        $expressions = ['{if(age > 5, "a & b", \'$1\\\\\')}', '{age}'];
        $translated = '<p>Hallo <span class="notranslate" translate="no">LSEXPR0</span>,</p><p>Alter: <b>LSEXPR1</b></p>';

        $this->assertSame(
            '<p>Hallo ' . $expressions[0] . ',</p><p>Alter: <b>{age}</b></p>',
            QuickTranslation::restoreExpressions($translated, $expressions)
        );
    }

    /**
     * A translation that lost an expression is rejected.
     */
    public function testRestoreExpressionsMissingMarker()
    {
        $this->assertNull(QuickTranslation::restoreExpressions('<p>Hallo</p>', ['{TOKEN:FIRSTNAME}']));
    }
}
