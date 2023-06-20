<?php

namespace ls\tests;

/**
 * Tests for the GetQuestions function in LimeSurveyApi.
 */
class GetQuestionsTest extends TestBaseClass
{
    public static function setupBeforeClass(): void
    {
        parent::setupBeforeClass();

        self::importSurvey(self::$surveysFolder . '/survey_GetQuestionTest.lss');
    }

    /**
     * Testing that there are three questions in English.
     */
    public function testGetQuestionsInEnglish()
    {
        $limeSurveyApi = new \LimeSurvey\PluginManager\LimesurveyApi();
        // The function takes English by default.
        $questionsInEnglish = $limeSurveyApi->getQuestions(self::$surveyId);

        $this->assertCount(3, $questionsInEnglish, 'Unexpected number of questions in English returned.');
    }

    /**
     * Testing that there are three questions in Spanish.
     */
    public function testGetQuestionsInSpanish()
    {
        $limeSurveyApi = new \LimeSurvey\PluginManager\LimesurveyApi();

        $questionsInSpanish = $limeSurveyApi->getQuestions(self::$surveyId, 'es');

        $this->assertCount(3, $questionsInSpanish, 'Unexpected number of questions in Spanish returned.');
    }

    /**
     * Testing that there aren't any questions in French.
     */
    public function testGetQuestionsInFrench()
    {
        $limeSurveyApi = new \LimeSurvey\PluginManager\LimesurveyApi();

        $questionsInFrench = $limeSurveyApi->getQuestions(self::$surveyId, 'fr');

        $this->assertEmpty($questionsInFrench, 'Unexpected number of questions in French returned.');
    }
}
