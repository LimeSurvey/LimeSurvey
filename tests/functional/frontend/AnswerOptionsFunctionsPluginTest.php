<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;

/**
 * @since 2021-11-24
 * @group plugins
 */
class AnswerOptionsFunctionsPluginTest extends TestBaseClassWeb
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
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_expressionAnswerOptionsTest.lss';
        self::importSurvey($surveyFile);

    }

    /**
     * Check if function return good value in loaded survey
     **/
    public function testGetAnswerOptionText()
    {
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');

        $testToDo = array(
            'en' => array(
                'checkstring'=> array(
                    'id' => 'check-string',
                    'text' => 'Very important',
                ),
                'checkselfqid'=> array(
                    'id' => 'check-selfqid',
                    'text' => 'Very important',
                ),
                'checkstringmulti'=> array(
                    'id' => 'check-stringmulti',
                    'text' => 'Very important',
                ),
                'checkselfqidmulti'=> array(
                    'id' => 'check-selfqidmulti',
                    'text' => 'Very important',
                ),
                'checkanswerem'=> array(
                    'id' => 'check-answerem',
                    'text' => 'Very important',
                ),
            ),
            'fr' => array(
                'checkstring'=> array(
                    'id' => 'check-string',
                    'text' => 'Très important',
                ),
                'checkselfqid'=> array(
                    'id' => 'check-selfqid',
                    'text' => 'Très important',
                ),
                'checkstringmulti'=> array(
                    'id' => 'check-stringmulti',
                    'text' => 'Très important',
                ),
                'checkselfqidmulti'=> array(
                    'id' => 'check-selfqidmulti',
                    'text' => 'Très important',
                ),
                'checkanswerem'=> array(
                    'id' => 'check-answerem',
                    'text' => 'Très important',
                ),
            )
        );
        
        try {
            foreach ($testToDo as $lang => $testids) {
                $url = $urlMan->createUrl(
                    'survey/index',
                    [
                        'sid' => self::$surveyId,
                        'newtest' => "Y",
                        'lang' => $lang
                    ]
                );
                self::$webDriver->get($url);
                foreach($testids as $testTitle => $values) {
                    $checktext = self::$webDriver->findElement(WebDriverBy::id($values['id']))->getText();
                    $this->assertEquals($checktext, $values['text'], "Check $testTitle seems invalid, get “".$checktext."”");
                }
            }
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
        /* expressionAnswerOptions plugin */
        $plugin = \Plugin::model()->findByAttributes(array('name'=>'ExpressionAnswerOptions'));
        if (!$plugin) {
            $plugin = new \Plugin();
            $plugin->name = 'ExpressionAnswerOptions';
            $plugin->plugin_type = 'core';
            $plugin->active = 1;
            $plugin->save();
        } else {
            $plugin->active = 1;
            $plugin->save();
        }
    }
}
