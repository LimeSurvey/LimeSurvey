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
}
