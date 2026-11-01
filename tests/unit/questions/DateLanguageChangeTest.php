<?php

namespace ls\tests;

/**
 * Dates posted from a page in the previous language must be read with the date format of that language.
 * Survey base language is French (dd/mm/yyyy), additional language is English (mm-dd-yyyy).
 * @group date
 */
class DateLanguageChangeTest extends TestBaseClass
{
    /**
     * Import the survey
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_POST = [];
        $_SESSION = [];

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_19247_date_language_change.lss';
        self::importSurvey($surveyFile);
    }

    /**
     * Reset the posted language, since it is static in LimeExpressionManager
     * @return void
     */
    protected function tearDown(): void
    {
        \LimeExpressionManager::setPostedLanguage(null);
        $_POST = [];
        parent::tearDown();
    }

    /**
     * Start the survey in French and post an English formatted date for question D
     * @param string|null $postedLanguage Language of the page the date was entered on
     * @return string The value of question D after processing the posted answers
     */
    private function processEnglishDate($postedLanguage)
    {
        list($question, $group, $sgqa) = self::$testHelper->getSgqa('D', self::$surveyId);

        \LimeExpressionManager::SetEMLanguage('fr');
        \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            'survey',
            self::$testHelper->getSurveyOptions(self::$surveyId),
            false,
            0
        );
        \LimeExpressionManager::singleton()->setCurrentQset([
            $question->qid => [
                'info' => [
                    'qid' => $question->qid,
                    'gseq' => 0,
                    'type' => 'D',
                    'hidden' => false,
                ],
                'sgqa' => $sgqa,
            ]
        ]);
        \LimeExpressionManager::setPostedLanguage($postedLanguage);

        $_POST['relevance' . $question->qid] = 1;
        $_POST['relevanceG0'] = 1;
        $_POST[$sgqa] = '11-25-2023';

        $result = \LimeExpressionManager::ProcessCurrentResponses();
        return $result[$sgqa]['value'];
    }

    /**
     * After changing the language from English to French, the English date is kept
     * @return void
     */
    public function testDateFromPreviousLanguage()
    {
        $this->assertEquals('2023-11-25 00:00', $this->processEnglishDate('en'));
    }

    /**
     * Without a language change, the date is read in the current (French) date format
     * @return void
     */
    public function testDateFromCurrentLanguage()
    {
        $this->assertEquals('INVALID', $this->processEnglishDate(null));
    }
}
