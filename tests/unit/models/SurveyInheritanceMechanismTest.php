<?php

namespace ls\tests;

use Yii;

class SurveyInheritanceMechanismTest extends TestBaseClass
{
    private static $globalSurveyGroupSettings;
    private static $defaultSurveyGroupSettings;
    private static $surveysGroup;
    private static $surveysGroupSettings;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Getting values from default surveys group.
        $defaultSurveyGroup = \SurveysGroups::model()->findByAttributes(array('name' => 'default'));
        $defaultSurveyGroupSettings = \SurveysGroupsettings::model()->findByAttributes(array('gsid' => $defaultSurveyGroup->gsid));
        $globalSurveyGroupSettings = \SurveysGroupsettings::model()->findByAttributes(array('gsid' => 0));

        // Getting values from global group in case they are inherited.
        if ($defaultSurveyGroupSettings->anonymized == 'I') {
            $defaultSurveyGroupSettings->anonymized = $globalSurveyGroupSettings->anonymized;
        }

        if ($defaultSurveyGroupSettings->format == 'I') {
            $defaultSurveyGroupSettings->format = $globalSurveyGroupSettings->format;
        }

        if ($defaultSurveyGroupSettings->savetimings == 'I') {
            $defaultSurveyGroupSettings->savetimings = $globalSurveyGroupSettings->savetimings;
        }

        if ($defaultSurveyGroupSettings->template == 'inherit') {
            $defaultSurveyGroupSettings->template = $globalSurveyGroupSettings->template;
        }

        self::$globalSurveyGroupSettings = $globalSurveyGroupSettings;
        self::$defaultSurveyGroupSettings = $defaultSurveyGroupSettings;

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

        // Asserting that the options have been initialized.
        $this->assertInstanceOf('stdClass', $survey->oOptions, 'The oOptions attribute should be an object of class stdClass.');
        $this->assertInstanceOf('stdClass', $survey->oOptionLabels, 'The oOptionLabels attribute should be an object of class stdClass.');
        $this->assertIsArray($survey->aOptions, 'The aOptions attribute should be an array.');

        $this->assertSame(self::$globalSurveyGroupSettings->anonymized, $survey->oOptions->anonymized, 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$globalSurveyGroupSettings->anonymized, $survey->aOptions['anonymized'], 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$globalSurveyGroupSettings->format, $survey->oOptions->format, 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$globalSurveyGroupSettings->format, $survey->aOptions['format'], 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$globalSurveyGroupSettings->savetimings, $survey->oOptions->savetimings, 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$globalSurveyGroupSettings->savetimings, $survey->aOptions['savetimings'], 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$globalSurveyGroupSettings->template, $survey->oOptions->template, 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$globalSurveyGroupSettings->template, $survey->aOptions['template'], 'The value should have been inherited from the default surveys group.');
    }

    public function testSetDefaultGroupOptions()
    {
        $survey = new \Survey();

        // Asserting that the options have not been initialized yet.
        $this->assertNull($survey->oOptions, 'The survey options object should be null since it has not been initialized yet.');
        $this->assertNull($survey->oOptionLabels, 'The survey option labels object should be null since it has not been initialized yet.');
        $this->assertEmpty($survey->aOptions, 'The survey options array should be empty since options have not been initialized yet.');

        // Initializing
        $survey->setOptions();

        // Asserting that the options have been initialized.
        $this->assertInstanceOf('stdClass', $survey->oOptions, 'The oOptions attribute should be an object of class stdClass.');
        $this->assertInstanceOf('stdClass', $survey->oOptionLabels, 'The oOptionLabels attribute should be an object of class stdClass.');
        $this->assertIsArray($survey->aOptions, 'The aOptions attribute should be an array.');

        $this->assertSame(self::$defaultSurveyGroupSettings->anonymized, $survey->oOptions->anonymized, 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$defaultSurveyGroupSettings->anonymized, $survey->aOptions['anonymized'], 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$defaultSurveyGroupSettings->format, $survey->oOptions->format, 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$defaultSurveyGroupSettings->format, $survey->aOptions['format'], 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$defaultSurveyGroupSettings->savetimings, $survey->oOptions->savetimings, 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$defaultSurveyGroupSettings->savetimings, $survey->aOptions['savetimings'], 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$defaultSurveyGroupSettings->template, $survey->oOptions->template, 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$defaultSurveyGroupSettings->template, $survey->aOptions['template'], 'The value should have been inherited from the default surdefaultSeys group.');
    }

    public function testSetSpecificGroupOptions()
    {
        $survey = new \Survey();
        $survey->bShowRealOptionValues = false;

        // Asserting that the options have not been initialized yet.
        $this->assertNull($survey->oOptions, 'The survey options object should be null since it has not been initialized yet.');
        $this->assertNull($survey->oOptionLabels, 'The survey option labels object should be null since it has not been initialized yet.');
        $this->assertEmpty($survey->aOptions, 'The survey options array should be empty since options have not been initialized yet.');

        $survey->setOptions((int)self::$surveysGroup->gsid);

        // Asserting that the options have been initialized.
        $this->assertInstanceOf('stdClass', $survey->oOptions, 'The oOptions attribute should be an object of class stdClass.');
        $this->assertInstanceOf('stdClass', $survey->oOptionLabels, 'The oOptionLabels attribute should be an object of class stdClass.');
        $this->assertIsArray($survey->aOptions, 'The aOptions attribute should be an array.');

        $this->assertSame(self::$surveysGroupSettings->anonymized, $survey->oOptions->anonymized, 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$surveysGroupSettings->anonymized, $survey->aOptions['anonymized'], 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$surveysGroupSettings->format, $survey->oOptions->format, 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$surveysGroupSettings->format, $survey->aOptions['format'], 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$surveysGroupSettings->savetimings, $survey->oOptions->savetimings, 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$surveysGroupSettings->savetimings, $survey->aOptions['savetimings'], 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$surveysGroupSettings->template, $survey->oOptions->template, 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$surveysGroupSettings->template, $survey->aOptions['template'], 'The value should have been inherited from the default surveys group.');
    }

    public function testSetSpecificGroupOptionsButShowingRealOptionValues()
    {
        $survey = new \Survey();

        // Asserting that the options have not been initialized yet.
        $this->assertNull($survey->oOptions, 'The survey options object should be null since it has not been initialized yet.');
        $this->assertNull($survey->oOptionLabels, 'The survey option labels object should be null since it has not been initialized yet.');
        $this->assertEmpty($survey->aOptions, 'The survey options array should be empty since options have not been initialized yet.');

        // Asserting that bShowRealOptionValues is true by default.
        $this->assertTrue($survey->bShowRealOptionValues, 'The bShowRealOptionValues attributes should be true by default.');

        $survey->setOptions((int)self::$surveysGroup->gsid);

        // Asserting that the options have been initialized.
        $this->assertInstanceOf('stdClass', $survey->oOptions, 'The oOptions attribute should be an object of class stdClass.');
        $this->assertInstanceOf('stdClass', $survey->oOptionLabels, 'The oOptionLabels attribute should be an object of class stdClass.');
        $this->assertIsArray($survey->aOptions, 'The aOptions attribute should be an array.');

        $this->assertSame(self::$defaultSurveyGroupSettings->anonymized, $survey->oOptions->anonymized, 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$defaultSurveyGroupSettings->anonymized, $survey->aOptions['anonymized'], 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$defaultSurveyGroupSettings->format, $survey->oOptions->format, 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$defaultSurveyGroupSettings->format, $survey->aOptions['format'], 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$defaultSurveyGroupSettings->savetimings, $survey->oOptions->savetimings, 'The value should have been inherited from the default surveys group.');
        $this->assertSame(self::$defaultSurveyGroupSettings->savetimings, $survey->aOptions['savetimings'], 'The value should have been inherited from the default surveys group.');
    }
}
