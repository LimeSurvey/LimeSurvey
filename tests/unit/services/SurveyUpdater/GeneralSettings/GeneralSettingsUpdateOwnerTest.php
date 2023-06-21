<?php

namespace ls\tests\unit\services\SurveyUpdater\GeneralSettings;

use Mockery;
use Permission;
use ls\tests\TestBaseClass;
use LimeSurvey\Models\Services\SurveyUpdater\GeneralSettings;

class GeneralSettingsUpdateOwnerTest extends TestBaseClass
{
    public function testCanUpdateOwnerIdIfUserIsCurrentOwner()
    {
        $mockSet = (new GeneralSettingsMockFactory)->make();

        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission->shouldReceive('hasSurveyPermission')
            ->andReturn(true);
        $modelPermission->shouldReceive('hasGlobalPermission')
            ->andReturn(false);

        $mockSet->sessionData['loginID'] = 123;

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'owner_id' => 123
        ], false);

        $surveyUpdater = new GeneralSettings(
            $modelPermission,
            $mockSet->modelSurvey,
            $mockSet->yiiApp,
            $mockSet->sessionData,
            $mockSet->pluginManager,
            $mockSet->languageConsistency
        );

        $surveyUpdater->update(1, [
            'owner_id' => 456
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals(456, $attributes['owner_id']);
    }

    public function testCanNotUpdateOwnerIdIfUserIsNotCurrentOwner()
    {
        $mockSet = (new GeneralSettingsMockFactory)->make();

        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission->shouldReceive('hasSurveyPermission')
            ->andReturn(true);
        $modelPermission->shouldReceive('hasGlobalPermission')
            ->andReturn(false);

        $mockSet->sessionData['loginID'] = 456;

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'owner_id' => 123
        ], false);

        $surveyUpdater = new GeneralSettings(
            $modelPermission,
            $mockSet->modelSurvey,
            $mockSet->yiiApp,
            $mockSet->sessionData,
            $mockSet->pluginManager,
            $mockSet->languageConsistency
        );

        $surveyUpdater->update(1, [
            'owner_id' => 456
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals(123, $attributes['owner_id']);
    }
}

