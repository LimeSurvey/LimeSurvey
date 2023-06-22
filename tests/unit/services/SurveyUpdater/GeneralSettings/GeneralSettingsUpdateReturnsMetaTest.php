<?php

namespace ls\tests\unit\services\SurveyUpdater\GeneralSettings;

use ls\tests\TestBaseClass;

use LimeSurvey\Models\Services\SurveyUpdater\GeneralSettings;

class GeneralSettingsUpdateReturnsMetaTest extends TestBaseClass
{
    public function testUpdateReturnsMeta()
    {
        $mockSet = (new GeneralSettingsMockFactory)->make();

        $mockSet->survey->shouldReceive('setAttributes')
            ->passthru();
            $mockSet->survey->setAttributes([
            'sid' => 1,
            'startdate' => '2023-12-01 00:00:00'
        ]);

        $surveyUpdater = new GeneralSettings(
            $mockSet->modelPermission,
            $mockSet->modelSurvey,
            $mockSet->yiiApp,
            $mockSet->sessionData,
            $mockSet->pluginManager,
            $mockSet->languageConsistency
        );

        $meta = $surveyUpdater->update(1, [
            'startdate' => '01.01.2024 13:45'
        ]);

        $this->assertIsArray($meta);

        $this->assertIsArray($meta);
        $this->assertArrayHasKey('updateFields', $meta);
        $this->assertIsArray($meta['updateFields']);
        $this->assertContains('startdate', $meta['updateFields']);
    }
}
