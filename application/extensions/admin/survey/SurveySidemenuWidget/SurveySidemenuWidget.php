<?php

class SurveySidemenuWidget extends WhSelect2
{
    public $sid;
    public $surveySettings;
    public $surveyMenu;
    public $activePanel;
    public $allLanguages;
    public $presentation;
    /**
     * Runs the widget.
     */
    public function run()
    {
        $this->render('sidemenu', [
            'sid' => $this->sid,
            'menu' => $this->surveyMenu,
            'presentation' => $this->presentation,
            'settings' => $this->surveySettings
        ]);
    }

    public function init()
    {
        $this->registerClientScript();

        $this->surveyMenu = $this->getSurveyMenu();
        $this->surveySettings = $this->getSurveySettings();
        $this->presentation = $this->getPresentationSettings();
        $this->activePanel = $this->getActivePanel();
        $this->allLanguages = Survey::model()->findByPk($this->sid)->allLanguages;
    }

    public function getActivePanel()
    {
        if (App()->request->getPathInfo() == 'quickTranslation/index') {
            return 'survey-quick-translation';
        } elseif (App()->request->getPathInfo() == 'surveyPermissions/index') {
            return 'survey-permissions-panel';
        } else {
            $currentPage = App()->request->getPathInfo() . '?' . App()->request->getQueryString();
            foreach ($this->surveyMenu as $item) {
                if (strpos($item['url'], $currentPage)) {
                    return 'survey-menu-panel';
                }
            }
            foreach ($this->presentation as $item) {
                if (strpos($item['url'], $currentPage)) {
                    return 'survey-presentation-panel';
                }
            }
        }
        return 'survey-settings-panel';
    }

    public function getSurveyMenu()
    {
        $oSurvey = Survey::model()->findByPk($this->sid);
        return array(
            [
                'name' => gT('Overview questions & groups'),
                'url' => App()->createUrl('questionAdministration/listQuestions/', array('surveyid' => $this->sid)),
                'enabled' => true
            ],
            [
                'name' => gT('Survey participants'),
                'url' => App()->createUrl('admin/tokens/', array('sa' => 'index', 'surveyid' => $this->sid)),
                'enabled' => true
            ],
            [
                'name' => gT('Email templates'),
                'url' => App()->createUrl('admin/emailtemplates/', array('sa' => 'index', 'surveyid' => $this->sid)),
                'enabled' => true
            ],
            [
                'name' => gT('Failed email notifications'),
                'url' => App()->createUrl('failedEmail/index/', array('surveyid' => $this->sid)),
                'enabled' => true
            ],
            [
                'name' => gT('Quotas'),
                'url' => App()->createUrl('quotas/index/', array('surveyid' => $this->sid)),
                'enabled' => true
            ],
            [
                'name' => gT('Assessments'),
                'url' => App()->createUrl('assessment/index/', array('surveyid' => $this->sid)),
                'enabled' => true
            ],
            [
                'name' => gT('Panel integration'),
                'url' => App()->createUrl('surveyAdministration/rendersidemenulink/', array('surveyid' => $this->sid, 'subaction' => 'panelintegration')),
                'enabled' => true
            ],
            [
                'name' => gT('Responses'),
                'url' => App()->createUrl('responses/browse/', array('surveyId' => $this->sid)),
                'enabled' => $oSurvey->active == 'Y'
            ],
            [
                'name' => gT('Statistics'),
                'url' => App()->createUrl('admin/statistics/', array('sa' => 'index', 'surveyid' => $this->sid)),
                'enabled' => $oSurvey->active == 'Y'
            ],
            [
                'name' => gT('Resources'),
                'url' => App()->createUrl('surveyAdministration/rendersidemenulink/', array('surveyid' => $this->sid, 'subaction' => 'resources')),
                'enabled' => true
            ],
            [
                'name' => gT('Simple plugins'),
                'url' => App()->createUrl('surveyAdministration/rendersidemenulink/', array('surveyid' => $this->sid, 'subaction' => 'plugins')),
                'enabled' => true
            ],
        );
    }
    public function getSurveySettings()
    {
        return array(
            [
                'name' => gT('Overview'),
                'url' => App()->createUrl('surveyAdministration/view/', array('surveyid' => $this->sid))
            ],
            [
                'name' => gT('General'),
                'url' => App()->createUrl('editorLink/index', ['route' => 'survey/' . $this->sid . '/general/setting']),
            ],
            [
                'name' => gT('Text elements'),
                'url' => App()->createUrl('surveyAdministration/rendersidemenulink/', array('surveyid' => $this->sid, 'subaction' => 'surveytexts')),
            ],
            [
                'name' => gT('Privacy Policy'),
                'url' => App()->createUrl('editorLink/index', ['route' => 'survey/' . $this->sid . '/privacyPolicy/setting']),
            ],
            [
                'name' => gT('Participants'),
                'url' => App()->createUrl('surveyAdministration/rendersidemenulink/', array('surveyid' => $this->sid, 'subaction' => 'tokens')),
            ],
            [
                'name' => gT('Publication & access'),
                'url' => App()->createUrl('surveyAdministration/rendersidemenulink/', array('surveyid' => $this->sid, 'subaction' => 'publication')),
            ],
            [
                'name' => gT('Notifications & data'),
                'url' => App()->createUrl('surveyAdministration/rendersidemenulink/', array('surveyid' => $this->sid, 'subaction' => 'notification')),
            ]
        );
    }

    public function getPresentationSettings()
    {
        return array(
            [
                'name' => gT('Presentation'),
                'url' => App()->createUrl('editorLink/index', ['route' => 'survey/' . $this->sid . '/presentation/presentation']),
                //'url' => App()->createUrl('surveyAdministration/rendersidemenulink/', array('surveyid' => $this->sid, 'subaction' => 'presentation')),
            ],
            [
                'name' => gT('Theme options'),
                'url' => App()->createUrl('themeOptions/updateSurvey/', array('surveyid' => $this->sid)),
            ],
        );
    }

    /**
     * Registers required script files
     * @return void
     */
    public function registerClientScript()
    {
        App()->getClientScript()->registerScriptFile(
            App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/sidemenu.js'),
            CClientScript::POS_END
        );
        App()->getClientScript()->registerCssFile(
            App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/sidemenu.css')
        );
    }
}
