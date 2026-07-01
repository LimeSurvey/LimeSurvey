<?php

namespace ls\tests\unit\services\SurveyAggregateService\GeneralSettings;

use ls\tests\TestBaseClass;

/**
 * @group services
 */
class GeneralSettingsBooleanTest extends TestBaseClass
{
    public function testUpdateAllowRegisterSetTrue()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'allowregister' => 'N',
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'allowregister' => '1'
        ]);

        $this->assertEquals(
            'Y',
            $mockSet->survey->allowregister
        );
    }

    public function testUpdateAllowRegisterSetFalse()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'allowregister' => 'Y',
        ]);
        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'allowregister' => '0'
        ]);

        $this->assertEquals(
            'N',
            $mockSet->survey->allowregister
        );
    }

    public function testUpdateAllowRegisterSetInherit()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'allowregister' => 'Y',
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'allowregister' => 'I'
        ]);

        $this->assertEquals(
            'I',
            $mockSet->survey->allowregister
        );
    }
}
