<?php

namespace ls\tests\unit\helpers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;

/**
 * Ranking question with a condition (relevance equation) on another question.
 *
 * @see https://bugs.limesurvey.org/view.php?id=20666
 */
class RankingWithConditionTest extends TestBaseClass
{
    /**
     * Import and activate the survey (list question Q01, ranking question Q00 relevant if Q01 is A2)
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_POST = [];
        $_SESSION = [];

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_836215_ranking_with_condition.lss';
        self::importSurvey($surveyFile);
        self::$testHelper->activateSurvey(self::$surveyId);
    }

    /**
     * Reset request and session data
     */
    public function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
        parent::tearDown();
    }

    /**
     * The question relevance of a ranking question must not be used as relevance
     * of a ranking item: only rank slots ({qid}_S{sqid}) may have subquestion relevance.
     */
    public function testQuestionRelevanceIsNotSubquestionRelevance()
    {
        $ranking = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0, 'type' => \Question::QT_R_RANKING]);

        \Yii::app()->setConfig('surveyID', self::$surveyId);
        \Yii::app()->setController(new DummyController('dummyid'));
        \buildsurveysession(self::$surveyId);
        \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            'group',
            self::$testHelper->getSurveyOptions(self::$surveyId),
            false,
            0
        );
        \LimeExpressionManager::NavigateForwards();

        $LEM = \LimeExpressionManager::singleton();
        $subQrelInfo = (new \ReflectionProperty($LEM, 'subQrelInfo'))->getValue($LEM);
        $this->assertArrayNotHasKey('Q' . $ranking->qid, $subQrelInfo[$ranking->qid] ?? [], 'The ranking question itself has no subquestion relevance');

        $scripts = \LimeExpressionManager::GetRelevanceAndTailoringJavaScript(true);
        $this->assertIsArray($scripts);
        $this->assertFalse($_SESSION['responses_' . self::$surveyId]['relevanceStatus'][$ranking->qid], 'Ranking question is hidden while the condition is not met');
    }
}
