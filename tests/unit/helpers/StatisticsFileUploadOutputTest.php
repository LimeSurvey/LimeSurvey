<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use Yii;

/**
 * Tests that statistics_helper::buildOutputList() only queries real response
 * table columns for file upload questions (mantis #20755).
 */
class StatisticsFileUploadOutputTest extends TestBaseClass
{
    /** @var \Question File upload question of the test survey */
    private static $question;

    public static function setUpBeforeClass(): void
    {
        Yii::app()->loadHelper('admin.statistics');
        Yii::app()->loadHelper('common');

        parent::setUpBeforeClass();

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
     * Calls the protected statistics_helper::buildOutputList() for a summary entry.
     *
     * @param string $rt Summary entry, as sent in the summary[] request parameter
     * @return array
     */
    private function buildOutputList(string $rt): array
    {
        $language = \Survey::model()->findByPk(self::$surveyId)->language;
        $method = new \ReflectionMethod(\statistics_helper::class, 'buildOutputList');

        return $method->invoke(new \statistics_helper(), $rt, $language, self::$surveyId, 'html', '', $language);
    }

    /**
     * Returns the result shown for a calculation in the file upload summary table.
     *
     * @param array $output Output of buildOutputList()
     * @param string $label Untranslated calculation label
     * @return string|null Null if the calculation is not in the output
     */
    private function resultCell(array $output, string $label): ?string
    {
        $pattern = '#<tr><td>' . preg_quote(gT($label), '#') . '</td><td>([^<]*)</td></tr>#';

        return preg_match($pattern, $output['statisticsoutput'], $matches) ? $matches[1] : null;
    }

    /**
     * A regular file upload summary entry outputs the file statistics of the question, both
     * without responses and with one response with uploaded files and one without.
     */
    public function testFileUploadSummary()
    {
        $this->assertNotNull(self::$question, 'The test survey has no file upload question.');
        $fieldname = 'Q' . self::$question->qid;

        $output = $this->buildOutputList('|' . $fieldname);

        $this->assertSame((int) self::$question->qid, $output['parentqid']);
        $this->assertSame('0 KB', $this->resultCell($output, 'Total size of files'));
        $this->assertSame('0 KB', $this->resultCell($output, 'Average file size'));
        $this->assertSame('0 KB', $this->resultCell($output, 'Average size per respondent'));

        $dateStamp = date('Y-m-d H:i:s');
        $files = [['name' => 'a.txt', 'size' => '100'], ['name' => 'b.txt', 'size' => '200']];
        \SurveyDynamic::model(self::$surveyId)->insertRecords([
            'startlanguage' => self::$testSurvey->language,
            'startdate' => $dateStamp,
            'datestamp' => $dateStamp,
            $fieldname => json_encode($files),
            $fieldname . '_Cfilecount' => 2,
        ]);
        \SurveyDynamic::model(self::$surveyId)->insertRecords([
            'startlanguage' => self::$testSurvey->language,
            'startdate' => $dateStamp,
            'datestamp' => $dateStamp,
            $fieldname . '_Cfilecount' => 0,
        ]);

        $output = $this->buildOutputList('|' . $fieldname);

        // The sum comes from the database, so its number format depends on the database.
        $this->assertEquals(2, $this->resultCell($output, 'Total number of files'));
        $this->assertSame('300 KB', $this->resultCell($output, 'Total size of files'));
        $this->assertSame('150 KB', $this->resultCell($output, 'Average file size'));
        $this->assertSame('150 KB', $this->resultCell($output, 'Average size per respondent'));
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
