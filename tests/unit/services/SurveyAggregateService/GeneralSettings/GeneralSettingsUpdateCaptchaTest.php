<?php

namespace ls\tests\unit\services\SurveyAggregateService\GeneralSettings;

use ls\tests\TestBaseClass;

class GeneralSettingsUpdateCaptchaTest extends TestBaseClass
{
    public function testSetCaptchaDirect()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'usecaptcha' => 'N'

        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'usecaptcha' => 'E'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals(
            'E',
            $attributes['usecaptcha']
        );
    }

    public function testDoNothingOnNoCaptchaInput()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'usecaptcha' => 'E'
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals(
            'E',
            $attributes['usecaptcha']
        );
    }

    public function testDoNothingOnAllNullValues()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'usecaptcha' => 'E'

        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'usecaptcha_surveyaccess' => null,
            'usecaptcha_registration' => null,
            'usecaptcha_saveandload' => null
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals(
            'E',
            $attributes['usecaptcha']
        );
    }



    public function testIIIProducesE()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'usecaptcha' => 'N'

        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'usecaptcha_surveyaccess' => 'I',
            'usecaptcha_registration' => 'I',
            'usecaptcha_saveandload' => 'I'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals(
            'E',
            $attributes['usecaptcha']
        );
    }
}
