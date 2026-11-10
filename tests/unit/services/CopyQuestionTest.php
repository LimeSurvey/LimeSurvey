<?php

namespace ls\tests;

use LimeSurvey\Datavalueobjects\CopyQuestionValues;
use LimeSurvey\Models\Services\CopyQuestion;

/**
 * Tests for the CopyQuestion service.
 */
class CopyQuestionTest extends TestBaseClass
{
    /**
     * Import a survey with a multiple choice question that has subquestions.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $filename = self::$surveysFolder . '/limesurvey_survey_425647_subquestion_relevance_edit.lss';
        self::importSurvey($filename);
    }

    /**
     * A subquestion that fails validation is reported, and the other subquestions are still copied.
     *
     * @return void
     */
    public function testFailedSubquestionIsReportedAndOthersAreCopied()
    {
        $questions = $this->getAllSurveyQuestions();
        $sourceQuestion = $questions['Q01'];
        $sourceSubquestions = \Question::model()->findAllByAttributes(
            ['parent_qid' => $sourceQuestion->qid],
            ['order' => 'qid']
        );
        $this->assertGreaterThan(1, count($sourceSubquestions));

        // 'time' is a reserved subquestion code, write it without validation to get a subquestion that can't be copied.
        $invalidSubquestion = $sourceSubquestions[0];
        \Question::model()->updateByPk($invalidSubquestion->qid, ['title' => 'time']);

        $copyQuestionValues = new CopyQuestionValues();
        $copyQuestionValues->setOSurvey(self::$testSurvey);
        $copyQuestionValues->setQuestionCode('Q01copy');
        $copyQuestionValues->setQuestionGroupId($sourceQuestion->gid);
        $copyQuestionValues->setQuestiontoCopy(\Question::model()->findByPk($sourceQuestion->qid));
        $copyQuestionValues->setQuestionL10nData([]);
        $copyQuestionValues->setQuestionPositionInGroup(\Question::getHighestQuestionOrderNumberInGroup($sourceQuestion->gid) + 1);
        $copyQuestion = new CopyQuestion($copyQuestionValues, ['copySubquestions' => true]);

        $this->assertTrue($copyQuestion->copyQuestion());

        $copyErrors = $copyQuestion->getCopyErrors();
        $this->assertCount(1, $copyErrors);
        $this->assertStringContainsString("Subquestion &#039;time&#039; could not be copied", $copyErrors[0]);

        $newQuestion = $copyQuestion->getNewCopiedQuestion();
        $copiedSubquestions = \Question::model()->findAllByAttributes(['parent_qid' => $newQuestion->qid]);
        $this->assertCount(count($sourceSubquestions) - 1, $copiedSubquestions);
        foreach ($copiedSubquestions as $copiedSubquestion) {
            $this->assertNotEmpty($copiedSubquestion->questionl10ns);
        }

        $mappedSubquestionIds = $copyQuestion->getMappedSubquestionIds();
        $this->assertArrayNotHasKey($invalidSubquestion->qid, $mappedSubquestionIds);
        $this->assertCount(count($sourceSubquestions) - 1, $mappedSubquestionIds);
    }
}
