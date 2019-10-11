<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;

/**
 * @since 2019-08-05
 * @group plugins
 */
class FixedFunctionExpressionPluginTest extends TestBaseClassWeb
{

    /**
     * @inheritdoc
     * Activate needed plugins
     * Import survey in tests/surveys/.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::installAndActivatePlugin('statFunctions');
        $surveyFile = self::$surveysFolder . '/survey_archive_statCountFunctionsTest.lsa';
        self::importSurvey($surveyFile);

    }

    /* Launch survey with an already submitted token */
    public function testPluginsStats()
    {
        $questions = $this->getAllSurveyQuestions();
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'token' => 'tokenTest',
                'newtest' => "Y",
            ]
        );
        try {
            self::$webDriver->get($url);

        } catch (\Exception $e) {
            $filename = __CLASS__ ."_". __FUNCTION__;
            self::$testHelper->takeScreenshot(self::$webDriver,$filename);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot ' .$filename . PHP_EOL . $e->getMessage()
            );
        }
    }

    /**
     * @inheritdoc
     * @todo Deactivate and uninstall plugins ?
     */
    public static function tearDownAfterClass()
    {
        self::deActivatePlugin('statFunctions');
        parent::tearDownAfterClass();
    }

}
