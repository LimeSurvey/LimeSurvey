<?php

namespace ls\tests\unit\services\SurveyAggregateService\GeneralSettings;

use Survey;
use Permission;
use LSYii_Application;
use CHttpSession;
use LimeSurvey\PluginManager\PluginManager;
use LimeSurvey\Models\Services\SurveyAggregateService\LanguageConsistency;
use User;
use LimeSurvey\Models\Services\SurveyAccessModeService;

class GeneralSettingsMockSet
{
    public Permission $modelPermission;
    public Survey $survey;
    public Survey $modelSurvey;
    public LSYii_Application $yiiApp;
    public CHttpSession $session;
    public PluginManager $pluginManager;
    public LanguageConsistency $languageConsistency;
    public User $user;
    public User $modelUser;
    public SurveyAccessModeService $surveyAccessModeService;
}
