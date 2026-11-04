<?php

namespace ls\tests;

use Condition;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\GroupHelper;
use LimeSurvey\Models\Services\QuestionAggregateService\AttributesService;
use LimeSurvey\Models\Services\QuestionAggregateService\QuestionService;
use LimeSurvey\Models\Services\QuickTranslation;
use LimeSurvey\Models\Services\Quotas;
use Question;
use QuestionGroup;
use QuestionGroupL10n;
use QuestionL10n;
use Quota;
use QuotaMember;
use Survey;

/**
 * IDs of groups, questions, quotas and conditions of another survey must never be
 * accepted when the permission was checked on the current survey.
 *
 * @group services
 */
class CrossSurveyObjectIdsTest extends TestBaseClass
{
    /** @var int Survey the actions are done on */
    private static $ownSurveyId;

    /** @var int Survey whose objects must stay untouched */
    private static $foreignSurveyId;

    /**
     * Imports the same survey twice: once as "own" and once as "foreign" survey.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_376789_quotas.lss';
        self::importSurvey($surveyFile);
        self::$foreignSurveyId = self::$surveyId;
        self::importSurvey($surveyFile);
        self::$ownSurveyId = self::$surveyId;
    }

    /**
     * Deletes the foreign survey; the own survey is deleted by the parent.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        \Yii::app()->session['loginID'] = 1;
        $foreignSurvey = Survey::model()->findByPk(self::$foreignSurveyId);
        if ($foreignSurvey) {
            $foreignSurvey->delete();
        }
        parent::tearDownAfterClass();
    }

    /**
     * @testdox quota massive actions skip quotas of another survey
     * @return void
     */
    public function testQuotaMassiveActionSkipsForeignQuota()
    {
        $foreignQuota = Quota::model()->find('sid = :sid', [':sid' => self::$foreignSurveyId]);
        $this->assertNotNull($foreignQuota, 'Test survey has no quota');
        $quotaService = new Quotas(Survey::model()->findByPk(self::$ownSurveyId));

        $errors = $quotaService->multipleItemsAction([$foreignQuota->id], Quotas::MASSIVE_ACTION_DEACTIVATE);
        $this->assertNotEmpty($errors);
        $this->assertSame((int) $foreignQuota->active, (int) Quota::model()->findByPk($foreignQuota->id)->active);

        $errors = $quotaService->multipleItemsAction([$foreignQuota->id], Quotas::MASSIVE_ACTION_DELETE);
        $this->assertNotEmpty($errors);
        $this->assertNotNull(Quota::model()->findByPk($foreignQuota->id));
    }

    /**
     * @testdox the quota overview leaves out members based on a question of another survey
     * @return void
     */
    public function testQuotaStructureHidesForeignQuestionMembers()
    {
        $ownQuota = Quota::model()->find('sid = :sid', [':sid' => self::$ownSurveyId]);
        $foreignQuestion = $this->getFirstQuestion(self::$foreignSurveyId);
        $quotaMember = new QuotaMember();
        $quotaMember->sid = self::$ownSurveyId;
        $quotaMember->qid = $foreignQuestion->qid;
        $quotaMember->quota_id = $ownQuota->id;
        $quotaMember->code = 'A1';
        $this->assertTrue($quotaMember->save());

        try {
            $quotaStructure = (new Quotas(Survey::model()->findByPk(self::$ownSurveyId)))->getQuotaStructure();
            foreach ($quotaStructure['aQuotaItems'][$ownQuota->id] ?? [] as $quotaItem) {
                $this->assertNotEquals($quotaMember->id, $quotaItem['oQuotaMember']->id);
            }
            $this->assertNotEmpty($quotaStructure['aQuotaItems'], 'The members of the own survey are still shown');
        } finally {
            $quotaMember->delete();
        }
    }

    /**
     * @testdox quick translation does not change texts of another survey
     * @return void
     */
    public function testQuickTranslationIgnoresForeignTexts()
    {
        $quickTranslation = new QuickTranslation(Survey::model()->findByPk(self::$ownSurveyId));
        $foreignQuestion = $this->getFirstQuestion(self::$foreignSurveyId);
        $foreignQuestionText = $this->getQuestionText($foreignQuestion->qid);
        $foreignGroupName = $this->getGroupName($foreignQuestion->gid);

        $this->assertSame(0, $quickTranslation->updateTranslations('question', 'en', 'Changed', $foreignQuestion->qid));
        $this->assertSame(0, $quickTranslation->updateTranslations('group', 'en', 'Changed', $foreignQuestion->gid));
        $this->assertSame($foreignQuestionText, $this->getQuestionText($foreignQuestion->qid));
        $this->assertSame($foreignGroupName, $this->getGroupName($foreignQuestion->gid));

        $ownQuestion = $this->getFirstQuestion(self::$ownSurveyId);
        $this->assertSame(1, $quickTranslation->updateTranslations('question', 'en', 'Own text', $ownQuestion->qid));
        $this->assertSame('Own text', $this->getQuestionText($ownQuestion->qid));
    }

    /**
     * @testdox survey reorganization ignores groups and questions of another survey
     * @return void
     */
    public function testReorderGroupIgnoresForeignObjects()
    {
        $ownQuestion = $this->getFirstQuestion(self::$ownSurveyId);
        $foreignQuestion = $this->getFirstQuestion(self::$foreignSurveyId);
        $foreignGroup = QuestionGroup::model()->findByPk($foreignQuestion->gid);

        (new GroupHelper())->reorderGroup(self::$ownSurveyId, [
            'g' . $foreignGroup->gid => 'root',
            'q' . $foreignQuestion->qid => 'g' . $ownQuestion->gid,
            'q' . $ownQuestion->qid => 'g' . $foreignGroup->gid,
        ]);

        $this->assertSame((int) $foreignGroup->group_order, (int) QuestionGroup::model()->findByPk($foreignGroup->gid)->group_order);
        $this->assertSame((int) $foreignQuestion->gid, (int) Question::model()->findByPk($foreignQuestion->qid)->gid);
        $this->assertSame((int) $ownQuestion->gid, (int) Question::model()->findByPk($ownQuestion->qid)->gid);
    }

    /**
     * @testdox deleting a group checks that it belongs to the given survey
     * @return void
     */
    public function testDeleteGroupRejectsForeignGroup()
    {
        $foreignGroup = QuestionGroup::model()->find('sid = :sid', [':sid' => self::$foreignSurveyId]);

        try {
            QuestionGroup::deleteWithDependency($foreignGroup->gid, self::$ownSurveyId);
            $this->fail('Expected NotFoundException for a group of another survey');
        } catch (NotFoundException $e) {
            // expected
        }
        $this->assertNotNull(QuestionGroup::model()->findByPk($foreignGroup->gid));
    }

    /**
     * @testdox a condition can't be based on a question of another survey
     * @return void
     */
    public function testConditionRejectsForeignSourceQuestion()
    {
        $ownQuestions = Question::model()->findAll(['condition' => 'sid = :sid AND parent_qid = 0', 'params' => [':sid' => self::$ownSurveyId], 'order' => 'qid']);
        $foreignQuestion = $this->getFirstQuestion(self::$foreignSurveyId);
        $conditionData = [
            'qid' => $ownQuestions[1]->qid,
            'scenario' => 1,
            'cqid' => $foreignQuestion->qid,
            'cfieldname' => 'Q' . $foreignQuestion->qid,
            'method' => '==',
            'value' => 'A1',
        ];

        $this->assertFalse(Condition::model()->insertRecords($conditionData));
        $condition = new Condition();
        $condition->setAttributes($conditionData, false);
        $this->assertFalse($condition->save());
        $this->assertSame('0', (string) Condition::model()->countByAttributes(['cqid' => $foreignQuestion->qid]));

        $conditionData['cqid'] = $ownQuestions[0]->qid;
        $conditionData['cfieldname'] = 'Q' . $ownQuestions[0]->qid;
        $this->assertTrue(Condition::model()->insertRecords($conditionData));
        Condition::model()->deleteAllByAttributes(['qid' => $ownQuestions[1]->qid]);
    }

    /**
     * @testdox the question attributes don't move a question to another survey
     * @return void
     */
    public function testAttributesServiceKeepsSurveyAndGroup()
    {
        $ownQuestion = $this->getFirstQuestion(self::$ownSurveyId);
        $foreignQuestion = $this->getFirstQuestion(self::$foreignSurveyId);

        DI::getContainer()->get(AttributesService::class)->save(
            $ownQuestion,
            ['sid' => self::$foreignSurveyId, 'gid' => $foreignQuestion->gid, 'parent_qid' => $foreignQuestion->qid]
        );

        $savedQuestion = Question::model()->findByPk($ownQuestion->qid);
        $this->assertSame(self::$ownSurveyId, (int) $savedQuestion->sid);
        $this->assertSame((int) $ownQuestion->gid, (int) $savedQuestion->gid);
        $this->assertSame(0, (int) $savedQuestion->parent_qid);
    }

    /**
     * @testdox saving a question refuses a group or parent question of another survey
     * @return void
     */
    public function testQuestionServiceRejectsForeignGroupAndParent()
    {
        $ownQuestion = $this->getFirstQuestion(self::$ownSurveyId);
        $foreignQuestion = $this->getFirstQuestion(self::$foreignSurveyId);
        $questionService = DI::getContainer()->get(QuestionService::class);

        foreach (['gid' => $foreignQuestion->gid, 'parent_qid' => $foreignQuestion->qid] as $key => $foreignId) {
            try {
                $questionService->save([
                    'question' => ['qid' => $ownQuestion->qid, 'sid' => self::$ownSurveyId, $key => $foreignId],
                ]);
                $this->fail("Expected NotFoundException for a $key of another survey");
            } catch (NotFoundException $e) {
                // expected
            }
        }
        $savedQuestion = Question::model()->findByPk($ownQuestion->qid);
        $this->assertSame((int) $ownQuestion->gid, (int) $savedQuestion->gid);
        $this->assertSame(0, (int) $savedQuestion->parent_qid);

        try {
            $questionService->save([
                'question' => ['sid' => self::$ownSurveyId, 'gid' => $foreignQuestion->gid, 'title' => 'NewQ', 'type' => 'T'],
            ]);
            $this->fail('Expected NotFoundException for a new question in a group of another survey');
        } catch (NotFoundException $e) {
            // expected
        }
        $this->assertSame('0', (string) Question::model()->countByAttributes(['gid' => $foreignQuestion->gid, 'sid' => self::$ownSurveyId]));
    }

    /**
     * Returns the first main question of a survey.
     *
     * @param int $surveyId
     * @return Question
     */
    private function getFirstQuestion(int $surveyId): Question
    {
        $question = Question::model()->find(['condition' => 'sid = :sid AND parent_qid = 0', 'params' => [':sid' => $surveyId], 'order' => 'qid']);
        $this->assertNotNull($question, 'Test survey has no question');
        return $question;
    }

    /**
     * Returns the English question text of a question.
     *
     * @param int $questionId
     * @return string|null
     */
    private function getQuestionText(int $questionId): ?string
    {
        $l10n = QuestionL10n::model()->findByAttributes(['qid' => $questionId, 'language' => 'en']);
        return $l10n ? $l10n->question : null;
    }

    /**
     * Returns the English name of a question group.
     *
     * @param int $groupId
     * @return string|null
     */
    private function getGroupName(int $groupId): ?string
    {
        $l10n = QuestionGroupL10n::model()->findByAttributes(['gid' => $groupId, 'language' => 'en']);
        return $l10n ? $l10n->group_name : null;
    }
}
