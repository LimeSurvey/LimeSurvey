<?php

namespace ls\tests\unit\services\SurveyAggregateService\GeneralSettings;

use ls\tests\TestBaseClass;
use LimeSurvey\PluginManager\PluginManager;
use Mockery;

/**
 * @group services
 */
class GeneralSettingsDispatchesEventsTest extends TestBaseClass
{
    public function testDispatchesNewSurveySettings()
    {
        $eventsTriggered = [];

        $mockSet = (new GeneralSettingsMockSetFactory)->make();
        $mockSet->pluginManager = Mockery::mock(PluginManager::class)
            ->makePartial();
        $mockSet->pluginManager->shouldReceive('dispatchEvent')
            ->withArgs(function ($arg) use (&$eventsTriggered) {
                $eventsTriggered[] = $arg->getEventName();
                return true;
            });

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'plugin' => ['testSetting' => 1]
        ]);

        $this->assertContains('newSurveySettings', $eventsTriggered);
    }

    public function testDispatchesBeforeSurveySettingsSave()
    {
        $eventsTriggered = [];

        $mockSet = (new GeneralSettingsMockSetFactory)->make();
        $mockSet->pluginManager = Mockery::mock(PluginManager::class)
            ->makePartial();
        $mockSet->pluginManager->shouldReceive('dispatchEvent')
            ->withArgs(function ($arg) use (&$eventsTriggered) {
                $eventsTriggered[] = $arg->getEventName();
                return true;
            });

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, ['printanswers' => 'Y']);

        $this->assertContains('beforeSurveySettingsSave', $eventsTriggered);
    }
}
