<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2017-12-01
 * @group multem
 */
class MultipleChoiceWithExpressionTest extends TestBaseClassWeb
{
    /**
     * 
     */
    public function testBasic()
    {
        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_352985.lss';
        self::importSurvey($surveyFile);
    }
}
