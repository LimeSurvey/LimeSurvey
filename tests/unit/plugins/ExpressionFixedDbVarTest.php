<?php

namespace ls\tests;

/**
 * expressionFixedDbVar plugin: The date variables have the same format on every database.
 *
 * @group plugins
 */
class ExpressionFixedDbVarTest extends TestBaseClass
{
    /** @var boolean|null Activation state of the plugin before the test, null if not installed */
    private static $pluginWasActive;

    /** @var \expressionFixedDbVar */
    private static $plugin;

    /**
     * Activate the plugin and import the survey archive with its responses
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_POST = [];
        $_SESSION = [];

        $plugin = \Plugin::model()->findByAttributes(['name' => 'expressionFixedDbVar']);
        if ($plugin) {
            self::$pluginWasActive = (bool) $plugin->active;
        } else {
            $plugin = new \Plugin();
            $plugin->name = 'expressionFixedDbVar';
            $plugin->plugin_type = 'core';
        }
        $plugin->active = 1;
        $plugin->save();
        self::$plugin = App()->getPluginManager()->loadPlugin('expressionFixedDbVar', $plugin->id);

        self::importSurvey(self::$surveysFolder . '/survey_archive_setVariableExpressionEndPluginTest.lsa');
    }

    /**
     * Restore the plugin activation state and reset request and session data
     */
    public static function tearDownAfterClass(): void
    {
        $plugin = \Plugin::model()->findByAttributes(['name' => 'expressionFixedDbVar']);
        if (self::$pluginWasActive === null) {
            $plugin->delete();
        } else {
            $plugin->active = (int) self::$pluginWasActive;
            $plugin->save();
        }
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
        parent::tearDownAfterClass();
    }

    /**
     * SUBMITDATE and STARTDATE have no fractional seconds (MSSQL returns 2019-08-05 00:10:35.000)
     */
    public function testDateVariablesHaveNoFractionalSeconds()
    {
        $response = \Response::model(self::$surveyId)->findByAttributes(['token' => 'tokenTest']);
        $this->assertNotNull($response);

        \Yii::app()->setConfig('surveyID', self::$surveyId);
        \Yii::app()->setController(new DummyController('dummyid'));
        \LimeExpressionManager::SetDirtyFlag();
        \LimeExpressionManager::SetSurveyId(self::$surveyId);
        \buildsurveysession(self::$surveyId);
        $_SESSION['responses_' . self::$surveyId]['srid'] = $response->id;
        \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            'group',
            self::$testHelper->getSurveyOptions(self::$surveyId),
            true,
            0
        );

        self::$plugin->beforeTwigViews();

        $this->assertSame('2019-08-05 00:10:35', \LimeExpressionManager::ProcessString('{SUBMITDATE}'));
        $this->assertSame('2019-08-04 23:53:47', \LimeExpressionManager::ProcessString('{STARTDATE}'));
    }
}
