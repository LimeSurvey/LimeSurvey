<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;

/**
 * @since 2019-08-05
 * @group plugins
 */
class VariableExpressionEndPluginTest extends TestBaseClassWeb
{

    /**
     * @inheritdoc
     * Activate needed plugins
     * Import survey in tests/surveys/.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::_activateAndLoadPlugins();
        $surveyFile = self::$surveysFolder . '/survey_archive_setVariableExpressionEndPluginTest.lsa';
        self::importSurvey($surveyFile);

    }

    /* Launch survey with an already submitted token */
    public function testPluginsByTokens()
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

            /* expressionFixedDbVar plugin */
            $checkSEED = self::$webDriver->findElement(WebDriverBy::id('Check-SEED'))->getText();
            $this->assertEquals($checkSEED,"SEED : 521277562","SEED seems invalid, get «".$checkSEED.">»");
            $checkSUBMITDATE = self::$webDriver->findElement(WebDriverBy::id('Check-SUBMITDATE'))->getText();
            $this->assertEquals($checkSUBMITDATE,"SUBMITDATE : 2019-08-05 00:10:35","SUBMITDATE seems invalid, get «".$checkSUBMITDATE.">»");
            $checkSTARTDATE = self::$webDriver->findElement(WebDriverBy::id('Check-STARTDATE'))->getText();
            $this->assertEquals($checkSTARTDATE,"STARTDATE : 2019-08-04 23:53:47","STARTDATE seems invalid, get «".$checkSTARTDATE."»");

            /* expressionQuestionForAll and expressionQuestionHelp plugins */
            /* Current */
            $checkMULTIquestion = self::$webDriver->findElement(WebDriverBy::id('MULTI-question'))->getText();
            $checkMULTIquestionFixed = "MULTI.question : TEXT response is Check filled";
            $this->assertEquals($checkMULTIquestion,$checkMULTIquestionFixed,"MULTI.question seems invalid, get «".$checkMULTIquestion."»");
            $checkMULTIhelp = self::$webDriver->findElement(WebDriverBy::id('MULTI-help'))->getText();
            $checkMULTIhelpFixed = "MULTI.help : MULTI response is 2";
            $this->assertEquals($checkMULTIhelp,$checkMULTIhelpFixed,"MULTI.help seems invalid, get «".$checkMULTIhelp."»");
            /* updated */
            $TextSGQA = "Q".$questions['TEXT']['qid'];
            $Text = self::$webDriver->findElement(WebDriverBy::id("answer".$TextSGQA));
            $Text->sendKeys(" updated");
            $MultiTextSQ03SGQA = "Q".$questions['MULTI']['qid']."SQ03";
            $MultiTextSQ03 = self::$webDriver->findElement(WebDriverBy::id("answer".$MultiTextSQ03SGQA));
            $MultiTextSQ03->sendKeys("Sub question #3 updated");
             $checkMULTIquestion = self::$webDriver->findElement(WebDriverBy::id('MULTI-question'))->getText();
            $checkMULTIquestionFixed = "MULTI.question : TEXT response is Check filled updated";
            $this->assertEquals($checkMULTIquestion,$checkMULTIquestionFixed,"MULTI.question seems invalid after update, get «".$checkMULTIquestion."»");
            $checkMULTIhelp = self::$webDriver->findElement(WebDriverBy::id('MULTI-help'))->getText();
            $checkMULTIhelpFixed = "MULTI.help : MULTI response is 3";
            $this->assertEquals($checkMULTIhelp,$checkMULTIhelpFixed,"MULTI.help seems invalid after update, get «".$checkMULTIhelp."»");
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
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    private static function _activateAndLoadPlugins()
    {
        /* expressionFixedDbVar plugin */
        $plugin = \Plugin::model()->findByAttributes(array('name'=>'expressionFixedDbVar'));
        if (!$plugin) {
            $plugin = new \Plugin();
            $plugin->name = 'expressionFixedDbVar';
            $plugin->active = 1;
            $plugin->save();
        } else {
            $plugin->active = 1;
            $plugin->save();
        }

        /* expressionQuestionForAll plugin */
        App()->getPluginManager()->loadPlugin('expressionQuestionForAll', $plugin->id);
        $plugin = \Plugin::model()->findByAttributes(array('name'=>'expressionQuestionForAll'));
        if (!$plugin) {
            $plugin = new \Plugin();
            $plugin->name = 'expressionQuestionForAll';
            $plugin->active = 1;
            $plugin->priority = 1;
            $plugin->save();
        } else {
            $plugin->active = 1;
            $plugin->priority = 1;
            $plugin->save();
        }
        App()->getPluginManager()->loadPlugin('expressionQuestionForAll', $plugin->id);

        /* expressionQuestionHelp plugin */
        $plugin = \Plugin::model()->findByAttributes(array('name'=>'expressionQuestionHelp'));
        if (!$plugin) {
            $plugin = new \Plugin();
            $plugin->name = 'expressionQuestionHelp';
            $plugin->active = 1;
            $plugin->save();
        } else {
            $plugin->active = 1;
            $plugin->save();
        }
    }
}
