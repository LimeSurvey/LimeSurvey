<?php

namespace ls\tests;

/**
 * Tests for the print-answers data built by SurveyDynamic::getPrintAnswersArray().
 */
class PrintAnswersArrayTest extends TestBaseClass
{
    /** @var integer */
    private static $responseId;

    /** @var integer */
    private static $q1Qid;

    /** @var integer */
    private static $nameQid;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $_SESSION = [];
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_381354_print_answers_conditions.lss');
        $activator = new \SurveyActivator(self::$testSurvey);
        $activator->activate();

        // Cast to int: some drivers (e.g. MSSQL) return IDs as strings, while answerArray keys are integers
        self::$q1Qid = (int) \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'q1'])->qid;
        self::$nameQid = (int) \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'name'])->qid;
        self::$responseId = \SurveyDynamic::model(self::$surveyId)->insertRecords([
            'startlanguage' => 'en',
            'startdate' => date('Y-m-d H:i:s'),
            'datestamp' => date('Y-m-d H:i:s'),
            'Q' . self::$q1Qid => 'N',
        ]);

        // Start the survey in EM and mark the "name" question as not relevant (q1 was answered "No")
        \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            'group',
            self::$testHelper->getSurveyOptions(self::$surveyId),
            false,
            0
        );
        $_SESSION['responses_' . self::$surveyId]['relevanceStatus'][self::$nameQid] = 0;
    }

    public static function tearDownAfterClass(): void
    {
        $_SESSION = [];
        parent::tearDownAfterClass();
    }

    /**
     * When conditions are honored, the irrelevant question must be left out.
     *
     * @return void
     */
    public function testHonorConditionsSkipsIrrelevantQuestion(): void
    {
        $groups = \SurveyDynamic::model(self::$surveyId)->getPrintAnswersArray(self::$responseId, 'en', true);

        $questions = $this->getQuestionIds($groups);
        $this->assertContains(self::$q1Qid, $questions);
        $this->assertNotContains(self::$nameQid, $questions);
    }

    /**
     * When conditions are not honored ("printanswershonorsconditions" set to 0), all questions must be listed.
     *
     * @see https://bugs.limesurvey.org/view.php?id=15734
     * @return void
     */
    public function testIgnoreConditionsListsAllQuestions(): void
    {
        $groups = \SurveyDynamic::model(self::$surveyId)->getPrintAnswersArray(self::$responseId, 'en', false);

        $questions = $this->getQuestionIds($groups);
        $this->assertContains(self::$q1Qid, $questions);
        $this->assertContains(self::$nameQid, $questions);
    }

    /**
     * Collects the question IDs of all groups returned by getPrintAnswersArray()
     *
     * @param array $groups Group data as returned by getPrintAnswersArray()
     * @return integer[]
     */
    private function getQuestionIds(array $groups): array
    {
        $questionIds = [];
        foreach ($groups as $group) {
            $questionIds = array_merge($questionIds, array_keys($group['answerArray']));
        }
        return $questionIds;
    }
}
