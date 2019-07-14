<?php

namespace ls\tests;

/**
 * @group templateinstance
 */
class TemplateInstanceTest extends TestBaseClass
{
    /**
     * Test that getInstance() return config xml or database value
     * depending on setting force_xmlsettings_for_survey_rendering.
     *
     * TemplateManifest = config xml.
     * TemplateConfiguration = config in database (installed template).
     */
    public function testBasic()
    {
        if (defined('YII_DEBUG') && YII_DEBUG) {
            \Yii::import('application.helpers.globalsettings_helper', true);

            \Yii::app()->setConfig('force_xmlsettings_for_survey_rendering', true);

            \Template::model()->resetInstance();
            $oTemplate = \Template::model()->getInstance();
            $this->assertEquals('TemplateManifest', get_class($oTemplate));

            \Yii::app()->setConfig('force_xmlsettings_for_survey_rendering', false);

            \Template::model()->resetInstance();
            $oTemplate = \Template::model()->getInstance();
            $this->assertEquals('TemplateConfiguration', get_class($oTemplate));
        } else {
            // Feature only available in debug mode.
            $this->markTestSkipped();
        }
    }
}
