<?php

namespace ls\tests;

use QuestionCreate;

/**
 * @group questionattribute
 */
class QuestionAttributeTest extends TestBaseClassWeb
{

    /**
     * @inheritdoc
     * Activate needed plugins
     * Import survey in tests/surveys/.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        require_once __DIR__."/../../data/plugins/NewQuestionAttributesPlugin.php";
        $plugin = \Plugin::model()->findByAttributes(array('name'=>'NewQuestionAttributesPlugin'));
        if (!$plugin) {
            $plugin = new \Plugin();
            $plugin->name = 'NewQuestionAttributesPlugin';
            $plugin->active = 1;
            $plugin->save();
        } else {
            $plugin->active = 1;
            $plugin->save();
        }
        App()->getPluginManager()->loadPlugin('NewQuestionAttributesPlugin', $plugin->id);

    }

    public function testPluginQuestionAttributeProvider()
    {
        $question = new \Question();
        $question->type = 'S';
        $provider = new \LimeSurvey\Models\Services\PluginQuestionAttributeProvider();
        $questionAttributes = $provider->getDefinitions($question, []);

        $this->assertNotEmpty($questionAttributes);

        // The test plugin provides a 'testAttribute' for question type 'S'. It should be present.
        $this->assertArrayHasKey('testAttribute', $questionAttributes);

        // The test plugin provides a 'testAttributeForArray' for question type 'F'. It should not be present.
        $this->assertArrayNotHasKey('testAttributeForArray', $questionAttributes);
    }

    public function testCoreQuestionAttributeProvider()
    {
        $question = new \Question();
        $question->type = 'S';
        $provider = new \LimeSurvey\Models\Services\CoreQuestionAttributeProvider();
        $questionAttributes = $provider->getDefinitions($question, []);

        $this->assertNotEmpty($questionAttributes);
        $this->assertArrayHasKey('hide_tip', $questionAttributes);
    }

    public function testThemeQuestionAttributeProvider()
    {
        $question = new \Question();
        $question->type = 'S';
        $provider = new \LimeSurvey\Models\Services\ThemeQuestionAttributeProvider();
        $questionAttributes = $provider->getDefinitions($question, ['questionTheme' => 'browserdetect']);

        $this->assertNotEmpty($questionAttributes);
        $this->assertArrayHasKey('add_platform_info', $questionAttributes);

        $questionAttributes = $provider->getDefinitions($question, []);
        $this->assertEmpty($questionAttributes);
    }

    public function testFetcher()
    {
        // Import survey
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_QuestionAttributeTestSurvey.lss';
        self::importSurvey($surveyFile);

        $questionAttributeFetcher = new \LimeSurvey\Models\Services\QuestionAttributeFetcher();

        $question = QuestionCreate::getInstance(self::$surveyId, 'S');

        $questionAttributeFetcher->setQuestion($question);
        $questionAttributeFetcher->setTheme('browserdetect');
        $questionAttributeFetcher->setFilter('advancedOnly', true);

        $questionAttributes = $questionAttributeFetcher->fetch();
        $this->assertNotEmpty($questionAttributes);
        $this->assertArrayHasKey('hide_tip', $questionAttributes);          // Core attribute
        $this->assertArrayHasKey('add_platform_info', $questionAttributes); // Theme attribute
        $this->assertArrayHasKey('testAttribute', $questionAttributes);     // Plugin attribute
    }

    /**
     * @inheritdoc
     * @todo Deactivate and uninstall plugins ?
     */
    public static function tearDownAfterClass()
    {
        self::deActivatePlugin('NewQuestionAttributesPlugin');
        parent::tearDownAfterClass();
    }

}
