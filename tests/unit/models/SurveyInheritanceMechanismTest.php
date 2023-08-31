<?php

namespace ls\tests;

use Yii;

class SurveyInheritanceMechanismTest extends TestBaseClass
{
    public function testSetOptions()
    {
        $survey = new \Survey();

        // Asserting that the options have not been initialized yet.
        $this->assertNull($survey->oOptions, 'The survey options object should be null since it has not been initialized yet.');
        $this->assertNull($survey->oOptionLabels, 'The survey option labels object should be null since it has not been initialized yet.');
        $this->assertEmpty($survey->aOptions, 'The survey options array should be empty since options have not been initialized yet.');
        $this->assertSame(1, $survey->showInherited, 'The show inherited attribute should be set to 1 by default.');

        // Initializing
        $survey->setOptions();

        // Asserting that the options have been initialized.
        $this->assertNotNull($survey->oOptions, 'The survey options object should not be null since it has not been initialized yet.');
        $this->assertNotNull($survey->oOptionLabels, 'The survey option labels object should not be null since it has not been initialized yet.');
        $this->assertNotEmpty($survey->aOptions, 'The survey options array should not be empty since options have not been initialized yet.');
        $this->assertSame(1, $survey->showInherited, 'The show inherited attribute should be set to 1.');
    }

    public function testSetDefaultGroupOptions()
    {
        $survey = new \Survey();

        // Asserting that the options have not been initialized yet.
        $this->assertNull($survey->oOptions, 'The survey options object should be null since it has not been initialized yet.');
        $this->assertNull($survey->oOptionLabels, 'The survey option labels object should be null since it has not been initialized yet.');
        $this->assertEmpty($survey->aOptions, 'The survey options array should be empty since options have not been initialized yet.');
        $this->assertSame(1, $survey->showInherited, 'The show inherited attribute should be set to 1 by default.');

        // Initializing
        $survey->setOptions();
        $oOptions = $survey->oOptions;

        // Asserting that the options have been initialized with the default group options.
        $this->assertSame(1, $survey->oOptions->owner_id);
    }
}
