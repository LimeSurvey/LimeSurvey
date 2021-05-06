<?php

namespace ls\tests;

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

    public function testPluginAttributes()
    {
        $questionAttributeHelper = new \LimeSurvey\Models\Services\QuestionAttributeHelper();

        // Get attributes for question type 'S', which is used by the test plugin
        $aQuestionAttributes = $questionAttributeHelper->getAttributesFromPlugin('S');
        $this->assertNotEmpty($aQuestionAttributes);

        // Check the test attribute exists within the attributes received
        $aAttribute = null;
        foreach ($aQuestionAttributes as $item) {
            if (isset($item['name']) && $item['name'] == 'testAttribute') {
                $aAttribute = $item;
                break;
            }
        }
        $this->assertNotEmpty($aAttribute);

        // Check a core attribute does NOT exist within the attributes received
        $aAttribute = null;
        foreach ($aQuestionAttributes as $item) {
            if (isset($item['name']) && $item['name'] == 'question_template') {
                $aAttribute = $item;
                break;
            }
        }
        $this->assertEmpty($aAttribute);

        // Get attributes for question type 'T', which is NOT used by the test plugin
        $aQuestionAttributes = $questionAttributeHelper->getAttributesFromPlugin('T');

        // Check the test attribute does NOT exist within the attributes received
        $aAttribute = null;
        if (!empty($aQuestionAttributes)) {
            foreach ($aQuestionAttributes as $item) {
                if (isset($item['name']) && $item['name'] == 'testAttribute') {
                    $aAttribute = $item;
                    break;
                }
            }
        }
        $this->assertEmpty($aAttribute);
    }

    public function testCoreAttributes()
    {
        // Get attributes for question type 'S', which is used by the test plugin
        $aQuestionAttributes = \QuestionAttribute::getQuestionAttributesSettings('S', true);
        $this->assertNotEmpty($aQuestionAttributes);

        // Check the test attribute (from plugin) does NOT exist within the attributes received
        $aAttribute = null;
        foreach ($aQuestionAttributes as $item) {
            if (isset($item['name']) && $item['name'] == 'testAttribute') {
                $aAttribute = $item;
                break;
            }
        }
        $this->assertEmpty($aAttribute);

        // Check a core attribute exists within the attributes received
        $aAttribute = null;
        foreach ($aQuestionAttributes as $item) {
            if (isset($item['name']) && $item['name'] == 'hide_tip') {
                $aAttribute = $item;
                break;
            }
        }
        $this->assertNotEmpty($aAttribute);
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
