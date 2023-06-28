<?php

namespace ls\tests\unit\services\SurveyUpdater\GeneralSettings;

use Mockery;
use Permission;
use ls\tests\TestBaseClass;

class GeneralSettingsUpdateOwnerTest extends TestBaseClass
{
    public function testCanUpdateOwnerIdIfUserIsCurrentOwner()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->modelPermission->shouldReceive('hasGlobalPermission')
            ->andReturn(false);

        $mockSet->session['loginID'] = 123;

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'owner_id' => 123
        ], false);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'owner_id' => 456
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals(456, $attributes['owner_id']);
    }

    public function testCanNotUpdateOwnerIdIfUserIsNotCurrentOwner()
    {
        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission->shouldReceive('hasSurveyPermission')
            ->andReturn(true);
        $modelPermission->shouldReceive('hasGlobalPermission')
            ->andReturn(false);

        $mockSet = (new GeneralSettingsMockSetFactory)->make();
        $mockSet->modelPermission = $modelPermission;

        $mockSet->session['loginID'] = 456;

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'owner_id' => 123
        ], false);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'owner_id' => 456
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals(123, $attributes['owner_id']);
    }
}

