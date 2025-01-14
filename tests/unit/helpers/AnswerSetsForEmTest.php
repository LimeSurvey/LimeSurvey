<?php

namespace ls\tests;

use Yii;
use LimeExpressionManager;

/**
 * Tests for the GititSurvey remote API.
 */
class AnswerSetsForEmTest extends TestBaseClass
{
    private static $em;

    /**
     *
     */
    public static function setupBeforeClass(): void
    {
        parent::setupBeforeClass();

        Yii::import('application.helpers.common_helper', true);
        Yii::import('application.helpers.expressions.em_manager_helper', true);

        self::$em = LimeExpressionManager::singleton();

        /** @var string */
        $filename = self::$surveysFolder . '/limesurvey_survey_188432_answerSetsForEmTest.lss';
        self::importSurvey($filename);
    }

    public function testGerman()
    {
        $ansArray = self::$em->getAnswerSetsForEM(self::$surveyId, 'en');
        $this->assertCount(2, $ansArray);
        $second  = array_pop($ansArray);
        $first = array_pop($ansArray);
        $this->assertEquals('0|First Entry', $first['0~1']);
        $this->assertEquals('0|a en', $second['0~AO01']);
    }

    public function testEnglish()
    {
        $ansArray = self::$em->getAnswerSetsForEM(self::$surveyId, 'de');
        $this->assertCount(2, $ansArray);
        $second  = array_pop($ansArray);
        $first = array_pop($ansArray);
        $this->assertEquals('0|Erster Eintrag', $first['0~1']);
        $this->assertEquals('0|a', $second['0~AO01']);
    }
}
