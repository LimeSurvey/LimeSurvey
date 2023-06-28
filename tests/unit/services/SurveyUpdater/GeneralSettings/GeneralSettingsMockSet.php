<?php

namespace ls\tests\unit\services\SurveyUpdater\GeneralSettings;

use Survey;
use Permission;
use LSYii_Application;
use CHttpSession;
use LimeSurvey\PluginManager\PluginManager;
use LimeSurvey\Models\Services\SurveyUpdater\LanguageConsistency;

class GeneralSettingsMockSet
{
    public Permission $modelPermission;
    public Survey $survey;
    public Survey $modelSurvey;
    public LSYii_Application $yiiApp;
    public CHttpSession $session;
    public PluginManager $pluginManager;
    public LanguageConsistency $languageConsistency;
}
