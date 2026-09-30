<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use Yii;

/**
 * Tests that statistics_helper::buildOutputList() only returns real response
 * table columns for multiple-choice questions (mantis #20744).
 */
class StatisticsMultipleChoiceOutputTest extends TestBaseClass
{
    /** @var \Question Multiple-choice question of the test survey */
    private static $question;

    public static function setUpBeforeClass(): void
    {
        Yii::app()->loadHelper('admin.statistics');
        Yii::app()->loadHelper('common');

        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/survey_simple_statistics.lsa');

        self::$question = \Question::model()->findByAttributes([
            'sid' => self::$surveyId,
            'type' => \Question::QT_M_MULTIPLE_CHOICE,
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
        $method->setAccessible(true);

        return $method->invoke(new \statistics_helper(), $rt, $language, self::$surveyId, 'html', '', $language);
    }

    /**
     * A regular multiple-choice summary entry lists one response column per subquestion.
     */
    public function testMultipleChoiceListsResponseColumns()
    {
        $this->assertNotNull(self::$question, 'The test survey has no multiple-choice question.');

        $output = $this->buildOutputList('MQ' . self::$question->qid);
        $columns = \SurveyDynamic::model(self::$surveyId)->getTableSchema()->getColumnNames();

        $this->assertNotEmpty($output['alist']);
        foreach ($output['alist'] as $answer) {
            $this->assertContains($answer[2], $columns);
        }
    }

    /**
     * A tampered summary entry must not produce a column that is not in the response table.
     */
    public function testMultipleChoiceDropsUnknownColumns()
    {
        $this->assertNotNull(self::$question, 'The test survey has no multiple-choice question.');

        $output = $this->buildOutputList('MQ' . self::$question->qid . '`x');

        $this->assertSame([], $output['alist']);
    }
}
