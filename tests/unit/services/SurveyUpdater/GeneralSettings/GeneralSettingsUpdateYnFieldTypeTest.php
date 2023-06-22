<?php

namespace ls\tests\unit\services\SurveyUpdater\GeneralSettings;

use ls\tests\TestBaseClass;
use LimeSurvey\Models\Services\SurveyUpdater\GeneralSettings;

class GeneralSettingsUpdateYnFieldType extends TestBaseClass
{
    public function testAcceptsY()
    {
        $mockSet = (new GeneralSettingsMockFactory)->make();

        $mockSet->survey->setAttributes([
            'usecookie' => 'N'
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
            'usecookie' => 'Y'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('Y', $attributes['usecookie']);
    }

    public function testAcceptsN()
    {
        $mockSet = (new GeneralSettingsMockFactory)->make();

        $mockSet->survey->setAttributes([
            'usecookie' => 'Y'
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
            'usecookie' => 'N'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('N', $attributes['usecookie']);
    }

    public function testAcceptsI()
    {
        $mockSet = (new GeneralSettingsMockFactory)->make();

        $mockSet->survey->setAttributes([
            'usecookie' => 'Y'
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
            'usecookie' => 'I'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('I', $attributes['usecookie']);
    }

    public function testAcceptsFalsyZero()
    {
        $mockSet = (new GeneralSettingsMockFactory)->make();

        $mockSet->survey->setAttributes([
            'usecookie' => 'Y'
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
            'usecookie' => 0
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('N', $attributes['usecookie']);
    }

    public function testAcceptsFalsyStringZero()
    {
        $mockSet = (new GeneralSettingsMockFactory)->make();

        $mockSet->survey->setAttributes([
            'usecookie' => 'Y'
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
            'usecookie' => '0'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('N', $attributes['usecookie']);
    }

    public function testAcceptsFalsyEmpty()
    {
        $mockSet = (new GeneralSettingsMockFactory)->make();

        $mockSet->survey->setAttributes([
            'usecookie' => 'Y'
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
            'usecookie' => ''
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('N', $attributes['usecookie']);
    }

    public function testAcceptsNoneFalsyOne()
    {
        $mockSet = (new GeneralSettingsMockFactory)->make();

        $mockSet->survey->setAttributes([
            'usecookie' => 'N'
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
            'usecookie' => 1
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('Y', $attributes['usecookie']);
    }

    public function testAcceptsNoneFalsyStringOne()
    {
        $mockSet = (new GeneralSettingsMockFactory)->make();

        $mockSet->survey->setAttributes([
            'usecookie' => 'N'
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
            'usecookie' => '1'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('Y', $attributes['usecookie']);
    }
}
