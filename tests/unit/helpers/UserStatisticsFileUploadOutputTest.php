<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use Yii;

/**
 * Tests that userstatistics_helper::buildOutputList() (public statistics) only
 * queries real response table columns for file upload questions (mantis #20755).
 *
 * The public and the admin statistics helpers declare the same global functions,
 * so this test cannot share a process with tests that load the admin helper.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class UserStatisticsFileUploadOutputTest extends TestBaseClass
{
    /** @var \Question File upload question of the test survey */
    private static $question;

    /**
     * Loads the public statistics helper and imports the test survey. This runs in the
     * isolated test process only, unlike setUpBeforeClass(), which also runs in the parent.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Yii::app()->loadHelper('userstatistics');
        Yii::app()->loadHelper('common');

        do {
            $surveyId = random_int(100000, 999999);
        } while (\Survey::model()->findByPk($surveyId) !== null);
        Yii::app()->session['loginID'] = 1;
        \Survey::model()->resetCache();
        $result = \importSurveyFile(self::$surveysFolder . '/limesurvey_survey_561859.lss', false, null, $surveyId);
        if (empty($result) || !empty($result['error'])) {
            throw new \Exception('Could not import limesurvey_survey_561859.lss: ' . ($result['error'] ?? ''));
        }
        \Survey::model()->resetCache();
        self::$surveyId = $result['newsid'];
        self::$testSurvey = \Survey::model()->findByPk(self::$surveyId);
        self::$testHelper->activateSurvey(self::$surveyId);

        self::$question = \Question::model()->findByAttributes([
            'sid' => self::$surveyId,
            'type' => \Question::QT_VERTICAL_FILE_UPLOAD,
            'parent_qid' => 0,
        ]);
    }

    /**
     * Calls the protected userstatistics_helper::buildOutputList() for a summary entry.
     *
     * @param string $rt Summary entry, as sent in the summary[] request parameter
     * @return array
     */
    private function buildOutputList(string $rt): array
    {
        $language = self::$testSurvey->language;
        $method = new \ReflectionMethod(\userstatistics_helper::class, 'buildOutputList');
        $method->setAccessible(true);

        return $method->invoke(new \userstatistics_helper(), $rt, $language, self::$surveyId, 'html', '', $language);
    }

    /**
     * A regular file upload summary entry outputs the file statistics of the question.
     */
    public function testFileUploadSummary()
    {
        $this->assertNotNull(self::$question, 'The test survey has no file upload question.');

        $output = $this->buildOutputList('|Q' . self::$question->qid);

        $this->assertSame((int) self::$question->qid, $output['parentqid']);
        $this->assertStringContainsString(gT("Total number of files"), $output['statisticsoutput']);
    }

    /**
     * A tampered summary entry must be rejected before any query is built from it.
     */
    public function testFileUploadRejectsUnknownColumns()
    {
        $this->assertNotNull(self::$question, 'The test survey has no file upload question.');

        $qid = self::$question->qid;
        foreach (['|3|`)--+-', '|' . $qid . '|`)--+-', '|Q' . $qid . '`x', '|Q' . $qid . '_Cfilecount'] as $rt) {
            $this->assertSame([], $this->buildOutputList($rt), $rt);
        }
    }
}
