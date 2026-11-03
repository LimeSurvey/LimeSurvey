<?php

namespace ls\tests;

use Question;
use QuestionGroup;
use SurveyURLParameter;

/**
 * Deleting a question must not leave panel integration URL parameters
 * pointing to a question that does not exist anymore.
 */
class SurveyURLParameterTargetCleanupTest extends TestBaseClass
{
    /**
     * Import the test survey (q2: Multiple choice with subquestions SQ001-SQ003, q3: Long text in another group).
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.helpers.common_helper', true);
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_583999.lss';
        self::importSurvey($surveyFile);
    }

    /**
     * Deleting a subquestion removes the target from parameters pointing to it, and only from those.
     *
     * @return void
     */
    public function testDeletingSubquestionRemovesItsTarget()
    {
        $question = $this->getQuestion('q2');
        $subquestions = $question->subquestions;
        $deleted = $subquestions[0];
        $kept = $subquestions[1];

        $toDeleted = $this->createParameter('tosubquestion', $question->qid, $deleted->qid);
        $toKept = $this->createParameter('toothersubquestion', $question->qid, $kept->qid);

        $this->assertTrue($deleted->delete());

        $toDeleted->refresh();
        $this->assertSame('tosubquestion', $toDeleted->parameter, 'The parameter itself must be kept');
        $this->assertNull($toDeleted->targetqid);
        $this->assertNull($toDeleted->targetsqid);
        $this->assertSame('', $toDeleted->getQuestionTitle());

        $toKept->refresh();
        $this->assertEquals($question->qid, $toKept->targetqid);
        $this->assertEquals($kept->qid, $toKept->targetsqid);
    }

    /**
     * Deleting a question removes the target from parameters pointing to it or to one of its subquestions.
     *
     * @return void
     */
    public function testDeletingQuestionRemovesItsTargets()
    {
        $question = $this->getQuestion('q2');
        $subquestion = $question->subquestions[0];
        $otherQuestion = $this->getQuestion('q3');

        $toQuestion = $this->createParameter('toquestion', $question->qid);
        $toSubquestion = $this->createParameter('tosubquestionofdeleted', $question->qid, $subquestion->qid);
        $toOtherQuestion = $this->createParameter('tootherquestion', $otherQuestion->qid);
        $withoutTarget = $this->createParameter('withouttarget');

        $this->assertTrue($question->delete());

        foreach ([$toQuestion, $toSubquestion] as $parameter) {
            $parameter->refresh();
            $this->assertNull($parameter->targetqid);
            $this->assertNull($parameter->targetsqid);
        }
        $toOtherQuestion->refresh();
        $this->assertEquals($otherQuestion->qid, $toOtherQuestion->targetqid);
        $this->assertNotNull(SurveyURLParameter::model()->findByPk($withoutTarget->id));
    }

    /**
     * Deleting a question group removes the target from parameters pointing to its questions.
     *
     * @depends testDeletingQuestionRemovesItsTargets
     * @return void
     */
    public function testDeletingGroupRemovesTargets()
    {
        $question = $this->getQuestion('q3');
        $parameter = $this->createParameter('toquestioningroup', $question->qid);

        $this->assertNotEmpty(QuestionGroup::deleteWithDependency($question->gid, self::$surveyId));

        $parameter->refresh();
        $this->assertNull($parameter->targetqid);
        $this->assertNull($parameter->targetsqid);
    }

    /**
     * Find a question of the test survey by its code.
     *
     * @param string $title Question code
     * @return Question
     */
    private function getQuestion(string $title): Question
    {
        $question = Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => $title, 'parent_qid' => 0]);
        $this->assertNotEmpty($question, "Question $title not found");
        return $question;
    }

    /**
     * Create a URL parameter for the test survey.
     *
     * @param string $name Parameter name
     * @param int|null $targetQid Target question ID
     * @param int|null $targetSqid Target subquestion ID
     * @return SurveyURLParameter
     */
    private function createParameter(string $name, ?int $targetQid = null, ?int $targetSqid = null): SurveyURLParameter
    {
        $parameter = new SurveyURLParameter();
        $parameter->sid = self::$surveyId;
        $parameter->parameter = $name;
        $parameter->targetqid = $targetQid;
        $parameter->targetsqid = $targetSqid;
        $this->assertTrue($parameter->save(), print_r($parameter->getErrors(), true));
        return $parameter;
    }
}
