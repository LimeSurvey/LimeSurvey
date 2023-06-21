<?php

namespace ls\tests\unit\services\SurveyUpdater\GeneralSettings;

use ls\tests\TestBaseClass;

use LimeSurvey\Models\Services\SurveyUpdater\GeneralSettings;

class GeneralSettingsUpdateDatesTest extends TestBaseClass
{
    public function testUpdateStartDate()
    {
        $mockSet = (new GeneralSettingsMockFactory)
            ->make();

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'startdate' => '2023-12-01 12:00:00'
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
            'startdate' => '01.01.2024 13:45'
        ]);

        $this->assertEquals(
            '2024-01-01 13:45:00',
            $mockSet->survey->startdate
        );
    }

    public function testUpdateExpiresDate()
    {
        $mockSet = (new GeneralSettingsMockFactory)
            ->make();

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'expires' => '2023-12-01 00:00:00',
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
            'expires' => '01.01.2024 13:45'
        ]);

        $this->assertEquals(
            '2024-01-01 13:45:00',
            $mockSet->survey->expires
        );
    }
}
