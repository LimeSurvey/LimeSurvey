<?php

namespace ls\tests\unit\services\SurveyAggregateService\GeneralSettings;

use ls\tests\TestBaseClass;

class GeneralSettingsUpdateSurveyActiveTest extends TestBaseClass
{
    public function testCanNotUpdateSomeSettingsWhenSurveyIsActive()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'active' => 'Y',

            'anonymized' => 'N',
            'savetimings' => 'N',
            'datestamp' => 'N',
            'ipaddr' => 'N',
            'ipanonymize' => 'N',
            'refurl' => 'N',
            'savequotaexit' => 'N'
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'anonymized' => 'Y',
            'savetimings' => 'Y',
            'datestamp' => 'Y',
            'ipaddr' => 'Y',
            'ipanonymize' => 'Y',
            'refurl' => 'Y',
            'savequotaexit' => 'Y'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('N', $attributes['anonymized']);
        $this->assertEquals('N', $attributes['savetimings']);
        $this->assertEquals('N', $attributes['datestamp']);
        $this->assertEquals('N', $attributes['ipaddr']);
        $this->assertEquals('N', $attributes['ipanonymize']);
        $this->assertEquals('N', $attributes['refurl']);
        $this->assertEquals('N', $attributes['savequotaexit']);
    }

    public function testCanNotUpdateSomeSettingsWhenSurveyIsActiveSurveyInactive()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'active' => 'N',

            'anonymized' => 'N',
            'savetimings' => 'N',
            'datestamp' => 'N',
            'ipaddr' => 'N',
            'ipanonymize' => 'N',
            'refurl' => 'N',
            'savequotaexit' => 'N'
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);
 
        $generalSettings->update(1, [
            'anonymized' => 'Y',
            'savetimings' => 'Y',
            'datestamp' => 'Y',
            'ipaddr' => 'Y',
            'ipanonymize' => 'Y',
            'refurl' => 'Y',
            'savequotaexit' => 'Y'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('Y', $attributes['anonymized']);
        $this->assertEquals('Y', $attributes['savetimings']);
        $this->assertEquals('Y', $attributes['datestamp']);
        $this->assertEquals('Y', $attributes['ipaddr']);
        $this->assertEquals('Y', $attributes['ipanonymize']);
        $this->assertEquals('Y', $attributes['refurl']);
        $this->assertEquals('Y', $attributes['savequotaexit']);
    }
}
