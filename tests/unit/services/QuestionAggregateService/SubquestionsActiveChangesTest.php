<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use LimeSurvey\DI;
use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\QuestionAggregateService\AttributesService;
use LimeSurvey\Models\Services\QuestionAggregateService\L10nService;
use LimeSurvey\Models\Services\QuestionAttributeHelper;
use LimeSurvey\Models\Services\QuestionAggregateService\SubQuestionsService;
use ls\tests\TestBaseClass;
use Permission;
use Question;
use QuestionAttribute;
use QuestionL10n;
use Survey;

/**
 * Adding and removing subquestions after the survey was activated, allowed by the
 * subquestionsactiveedit tag of the question theme (ranking only).
 *
 * @group services
 */
class SubquestionsActiveChangesTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_157447_array_filter_ranking_max_columns.lss');
        $activator = new \SurveyActivator(self::$testSurvey);
        $activator->activate();
        self::$testSurvey->refresh();
    }

    public function setUp(): void
    {
        parent::setUp();
        // Other tests replace the models in the container by mocks
        DI::getContainer()->set(Question::class, \DI\autowire(Question::class));
        DI::getContainer()->set(QuestionL10n::class, \DI\autowire(QuestionL10n::class));
    }

    /**
     * Returns the service with the real models, the container may hold one with mocks of other tests
     * @return SubQuestionsService
     */
    private function getSubQuestionsService(): SubQuestionsService
    {
        return new SubQuestionsService(
            new L10nService(QuestionL10n::model()),
            Question::model(),
            Survey::model(),
            Permission::model()
        );
    }

    /**
     * Returns the first question of the test survey with the given type
     * @param string $type
     * @return Question
     */
    private function getQuestionByType(string $type): Question
    {
        return Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0, 'type' => $type]);
    }

    /**
     * Returns the subquestions of a question in the format of the question editors
     * @param Question $question
     * @return array
     */
    private function getSubquestionsData(Question $question): array
    {
        $subquestions = [];
        foreach ($question->subquestions as $subquestion) {
            $subquestions[$subquestion->qid][$subquestion->scale_id] = [
                'code' => $subquestion->title,
                'relevance' => $subquestion->relevance,
            ];
        }
        return $subquestions;
    }

    /**
     * @testdox The question theme XML sets subquestionsactiveedit
     */
    public function testQuestionThemeMetaDataReadsSetting()
    {
        $rootDir = \Yii::app()->getConfig('rootdir');
        $themes = [
            '/application/views/survey/questions/answer/ranking' => '1',
            '/themes/question/ranking_advanced/survey/questions/answer/ranking' => '1',
            '/application/views/survey/questions/answer/multiplechoice' => '0',
        ];
        foreach ($themes as $xmlFolder => $expected) {
            $settings = json_decode(\QuestionTheme::getQuestionMetaData($rootDir . $xmlFolder)['settings'], true);
            $this->assertSame($expected, $settings['subquestionsactiveedit'], $xmlFolder);
        }
    }

    /**
     * @testdox Only the ranking question type allows subquestion changes when active
     */
    public function testOnlyRankingAllowsChangesWhenActive()
    {
        $this->assertTrue(self::$testSurvey->isActive);
        $this->assertTrue($this->getQuestionByType(Question::QT_R_RANKING)->getAllowSubquestionChangesWhenActive());
        $this->assertFalse($this->getQuestionByType(Question::QT_M_MULTIPLE_CHOICE)->getAllowSubquestionChangesWhenActive());
    }

    /**
     * @testdox save() adds and removes ranking items of an active survey without changing the response table
     */
    public function testSaveAddsAndRemovesRankingItemsWhenActive()
    {
        $question = $this->getQuestionByType(Question::QT_R_RANKING);
        $columnsBefore = array_keys(\SurveyDynamic::model(self::$surveyId)->getTableSchema()->columns);
        $subquestions = $this->getSubquestionsData($question);
        $removedQid = array_key_last($subquestions);
        unset($subquestions[$removedQid]);
        $subquestions['new1'][0] = [
            'code' => 'NEW1',
            'subquestionl10n' => ['en' => 'New ranking item'],
        ];

        $this->getSubQuestionsService()->save($question, $subquestions);

        $this->assertNull(Question::model()->findByPk($removedQid));
        $newSubquestion = Question::model()->findByAttributes(['parent_qid' => $question->qid, 'title' => 'NEW1']);
        $this->assertNotNull($newSubquestion);
        $this->assertSame(Question::QT_R_RANKING, $newSubquestion->type);
        $this->assertEquals($question->gid, $newSubquestion->gid);
        \SurveyDynamic::model(self::$surveyId)->refreshMetaData();
        $this->assertSame($columnsBefore, array_keys(\SurveyDynamic::model(self::$surveyId)->getTableSchema()->columns));
    }

    /**
     * @testdox delete() removes a ranking item of an active survey
     */
    public function testDeleteRemovesRankingItemWhenActive()
    {
        \Yii::app()->session['loginID'] = 1;
        $question = $this->getQuestionByType(Question::QT_R_RANKING);
        $subquestion = $question->subquestions[0];

        $this->getSubQuestionsService()->delete(self::$surveyId, $subquestion->qid);

        $this->assertNull(Question::model()->findByPk($subquestion->qid));
    }

    /**
     * @testdox save() still refuses new subquestions of other question types when active
     */
    public function testSaveRefusesNewMultipleChoiceSubquestionWhenActive()
    {
        $question = $this->getQuestionByType(Question::QT_M_MULTIPLE_CHOICE);
        $subquestions = $this->getSubquestionsData($question);
        $subquestions['new1'][0] = [
            'code' => 'NEW1',
            'subquestionl10n' => ['en' => 'New option'],
        ];

        $this->expectException(NotFoundException::class);
        $this->getSubQuestionsService()->save($question, $subquestions);
    }

    /**
     * @testdox save() keeps omitted subquestions of other question types when active
     */
    public function testSaveKeepsOmittedMultipleChoiceSubquestionWhenActive()
    {
        $question = $this->getQuestionByType(Question::QT_M_MULTIPLE_CHOICE);
        $subquestions = $this->getSubquestionsData($question);
        $omittedQid = array_key_last($subquestions);
        unset($subquestions[$omittedQid]);

        $this->getSubQuestionsService()->save($question, $subquestions);

        $this->assertNotNull(Question::model()->findByPk($omittedQid));
    }

    /**
     * @testdox save() keeps the codes of existing ranking items when active
     */
    public function testSaveKeepsExistingRankingCodesWhenActive()
    {
        $question = $this->getQuestionByType(Question::QT_R_RANKING);
        $subquestions = $this->getSubquestionsData($question);
        $changedQid = array_key_first($subquestions);
        $originalCode = $subquestions[$changedQid][0]['code'];
        $subquestions[$changedQid][0]['code'] = 'CHANGED';

        $this->getSubQuestionsService()->save($question, $subquestions);

        $this->assertSame($originalCode, Question::model()->findByPk($changedQid)->title);
    }

    /**
     * @testdox max_subquestions of a ranking question can be changed when active
     */
    public function testMaxSubquestionsCanBeChangedWhenActive()
    {
        $question = $this->getQuestionByType(Question::QT_R_RANKING);
        $attributesService = new AttributesService(QuestionAttribute::model(), new QuestionAttributeHelper(), Survey::model());

        $attributesService->save($question, ['max_subquestions' => '3']);

        $attribute = QuestionAttribute::model()->find(
            "qid = :qid AND attribute = 'max_subquestions'",
            [':qid' => $question->qid]
        );
        $this->assertSame('3', $attribute->value);
    }

    /**
     * @testdox delete() still refuses to remove subquestions of other question types when active
     */
    public function testDeleteRefusesMultipleChoiceSubquestionWhenActive()
    {
        \Yii::app()->session['loginID'] = 1;
        $question = $this->getQuestionByType(Question::QT_M_MULTIPLE_CHOICE);

        $this->expectException(PermissionDeniedException::class);
        $this->getSubQuestionsService()->delete(self::$surveyId, $question->subquestions[0]->qid);
    }

    /**
     * @testdox A new subquestion of another question type can't be saved directly when active
     */
    public function testNewMultipleChoiceSubquestionModelIsNotSavedWhenActive()
    {
        $question = $this->getQuestionByType(Question::QT_M_MULTIPLE_CHOICE);
        $subquestion = new Question();
        $subquestion->setAttributes([
            'sid' => self::$surveyId,
            'gid' => $question->gid,
            'parent_qid' => $question->qid,
            'type' => Question::QT_M_MULTIPLE_CHOICE,
            'title' => 'NEW2',
            'question_order' => 99,
            'scale_id' => 0,
        ], false);

        $this->assertFalse($subquestion->save(false));
    }
}
