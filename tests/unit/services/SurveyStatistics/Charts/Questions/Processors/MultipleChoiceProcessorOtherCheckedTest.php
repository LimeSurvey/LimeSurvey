<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\MultipleChoiceProcessor;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use ls\tests\TestBaseClass;
use Question;

class MultipleChoiceProcessorOtherCheckedTest extends TestBaseClass
{
    use ResponseAggregateBatchTestHelper;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_213203_other_checked.lss');
        self::$testHelper->activateSurvey(self::$surveyId);
    }

    /**
     * @testdox process() counts Other with text or ticked when the response table has the column
     */
    public function testOtherCountsTextOrTickWithColumn()
    {
        $question = $this->getQuestion();
        $rt = 'Q' . $question['qid'];
        $batch = new ResponseAggregateBatch(self::$surveyId);
        $processor = new MultipleChoiceProcessor();
        $processor->setQuestion($question);
        $processor->setBatch($batch);

        $plan = $processor->process();

        $other = end($plan['data']);
        // The comments of the "other" item are still read from the text column.
        $this->assertSame($rt . '_Cother', $other['field']);
        $aliasOther = $batch->countAnyNonEmpty([$rt . '_Cother', $rt . '_Cotherchecked']);
        $this->injectBatchResults($batch, [$aliasOther => 4]);
        $this->assertSame(4, $other['value']());
    }

    /**
     * @testdox process() counts only Other with text when the survey was activated before the column existed
     */
    public function testOtherCountsOnlyTextWithoutColumn()
    {
        $question = $this->getQuestion();
        $rt = 'Q' . $question['qid'];
        $tableName = \Survey::model()->findByPk(self::$surveyId)->responsesTableName;
        \Yii::app()->db->createCommand()->dropColumn($tableName, $rt . '_Cotherchecked');
        \Yii::app()->db->schema->getTable($tableName, true);

        $batch = new ResponseAggregateBatch(self::$surveyId);
        $processor = new MultipleChoiceProcessor();
        $processor->setQuestion($question);
        $processor->setBatch($batch);

        $plan = $processor->process();

        $other = end($plan['data']);
        $aliasOther = $batch->countNonEmpty($rt . '_Cother');
        $this->injectBatchResults($batch, [$aliasOther => 2]);
        $this->assertSame(2, $other['value']());
    }

    /**
     * @return array the not mandatory question with Other, as the processors get it
     */
    private function getQuestion(): array
    {
        $question = Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'q1']);
        $subQuestions = [];
        foreach ($question->subquestions as $subQuestion) {
            $subQuestions[] = ['qid' => $subQuestion->qid, 'title' => $subQuestion->title, 'question' => $subQuestion->title];
        }
        return [
            'sid' => self::$surveyId,
            'gid' => $question->gid,
            'qid' => $question->qid,
            'title' => $question->title,
            'type' => Question::QT_M_MULTIPLE_CHOICE,
            'question' => 'Which options apply?',
            'other' => Question::QT_Y_YES_NO_RADIO,
            'subQuestions' => $subQuestions,
        ];
    }
}
