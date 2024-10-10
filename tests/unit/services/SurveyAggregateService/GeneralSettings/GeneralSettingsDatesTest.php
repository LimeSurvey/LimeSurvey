<?php

namespace ls\tests\unit\services\SurveyAggregateService\GeneralSettings;

use ls\tests\TestBaseClass;

/**
 * @group services
 */
class GeneralSettingsDatesTest extends TestBaseClass
{
    public function testUpdateStartDate()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'startdate' => '2023-12-01 12:00:00'
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'startdate' => '01.01.2024 13:45'
        ]);

        $this->assertEquals(
            '2024-01-01 13:45:00',
            $mockSet->survey->startdate
        );
    }

    public function testUpdateExpiresDate()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'expires' => '2023-12-01 00:00:00',
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'expires' => '01.01.2024 13:45'
        ]);

        $this->assertEquals(
            '2024-01-01 13:45:00',
            $mockSet->survey->expires
        );
    }
}
