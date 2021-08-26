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
    public static function setUpBeforeClass(): void
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
        // Test PluginQuestionAttributeProvider with a question object
        $question = new \Question();
        $question->type = 'S';
        $provider = new \LimeSurvey\Models\Services\PluginQuestionAttributeProvider();
        $questionAttributes = $provider->getDefinitions(['question' => $question]);

        $this->assertNotEmpty($questionAttributes);

        // The test plugin provides a 'testAttribute' for question type 'S'. It should be present.
        $this->assertArrayHasKey('testAttribute', $questionAttributes);

        // The test plugin provides a 'testAttributeForArray' for question type 'F'. It should not be present.
        $this->assertArrayNotHasKey('testAttributeForArray', $questionAttributes);

        // Test again but passing only a question type
        $questionAttributes = $provider->getDefinitions(['questionType' => 'F']);
        $this->assertNotEmpty($questionAttributes);
        $this->assertArrayHasKey('testAttributeForArray', $questionAttributes);
    }

    public function testCoreQuestionAttributeProvider()
    {
        // Test CoreQuestionAttributeProvider with a question object
        $question = new \Question();
        $question->type = 'S';
        $provider = new \LimeSurvey\Models\Services\CoreQuestionAttributeProvider();
        $questionAttributes = $provider->getDefinitions(['question' => $question]);

        $this->assertNotEmpty($questionAttributes);
        $this->assertArrayHasKey('hide_tip', $questionAttributes);

        // Test again but passing only a question type
        $questionAttributes = $provider->getDefinitions(['questionType' => 'M']);
        $this->assertNotEmpty($questionAttributes);
        $this->assertArrayHasKey('max_answers', $questionAttributes);
    }

    public function testThemeQuestionAttributeProvider()
    {
        // Test ThemeQuestionAttributeProvider with a question object
        $question = new \Question();
        $question->type = 'S';
        $provider = new \LimeSurvey\Models\Services\ThemeQuestionAttributeProvider();
        $questionAttributes = $provider->getDefinitions(['question' => $question, 'questionTheme' => 'browserdetect']);

        $this->assertNotEmpty($questionAttributes);
        $this->assertArrayHasKey('add_platform_info', $questionAttributes);

        $questionAttributes = $provider->getDefinitions(['question' => $question]);
        $this->assertEmpty($questionAttributes);

        // Test again but passing only a question type and theme
        $questionAttributes = $provider->getDefinitions(['questionType' => 'L', 'questionTheme' => 'bootstrap_buttons']);
        $this->assertNotEmpty($questionAttributes);
        $this->assertArrayHasKey('button_size', $questionAttributes);
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
        $questionAttributeFetcher->setAdvancedOnly(true);

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
    public static function tearDownAfterClass(): void
    {
        self::deActivatePlugin('NewQuestionAttributesPlugin');
        parent::tearDownAfterClass();
    }

}
