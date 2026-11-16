<?php

namespace ls\tests;

/**
 * expressionQuestionForAll plugin: question attributes of questions with subquestions in ExpressionScript.
 *
 * @see https://bugs.limesurvey.org/view.php?id=14200
 * @group plugins
 */
class ExpressionQuestionForAllTest extends TestBaseClass
{
    /** @var boolean|null Activation state of the plugin before the test, null if not installed */
    private static $pluginWasActive;

    /**
     * Activate the plugin, import and activate the survey
     * (multiple choice A123 in the first group, ranking C1x0 in the second group).
     * The survey file must not be imported by other unit tests: getSubQuestions() caches the
     * subquestions by survey id for the whole process, a reimport with the same id gets the old ones.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_POST = [];
        $_SESSION = [];

        $plugin = \Plugin::model()->findByAttributes(['name' => 'expressionQuestionForAll']);
        if ($plugin) {
            self::$pluginWasActive = (bool) $plugin->active;
        } else {
            $plugin = new \Plugin();
            $plugin->name = 'expressionQuestionForAll';
            $plugin->plugin_type = 'core';
        }
        $plugin->active = 1;
        $plugin->save();
        App()->getPluginManager()->loadPlugin('expressionQuestionForAll', $plugin->id);

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_157447_array_filter_ranking_max_columns.lss';
        self::importSurvey($surveyFile);
        self::$testHelper->activateSurvey(self::$surveyId);
    }

    /**
     * Restore the plugin activation state
     */
    public static function tearDownAfterClass(): void
    {
        $plugin = \Plugin::model()->findByAttributes(['name' => 'expressionQuestionForAll']);
        if (self::$pluginWasActive === null) {
            $plugin->delete();
        } else {
            $plugin->active = (int) self::$pluginWasActive;
            $plugin->save();
        }
        parent::tearDownAfterClass();
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
     * Start the survey in ExpressionScript
     *
     * @param array $startingValues prefilled values (URL parameters)
     * @return void
     */
    private function startSurvey($startingValues = [])
    {
        \Yii::app()->setConfig('surveyID', self::$surveyId);
        \Yii::app()->setController(new DummyController('dummyid'));
        \LimeExpressionManager::SetDirtyFlag();
        \LimeExpressionManager::SetSurveyId(self::$surveyId);
        \buildsurveysession(self::$surveyId);
        if (!empty($startingValues)) {
            $_SESSION['responses_' . self::$surveyId]['startingValues'] = array_merge(['seed' => ''], $startingValues);
        }
        \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            'group',
            self::$testHelper->getSurveyOptions(self::$surveyId),
            true,
            0
        );
    }

    /**
     * Question-level attributes are available for multiple choice and ranking question codes
     */
    public function testQuestionAttributes()
    {
        $this->startSurvey();
        $multiple = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0, 'title' => 'A123']);
        $ranking = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0, 'title' => 'C1x0']);

        $this->assertSame((string) $multiple->qid, \LimeExpressionManager::ProcessString('{A123.qid}'));
        $this->assertSame((string) $multiple->gid, \LimeExpressionManager::ProcessString('{A123.gid}'));
        $this->assertSame('M', \LimeExpressionManager::ProcessString('{A123.type}'));
        $this->assertSame('N', \LimeExpressionManager::ProcessString('{A123.mandatory}'));
        $this->assertSame('0', \LimeExpressionManager::ProcessString('{A123.gseq}'));
        $this->assertSame('0', \LimeExpressionManager::ProcessString('{A123.qseq}'));

        $this->assertSame((string) $ranking->qid, \LimeExpressionManager::ProcessString('{C1x0.qid}'));
        $this->assertSame((string) $ranking->gid, \LimeExpressionManager::ProcessString('{C1x0.gid}'));
        $this->assertSame('R', \LimeExpressionManager::ProcessString('{C1x0.type}'));
        $this->assertSame('1', \LimeExpressionManager::ProcessString('{C1x0.gseq}'));
        $this->assertSame('1', \LimeExpressionManager::ProcessString('{C1x0.qseq}'));
    }

    /**
     * A question code with subquestions has no value
     */
    public function testNoValue()
    {
        $this->startSurvey();
        $this->assertSame('', \LimeExpressionManager::ProcessString('{C1x0.value}'));
        $this->assertSame('', \LimeExpressionManager::ProcessString('{A123.shown}'));
    }

    /**
     * A URL parameter with a question code with subquestions is ignored
     */
    public function testNoPrefill()
    {
        $this->startSurvey(['A123' => 'Y']);
        $this->assertArrayNotHasKey('', $_SESSION['responses_' . self::$surveyId]);
        $this->assertSame('', \LimeExpressionManager::ProcessString('{A123}'));
    }
}
