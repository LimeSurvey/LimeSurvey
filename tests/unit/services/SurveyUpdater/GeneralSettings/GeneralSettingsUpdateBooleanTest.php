<?php

namespace ls\tests\unit\services\SurveyUpdater\GeneralSettings;

use ls\tests\TestBaseClass;

use LimeSurvey\Models\Services\SurveyUpdater\GeneralSettings;

class GeneralSettingsUpdateBooleanTest extends TestBaseClass
{
    public function testUpdateAllowRegisterSetTrue()
    {
        $mockSet = (new GeneralSettingsMockFactory)->make();

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'allowregister' => 'N',
        ]);

        $surveyUpdater = new GeneralSettings(
            $mockSet->modelPermission,
            $mockSet->modelSurvey,
            $mockSet->yiiApp,
            $mockSet->sessionData,
            $mockSet->pluginManager,
            $mockSet->languageConsistency
        );

        $surveyUpdater->update(1, [
            'allowregister' => '1'
        ]);

        $this->assertEquals(
            'Y',
            $mockSet->survey->allowregister
        );
    }

    public function testUpdateAllowRegisterSetFalse()
    {
        $mockSet = (new GeneralSettingsMockFactory)->make();

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'allowregister' => 'Y',
        ]);

        $surveyUpdater = new GeneralSettings(
            $mockSet->modelPermission,
            $mockSet->modelSurvey,
            $mockSet->yiiApp,
            $mockSet->sessionData,
            $mockSet->pluginManager,
            $mockSet->languageConsistency
        );

        $surveyUpdater->update(1, [
            'allowregister' => '0'
        ]);

        $this->assertEquals(
            'N',
            $mockSet->survey->allowregister
        );
    }

    public function testUpdateAllowRegisterSetInherit()
    {
        $mockSet = (new GeneralSettingsMockFactory)->make();

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'allowregister' => 'Y',
        ]);

        $surveyUpdater = new GeneralSettings(
            $mockSet->modelPermission,
            $mockSet->modelSurvey,
            $mockSet->yiiApp,
            $mockSet->sessionData,
            $mockSet->pluginManager,
            $mockSet->languageConsistency
        );

        $surveyUpdater->update(1, [
            'allowregister' => 'I'
        ]);

        $this->assertEquals(
            'I',
            $mockSet->survey->allowregister
        );
    }
}
