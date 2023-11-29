<?php

namespace ls\tests\unit\services\SurveyAggregateService\GeneralSettings;

use Survey;
use Permission;
use LSYii_Application;
use CHttpSession;
use Mockery;
use LimeSurvey\PluginManager\PluginManager;
use LimeSurvey\Models\Services\SurveyAggregateService\LanguageConsistency;
use User;

/**
 * General Settings Mock Factory
 *
 * Reusable initialisation of mock dependencies for use in GeneralSettings tests.
 */
class GeneralSettingsMockSetFactory
{
    /**
     * @param ?GeneralSettingsMockSet $init
     */
    public function make(GeneralSettingsMockSet $init = null): GeneralSettingsMockSet
    {
        $mockSet = new GeneralSettingsMockSet;

        $mockSet->modelPermission = ($init && isset($init->modelPermission))
            ? $init->modelPermission
            : $this->getMockModelPermission();

        $mockSet->survey = ($init && isset($init->survey))
            ? $init->survey
            : $this->getMockSurvey();

        $mockSet->modelSurvey = ($init && isset($init->modelSurvey))
            ? $init->modelSurvey
            : $this->getMockModelSurvey($mockSet->survey);

        $mockSet->yiiApp = ($init && isset($init->yiiApp))
            ? $init->yiiApp
            : $this->getMockYiiApp();

        $mockSet->session = ($init && isset($init->session))
            ? $init->session
            : $this->getMockSession();

        $mockSet->pluginManager = ($init && isset($init->pluginManager))
            ? $init->pluginManager
            : $this->getMockPluginManager();

        $mockSet->languageConsistency = ($init && isset($init->languageConsistency))
            ? $init->languageConsistency
            : $this->getMockLanguageConsistency();

        $mockSet->user = ($init && isset($init->user))
            ? $init->user
            : $this->getMockUser();

        $mockSet->modelUser = ($init && isset($init->modelUser))
            ? $init->modelUser
            : $this->getMockModelUser($mockSet->user);

        return $mockSet;
    }

    private function getMockModelPermission(): Permission
    {
        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission->shouldReceive('hasSurveyPermission')
            ->andReturn(true);
        $modelPermission->shouldReceive('hasGlobalPermission')
            ->andReturn(true);

        return $modelPermission;
    }

    private function getMockSurvey(): Survey
    {
        $survey = Mockery::mock(Survey::class)
            ->makePartial();
        $survey->shouldReceive('save')
            ->andReturn(true);
        $survey->shouldReceive('setAttributes')
            ->passthru();
        $survey->setAttributes([]);
        $survey->shouldReceive('getAttributes')
            ->passthru();
        $survey->getAttributes([]);

        return $survey;
    }

    private function getMockModelSurvey(Survey $survey): Survey
    {
        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();
        $modelSurvey->shouldReceive('findByPk')
            ->andReturn($survey);

        return $modelSurvey;
    }

    private function getMockYiiApp(): LSYii_Application
    {
        return Mockery::mock(LSYii_Application::class)
            ->makePartial();
    }

    private function getMockSession(): CHttpSession
    {
        $mockSession = Mockery::mock(CHttpSession::class)
            ->makePartial();
        return $mockSession;
    }

    private function getMockPluginManager(): PluginManager
    {
        $pluginManager = Mockery::mock(PluginManager::class)
            ->makePartial();
        $pluginManager->shouldReceive('dispatchEvent')
            ->andReturn(null);

        return $pluginManager;
    }

    private function getMockLanguageConsistency(): LanguageConsistency
    {
        $mockLanguageConsistency = Mockery::mock(
            LanguageConsistency::class
        )->makePartial();
        $mockLanguageConsistency->shouldReceive('update')
            ->andReturn(null);
        return $mockLanguageConsistency;
    }

    private function getMockUser(): User
    {
        $mockUser = Mockery::mock(User::class)
            ->makePartial();
        return $mockUser;
    }

    private function getMockModelUser(User $user): User
    {
        $mockModelUser = Mockery::mock(User::class)
            ->makePartial();
        $mockModelUser->shouldReceive('findByPk')
            ->andReturn($user);
        return $mockModelUser;
    }
}
