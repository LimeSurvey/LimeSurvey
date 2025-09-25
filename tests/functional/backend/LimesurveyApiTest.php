<?php

namespace ls\tests;

/**
 * @group api
 */
class LimesurveyApiTest extends TestBaseClass
{

    /**
     * Test getQuestionAttributes() API endpoint
     */
    public function testGetQuestionAttributesApi()
    {
        // Import survey
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_QuestionAttributeTestSurvey.lss';
        self::importSurvey($surveyFile);

        // Activate test plugin
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

        // Get question
        $question = \Question::model()->find(
            'title = :title AND sid = :sid',
            [
                'title' => 'Q00',
                'sid'   => self::$surveyId
            ]
        );
        
        $questionAttributes = App()->getApi()->getQuestionAttributes($question->qid);
        $this->assertNotEmpty($questionAttributes);

        // Check core single attribute
        $this->assertArrayHasKey('cssclass', $questionAttributes);
        $this->assertEquals("test-class", $questionAttributes['cssclass']);

        // Check core localized attribute
        $this->assertArrayHasKey('em_validation_q_tip', $questionAttributes);
        $this->assertEquals("Test string", $questionAttributes['em_validation_q_tip']['en']);
        $this->assertEquals("Texto de prueba", $questionAttributes['em_validation_q_tip']['es']);

        // Check question theme attribute
        $this->assertArrayHasKey('add_platform_info', $questionAttributes);
        $this->assertEquals("yes", $questionAttributes['add_platform_info']);

        // Check plugin attribute
        $this->assertArrayHasKey('testAttribute', $questionAttributes);
        $this->assertEquals("", $questionAttributes['testAttribute']);

        self::deActivatePlugin('NewQuestionAttributesPlugin');
    }

    /**
     * @inheritdoc
     * @todo Deactivate and uninstall plugins ?
     */
//    public static function tearDownAfterClass(): void
//    {
//        self::deActivatePlugin('NewQuestionAttributesPlugin');
//        parent::tearDownAfterClass();
//    }

}
