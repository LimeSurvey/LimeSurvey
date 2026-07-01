<?php

namespace ls\tests\unit\services\SurveyAggregateService\GeneralSettings;

use ls\tests\TestBaseClass;
use LimeSurvey\Models\Services\SurveyAggregateService\GeneralSettings;

class GeneralSettingsUpdateGoogleAnalyticsKeyTest extends TestBaseClass
{
    public function testConvertsGToGlobalKey()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'googleanalyticsapikey' => ''
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'googleanalyticsapikey' => 'G'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals(
            GeneralSettings::GA_GLOBAL_KEY,
            $attributes['googleanalyticsapikey']
        );
    }

    public function testConvertsNToEmptyString()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'googleanalyticsapikey' => ''
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'googleanalyticsapikey' => 'N'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals(
            '',
            $attributes['googleanalyticsapikey']
        );
    }
}
