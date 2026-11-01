<?php

namespace ls\tests\unit\services\QuestionGroup;

use Condition;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\Exception\QuestionGroupHasConditionsException;
use LimeSurvey\Models\Services\QuestionGroupService;
use ls\tests\TestBaseClass;
use Question;
use QuestionGroup;

/**
 * Deleting a question group must not leave conditions of other groups behind
 * that point to the deleted questions.
 */
class QuestionGroupDeleteDependentConditionsTest extends TestBaseClass
{
    /** @var QuestionGroupService */
    private $questionGroupService;

    /**
     * Import a survey where question G02Q02 of the second group has a condition on Q00 of the first group.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::importSurvey(self::$surveysFolder . '/limesurvey_survey_allinoneCondition.lss');
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->questionGroupService = DI::getContainer()->get(QuestionGroupService::class);
    }

    /**
     * A group with questions that other groups have conditions on is not deleted.
     *
     * @return void
     */
    public function testDeleteGroupWithDependentConditionsIsRefused()
    {
        $targetQuestion = $this->getQuestionByTitle('Q00');
        $dependentQuestion = $this->getQuestionByTitle('G02Q02');
        $relevance = $dependentQuestion->relevance;

        try {
            $this->questionGroupService->deleteGroup((int) $targetQuestion->gid, self::$surveyId);
            $this->fail('Group with dependent conditions was deleted.');
        } catch (QuestionGroupHasConditionsException $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        $this->assertNotNull(QuestionGroup::model()->findByPk($targetQuestion->gid));
        $this->assertNotNull(Question::model()->findByPk($targetQuestion->qid));
        $this->assertSame(
            1,
            (int) Condition::model()->countByAttributes(['qid' => $dependentQuestion->qid, 'cqid' => $targetQuestion->qid])
        );
        $this->assertSame($relevance, $this->getQuestionByTitle('G02Q02')->relevance);
    }

    /**
     * The group holding the conditions itself can still be deleted, together with its conditions.
     *
     * @depends testDeleteGroupWithDependentConditionsIsRefused
     * @return void
     */
    public function testDeleteGroupHoldingConditionsIsAllowed()
    {
        $dependentQuestion = $this->getQuestionByTitle('G02Q02');

        $deleted = $this->questionGroupService->deleteGroup((int) $dependentQuestion->gid, self::$surveyId);

        $this->assertSame(1, (int) $deleted);
        $this->assertNull(QuestionGroup::model()->findByPk($dependentQuestion->gid));
        $this->assertSame(0, (int) Condition::model()->countByAttributes(['qid' => $dependentQuestion->qid]));
    }

    /**
     * @param string $title
     * @return Question
     */
    private function getQuestionByTitle(string $title): Question
    {
        $question = Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => $title, 'parent_qid' => 0]);
        $this->assertNotNull($question, "Question $title not found.");
        return $question;
    }
}
