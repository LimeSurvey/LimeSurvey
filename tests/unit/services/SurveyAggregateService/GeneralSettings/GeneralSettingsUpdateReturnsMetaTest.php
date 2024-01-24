<?php

namespace ls\tests\unit\services\SurveyAggregateService\GeneralSettings;

use ls\tests\TestBaseClass;

class GeneralSettingsUpdateReturnsMetaTest extends TestBaseClass
{
    public function testUpdateReturnsMeta()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->shouldReceive('setAttributes')
            ->passthru();
            $mockSet->survey->setAttributes([
            'sid' => 1,
            'startdate' => '2023-12-01 00:00:00'
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $meta = $generalSettings->update(1, [
            'startdate' => '01.01.2024 13:45'
        ]);

        $this->assertIsArray($meta);

        $this->assertIsArray($meta);
        $this->assertArrayHasKey('updateFields', $meta);
        $this->assertIsArray($meta['updateFields']);
        $this->assertContains('startdate', $meta['updateFields']);
    }
}
