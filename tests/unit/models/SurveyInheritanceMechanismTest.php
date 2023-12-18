<?php

namespace ls\tests;

use Yii;

class SurveyInheritanceMechanismTest extends TestBaseClass
{
    private static $surveysGroup;
    private static $surveysGroupSettings;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Creating a new custom group.
        $surveysGroup = new \SurveysGroups();
        $surveysGroup->name = 'new_group';
        $surveysGroup->sortorder = 0;
        $surveysGroup->created_by = 1;
        $surveysGroup->title = 'New Survey Group';
        $surveysGroup->description = 'A new test survey group.';
        $surveysGroup->owner_id = 1;
        $surveysGroup->alwaysavailable = 1;
        $surveysGroup->save();

        $surveysGroupSettings = new \SurveysGroupsettings();
        $surveysGroupSettings->gsid = $surveysGroup->gsid;
        $surveysGroupSettings->owner_id = -1;
        $surveysGroupSettings->admin = 'inherit';
        $surveysGroupSettings->anonymized = 'Y';
        $surveysGroupSettings->format = 'G';
        $surveysGroupSettings->savetimings = 'Y';
        $surveysGroupSettings->template = 'bootswatch';
        $surveysGroupSettings->save();

        self::$surveysGroup = $surveysGroup;
        self::$surveysGroupSettings = $surveysGroupSettings;
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        // Deleting group and its settings.
        self::$surveysGroup->delete();
        self::$surveysGroupSettings->delete();
    }

    /**
     * Testing that a survey inherits the global
     * settings correctly.
     */
    public function testSetGlobalGroupOptions()
    {
        $survey = new \Survey();

        // Asserting that the options have not been initialized yet.
        $this->assertNull($survey->oOptions, 'The survey options object should be null since it has not been initialized yet.');
        $this->assertNull($survey->oOptionLabels, 'The survey option labels object should be null since it has not been initialized yet.');
        $this->assertEmpty($survey->aOptions, 'The survey options array should be empty since options have not been initialized yet.');

        // Initializing
        $survey->bShowRealOptionValues = false;
        $survey->setOptions(0);

        // Asserting that the options have been initialized correctly.
        $instance = \SurveysGroupsettings::getInstance(0, $survey, null, 1, $survey->bShowRealOptionValues);

        $this->assertEquals($instance->oOptions, $survey->oOptions, 'The options object was not correctly initialized.');
        $this->assertEquals($instance->oOptionLabels, $survey->oOptionLabels, 'The option labels object was not correctly initialized.');
        $this->assertEquals((array)$instance->oOptions, $survey->aOptions, 'The options array was not correctly initialized.');
        $this->assertSame($instance->showInherited, $survey->showInherited, 'The showInherited attribute was not correctly initialized.');

        // Checking specific values.
        $globalOptions = \SurveysGroupsettings::model()->findByAttributes(array('gsid' => 0));
        $this->assertSame($globalOptions->anonymized, $survey->oOptions->anonymized, 'The survey anonymized attribute should correspond to the global attribute.');
        $this->assertSame($globalOptions->savetimings, $survey->aOptions['savetimings'], 'The survey savetimings attribute should correspond to the global attribute.');
        $this->assertSame($globalOptions->template, $survey->aOptions['template'], 'The survey template attribute should correspond to the global attribute.');
    }

    /**
     * Testing that a survey inherits the settings
     * from the default surveys group correctly.
     */
    public function testSetDefaultGroupOptions()
    {
        $survey = new \Survey();

        // Asserting that the options have not been initialized yet.
        $this->assertNull($survey->oOptions, 'The survey options object should be null since it has not been initialized yet.');
        $this->assertNull($survey->oOptionLabels, 'The survey option labels object should be null since it has not been initialized yet.');
        $this->assertEmpty($survey->aOptions, 'The survey options array should be empty since options have not been initialized yet.');

        // Initializing
        $survey->setOptions();

        // Asserting that the options have been initialized correctly.
        $instance = \SurveysGroupsettings::getInstance(1, $survey, null, 1, $survey->bShowRealOptionValues);

        $this->assertEquals($instance->oOptions, $survey->oOptions, 'The options object was not correctly initialized.');
        $this->assertEquals($instance->oOptionLabels, $survey->oOptionLabels, 'The option labels object was not correctly initialized.');
        $this->assertEquals((array)$instance->oOptions, $survey->aOptions, 'The options array was not correctly initialized.');
        $this->assertSame($instance->showInherited, $survey->showInherited, 'The showInherited attribute was not correctly initialized.');

        // Checking specific values.
        $defaultOptions = \SurveysGroupsettings::model()->findByAttributes(array('gsid' => 1));
        $globalOptions = \SurveysGroupsettings::model()->findByAttributes(array('gsid' => 0));

        if ($defaultOptions->anonymized === 'I') {
            $expectedAnonymizedOption = $globalOptions->anonymized;
        }

        if ($defaultOptions->savetimings === 'I') {
            $expectedSavetimingsOption = $globalOptions->savetimings;
        }

        if ($defaultOptions->template === 'inherit') {
            $expectedTemplate = $globalOptions->template;
        }

        $this->assertSame($expectedAnonymizedOption, $survey->oOptions->anonymized, 'The survey anonymized attribute should correspond to the global attribute.');
        $this->assertSame($expectedSavetimingsOption, $survey->aOptions['savetimings'], 'The survey savetimings attribute should correspond to the global attribute.');
        $this->assertSame($expectedTemplate, $survey->aOptions['template'], 'The survey template attribute should correspond to the global attribute.');
    }

    /**
     * Testing that a survey inherits the settings
     * from a surveys group correctly.
     */
    public function testSetSpecificGroupOptions()
    {
        $survey = new \Survey();
        $survey->bShowRealOptionValues = false;

        // Asserting that the options have not been initialized yet.
        $this->assertNull($survey->oOptions, 'The survey options object should be null since it has not been initialized yet.');
        $this->assertNull($survey->oOptionLabels, 'The survey option labels object should be null since it has not been initialized yet.');
        $this->assertEmpty($survey->aOptions, 'The survey options array should be empty since options have not been initialized yet.');

        $survey->setOptions((int)self::$surveysGroup->gsid);

        // Asserting that the options have been initialized correctly.
        $instance = \SurveysGroupsettings::getInstance((int)self::$surveysGroup->gsid, $survey, null, 1, $survey->bShowRealOptionValues);

        $this->assertEquals($instance->oOptions, $survey->oOptions, 'The options object was not correctly initialized.');
        $this->assertEquals($instance->oOptionLabels, $survey->oOptionLabels, 'The option labels object was not correctly initialized.');
        $this->assertEquals((array)$instance->oOptions, $survey->aOptions, 'The options array was not correctly initialized.');
        $this->assertSame($instance->showInherited, $survey->showInherited, 'The showInherited attribute was not correctly initialized.');

        // Checking specific custom group values.
        $this->assertSame('Y', $survey->oOptions->anonymized, 'The anonymized attribute was set to Y in the group.');
        $this->assertSame('Y', $survey->aOptions['savetimings'], 'The savetimings attribute was set to Y in the group.');
        $this->assertSame('bootswatch', $survey->aOptions['template'], 'The template attribute was set to bootswatch in the group.');
    }

    /**
     * Testing that a survey with the bShowRealOptionValues set to true
     * doesn't inherit the settings from a given surveys group.
     */
    public function testSetSpecificGroupOptionsButShowingRealOptionValues()
    {
        $survey = new \Survey();
        $survey->usecookie = 'Y';
        $survey->allowregister = 'Y';
        $survey->allowsave = 'N';

        // Asserting that the options have not been initialized yet.
        $this->assertNull($survey->oOptions, 'The survey options object should be null since it has not been initialized yet.');
        $this->assertNull($survey->oOptionLabels, 'The survey option labels object should be null since it has not been initialized yet.');
        $this->assertEmpty($survey->aOptions, 'The survey options array should be empty since options have not been initialized yet.');

        // Asserting that bShowRealOptionValues is true by default.
        $this->assertTrue($survey->bShowRealOptionValues, 'The bShowRealOptionValues attributes should be true by default.');

        $survey->setOptions((int)self::$surveysGroup->gsid);

        // Asserting that the options have been initialized correctly.
        $instance = \SurveysGroupsettings::getInstance((int)self::$surveysGroup->gsid, $survey, null, 1, $survey->bShowRealOptionValues);

        $this->assertEquals($instance->oOptions, $survey->oOptions, 'The options object was not correctly initialized.');
        $this->assertEquals($instance->oOptionLabels, $survey->oOptionLabels, 'The option labels object was not correctly initialized.');
        $this->assertEquals((array)$instance->oOptions, $survey->aOptions, 'The options array was not correctly initialized.');
        $this->assertSame($instance->showInherited, $survey->showInherited, 'The showInherited attribute was not correctly initialized.');

        // Checking specific custom group values.
        $this->assertNotSame('Y', $survey->oOptions->anonymized, 'The anonymized attribute should not be taken from the custom group.');
        $this->assertNotSame('Y', $survey->aOptions['savetimings'], 'The savetimings attribute should not be taken from the custom group.');

        // Asserting that survey defined values were not overwritten.
        $this->assertSame('Y', $survey->oOptions->usecookie, 'The usecookie attribute should have been preserved.');
        $this->assertSame('Y', $survey->aOptions['allowregister'], 'The allowregister attribute should have been preserved.');
        $this->assertSame('N', $survey->aOptions['allowsave'], 'The allowsave attribute should have been preserved.');
    }

    /**
     * Testing that the global options are correctly inherited.
     */
    public function testSetInheritedGlobalGroupOptions()
    {
        // Setting temporary global options for the test.
        $globalOptions = \SurveysGroupsettings::model()->findByAttributes(array('gsid' => 0));

        $tmpGlobalTemplate = $globalOptions->template;
        $tmpGlobalUsecookie = $globalOptions->usecookie;
        $tmpGlobalAllowsave = $globalOptions->allowsave;

        $globalOptions->template = 'fruity_twentythree';
        $globalOptions->usecookie = 'Y';
        $globalOptions->allowsave = 'N';

        $globalOptions->save();

        // Setting default options to inherit temporarily.
        $defaultOptions = \SurveysGroupsettings::model()->findByAttributes(array('gsid' => 1));

        $tmpDefaultTemplate = $defaultOptions->template;
        $tmpDefaultUsecookie = $defaultOptions->usecookie;
        $tmpDefaultAllowsave = $defaultOptions->allowsave;

        $defaultOptions->template = 'inherit';
        $defaultOptions->usecookie = 'I';
        $defaultOptions->allowsave = 'I';

        $defaultOptions->save();

        // Changing attributes in the custom group.
        self::$surveysGroupSettings->template = 'inherit';
        self::$surveysGroupSettings->usecookie = 'I';
        self::$surveysGroupSettings->allowsave = 'I';

        self::$surveysGroupSettings->save();
        self::$surveysGroupSettings->refresh();

        $survey = new \Survey();
        $survey->template = 'inherit';
        $survey->usecookie = 'I';
        $survey->allowsave = 'I';
        $survey->bShowRealOptionValues = false;

        $survey->setOptions((int)self::$surveysGroup->gsid);

        // Asserting that the options were inherited from the global context.
        $this->assertSame('fruity_twentythree', $survey->oOptions->template);
        $this->assertSame('Y', $survey->oOptions->usecookie);
        $this->assertSame('N', $survey->oOptions->allowsave);

        // Restoring options.
        $globalOptions->template = $tmpGlobalTemplate;
        $globalOptions->usecookie = $tmpGlobalUsecookie;
        $globalOptions->allowsave = $tmpGlobalAllowsave;

        $globalOptions->save();

        $defaultOptions->template = $tmpDefaultTemplate;
        $defaultOptions->usecookie = $tmpDefaultUsecookie;
        $defaultOptions->allowsave = $tmpDefaultAllowsave;

        $defaultOptions->save();
    }

    /**
     * Testing that the default options are correctly inherited.
     */
    public function testSetInheritedDefaultGroupOptions()
    {
        // Setting temporary default options for the test.
        $defaultOptions = \SurveysGroupsettings::model()->findByAttributes(array('gsid' => 0));

        $tmpDefaultTemplate = $defaultOptions->template;
        $tmpDefaultUsecookie = $defaultOptions->usecookie;
        $tmpDefaultAllowsave = $defaultOptions->allowsave;

        $defaultOptions->template = 'fruity_twentythree';
        $defaultOptions->usecookie = 'Y';
        $defaultOptions->allowsave = 'N';

        $defaultOptions->save();

        $survey = new \Survey();
        $survey->template = 'inherit';
        $survey->usecookie = 'I';
        $survey->allowsave = 'I';
        $survey->bShowRealOptionValues = false;

        $survey->setOptions((int)self::$surveysGroup->gsid);

        // Asserting that the options were inherited from the global context.
        $this->assertSame('fruity_twentythree', $survey->oOptions->template);
        $this->assertSame('Y', $survey->oOptions->usecookie);
        $this->assertSame('N', $survey->oOptions->allowsave);

        // Restoring options.
        $defaultOptions->template = $tmpDefaultTemplate;
        $defaultOptions->usecookie = $tmpDefaultUsecookie;
        $defaultOptions->allowsave = $tmpDefaultAllowsave;

        $defaultOptions->save();
    }
}
