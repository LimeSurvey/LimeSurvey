<?php

namespace ls\tests;

/**
 * Tests for the localize_date function.
 */

class LocalizeDateTest extends TestBaseClass
{
    public static function setupBeforeClass(): void
    {
        parent::setupBeforeClass();
        \Yii::import('application.core.plugins.dateFunctions.EMFunctions', true);
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_854771.lss');
        \Yii::app()->session['LEMsid'] = self::$surveyId;
    }

    /**
     * Testing for different date strings.
     */
    public function testDateStrings(): void
    {
        //Testing for an empty date string
        $date = \dateFunctions\EMFunctions::localize_date('');
        $this->assertEmpty($date, 'An empty string was passed as the date parameter');

        //Testing for a date string in an incorrect format.
        //Trying to localize January 30th 2023.
        $date = \dateFunctions\EMFunctions::localize_date('30-01-2023');
        $this->assertNotEquals($date, '01-30-2023', 'A date string with a wrong format was passed (30-01-2023)');

        //Testing for day only
        $date = \dateFunctions\EMFunctions::localize_date('2023-01-30');
        $this->assertEquals($date, '01-30-2023', 'The date string was correct (2023-01-30)');

        //Testing for day and hour
        $date = \dateFunctions\EMFunctions::localize_date('2023-01-30 10:13:00');
        $this->assertEquals($date, '01-30-2023', 'The date string was correct (2023-01-30 10:13:00)');
    }

    /**
     * Testing for localization in different languages.
     */
    public function testLocalizationInDifferentLanugages(): void
    {
        //Localize a date in the base language of the survey (en)
        $date = \dateFunctions\EMFunctions::localize_date('2023-01-24');
        $this->assertEquals($date, '01-24-2023', 'The date 2023-01-24 should have been formatted in English');

        //Localize a date in an additional language of the survey (es)
        $date = \dateFunctions\EMFunctions::localize_date('2023-01-24', 'es');
        $this->assertEquals($date, '24/01/2023', 'The date 2023-01-24 should have been formatted in Spanish');

        //Localize a date in a language that was not set in the survey (it)
        $date = \dateFunctions\EMFunctions::localize_date('2023-01-24', 'it');
        $this->assertEquals($date, '01-24-2023', 'The date 2023-01-24 should have been formatted in English, Italian was not set.');
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }
}
