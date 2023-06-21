<?php

namespace ls\tests\unit\services\SurveyUpdater\GeneralSettings;

use ls\tests\TestBaseClass;
use LimeSurvey\Models\Services\SurveyUpdater\GeneralSettings;

class GeneralSettingsUpdateSurveyActiveTest extends TestBaseClass
{
    public function testCanNotUpdateSomeSettingsWhenSurveyIsActive()
    {
        $mockSet = (new GeneralSettingsMockFactory)->make();

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'active' => 'Y',

            'anonymized' => 'N',
            'savetimings' => 'N',
            'datestamp' => 'N',
            'ipaddr' => 'N',
            'ipanonymize' => 'N',
            'refurl' => 'N'
        ]);

        $surveyUpdate = new GeneralSettings(
            $mockSet->modelPermission,
            $mockSet->modelSurvey,
            $mockSet->yiiApp,
            $mockSet->sessionData,
            $mockSet->pluginManager,
            $mockSet->languageConsistency
        );

        $surveyUpdate->update(1, [
            'anonymized' => 'Y',
            'savetimings' => 'Y',
            'datestamp' => 'Y',
            'ipaddr' => 'Y',
            'ipanonymize' => 'Y',
            'refurl' => 'Y'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('N', $attributes['anonymized']);
        $this->assertEquals('N', $attributes['savetimings']);
        $this->assertEquals('N', $attributes['datestamp']);
        $this->assertEquals('N', $attributes['ipaddr']);
        $this->assertEquals('N', $attributes['ipanonymize']);
        $this->assertEquals('N', $attributes['refurl']);
    }

    public function testCanNotUpdateSomeSettingsWhenSurveyIsActiveSurveyInactive()
    {
        $mockSet = (new GeneralSettingsMockFactory)->make();

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'active' => 'N',

            'anonymized' => 'N',
            'savetimings' => 'N',
            'datestamp' => 'N',
            'ipaddr' => 'N',
            'ipanonymize' => 'N',
            'refurl' => 'N'
        ]);

        $surveyUpdate = new GeneralSettings(
            $mockSet->modelPermission,
            $mockSet->modelSurvey,
            $mockSet->yiiApp,
            $mockSet->sessionData,
            $mockSet->pluginManager,
            $mockSet->languageConsistency
        );

        $surveyUpdate->update(1, [
            'anonymized' => 'Y',
            'savetimings' => 'Y',
            'datestamp' => 'Y',
            'ipaddr' => 'Y',
            'ipanonymize' => 'Y',
            'refurl' => 'Y'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('Y', $attributes['anonymized']);
        $this->assertEquals('Y', $attributes['savetimings']);
        $this->assertEquals('Y', $attributes['datestamp']);
        $this->assertEquals('Y', $attributes['ipaddr']);
        $this->assertEquals('Y', $attributes['ipanonymize']);
        $this->assertEquals('Y', $attributes['refurl']);
    }
}
