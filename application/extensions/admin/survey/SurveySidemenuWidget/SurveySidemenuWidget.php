<?php

class SurveySidemenuWidget extends WhSelect2
{
    public $sid;
//    public $surveySettings;
    public $sideMenu;
    public $activePanel;
    public $allLanguages;
//    public $presentation;
    public $surveyEntry;
    /**
     * Runs the widget.
     */
    public function run()
    {
        $this->render('sidemenu', [
            'sid' => $this->sid,
            'sideMenu' => $this->sideMenu,
//            'presentation' => $this->presentation,
//            'settings' => $this->surveySettings
        ]);
    }

    public function init()
    {
        $this->registerClientScript();
        $this->surveyEntry = SurveymenuEntries::model();

        $this->sideMenu = $this->getSideMenu();
        $this->highlightActiveMenuItem();
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
            foreach ($this->sideMenu as $k => $menu) {
                foreach ($menu as $menuItem) {
                    if (isset($menuItem['selected'])) {
                        return "survey-$k-panel";
                    }
                }
            }
        }
        return 'survey-settings-panel';
    }

    public function getSurveyEntry($entryName)
    {
        return $this->surveyEntry->find(
            'name=:name',
            array(':name' => $entryName)
        );
    }

    public function highlightActiveMenuItem()
    {
        $currentUrl = App()->request->requestUri;
        foreach ($this->sideMenu as $k => $menu) {
            foreach ($menu as $i => $menuItem) {
                if (
                    $menuItem['url'] == $currentUrl
                    || str_replace("/index.php", "", $menuItem['url']) == $currentUrl
                ) {
                    $this->sideMenu[$k][$i]['selected'] = true;
                }
            }
        }
    }

    public function getSideMenu()
    {
        $oSurvey = Survey::model()->findByPk($this->sid);
        $sideMenu = array(
             'menu' => array(
                 [
                     'name' => $this->getSurveyEntry('listQuestions')->menu_title,
                     'url' => App()->createUrl(
                         $this->getSurveyEntry('listQuestions')->menu_link,
                         array('surveyid' => $this->sid)
                     ),
                     'enabled' => true
                 ],
                 [
                     'name' => $this->getSurveyEntry('participants')->menu_title,
                     'url' => App()->createUrl(
                         $this->getSurveyEntry('participants')->menu_link,
                         array('surveyid' => $this->sid)
                     ),
                     'enabled' => true
                 ],
                 [
                     'name' => $this->getSurveyEntry('emailtemplates')->menu_title,
                     'url' => App()->createUrl(
                         $this->getSurveyEntry('emailtemplates')->menu_link,
                         array('surveyid' => $this->sid)
                     ),
                     'enabled' => true
                 ],
                 [
                     'name' => $this->getSurveyEntry('failedemail')->menu_title,
                     'url' => App()->createUrl(
                         $this->getSurveyEntry('failedemail')->menu_link,
                         array('surveyid' => $this->sid)
                     ),
                     'enabled' => true
                 ],
                 [
                     'name' => $this->getSurveyEntry('quotas')->menu_title,
                     'url' => App()->createUrl(
                         $this->getSurveyEntry('quotas')->menu_link,
                         array('surveyid' => $this->sid)
                     ),
                     'enabled' => true
                 ],
                 [
                     'name' => $this->getSurveyEntry('assessments')->menu_title,
                     'url' => App()->createUrl(
                         $this->getSurveyEntry('assessments')->menu_link,
                         array('surveyid' => $this->sid)
                     ),
                     'enabled' => true
                 ],
                 [
                     'name' => $this->getSurveyEntry('panelintegration')->menu_title,
                     'url' => App()->createUrl(
                         'surveyAdministration/rendersidemenulink/',
                         array('surveyid' => $this->sid, 'subaction' => 'panelintegration')
                     ),
                     'enabled' => true
                 ],
                 [
                     'name' => $this->getSurveyEntry('responses')->menu_title,
                     'url' => App()->createUrl(
                         $this->getSurveyEntry('responses')->menu_link,
                         array('surveyId' => $this->sid)
                     ),
                     'enabled' => $oSurvey->active == 'Y'
                 ],
                 [
                     'name' => $this->getSurveyEntry('statistics')->menu_title,
                     'url' => App()->createUrl(
                         $this->getSurveyEntry('statistics')->menu_link,
                         array('surveyid' => $this->sid)
                     ),
                     'enabled' => $oSurvey->active == 'Y'
                 ],
                 [
                     'name' => $this->getSurveyEntry('resources')->menu_title,
                     'url' => App()->createUrl(
                         'surveyAdministration/rendersidemenulink/',
                         array('surveyid' => $this->sid, 'subaction' => 'resources')
                     ),
                     'enabled' => true
                 ],
                 [
                     'name' => $this->getSurveyEntry('plugins')->menu_title,
                     'url' => App()->createUrl(
                         'surveyAdministration/rendersidemenulink/',
                         array('surveyid' => $this->sid, 'subaction' => 'plugins')
                     ),
                     'enabled' => true
                 ],
             ),
             'settings' => array(
                [
                    'name' => $this->getSurveyEntry('overview')->menu_title,
                    'url' => App()->createUrl(
                        $this->getSurveyEntry('overview')->menu_link,
                        array('surveyid' => $this->sid)
                    )
                ],
                [
                    'name' => $this->getSurveyEntry('generalsettings')->menu_title,
                    'url' => App()->createUrl('editorLink/index', ['route' => 'survey/' . $this->sid . '/settings/general']),
                ],
                [
                    'name' => $this->getSurveyEntry('surveytexts')->menu_title,
                    'url' => App()->createUrl('surveyAdministration/rendersidemenulink/', array('surveyid' => $this->sid, 'subaction' => 'surveytexts')),
                ],
                [
                    'name' => $this->getSurveyEntry('datasecurity')->menu_title,
                    'url' => App()->createUrl('editorLink/index', ['route' => 'survey/' . $this->sid . '/settings/privacyPolicy']),
                ],
                [
                    'name' => $this->getSurveyEntry('participants')->menu_title,
                    'url' => App()->createUrl('surveyAdministration/rendersidemenulink/', array('surveyid' => $this->sid, 'subaction' => 'tokens')),
                ],
                [
                    'name' => $this->getSurveyEntry('publication')->menu_title,
                    'url' => App()->createUrl('surveyAdministration/rendersidemenulink/', array('surveyid' => $this->sid, 'subaction' => 'publication')),
                ],
                [
                    'name' => $this->getSurveyEntry('notification')->menu_title,
                    'url' => App()->createUrl('surveyAdministration/rendersidemenulink/', array('surveyid' => $this->sid, 'subaction' => 'notification')),
                ]
             ),
             'presentation' => array(
                 [
                     'name' => $this->getSurveyEntry('presentation')->menu_title,
                     'url' => App()->createUrl('editorLink/index', ['route' => 'survey/' . $this->sid . '/presentation/presentation']),
                 ],
                 [
                     'name' => $this->getSurveyEntry('theme_options')->menu_title,
                     'url' => App()->createUrl(
                         $this->getSurveyEntry('theme_options')->menu_link,
                         array('surveyid' => $this->sid)
                     ),
                 ],
             )
        );

        return $sideMenu;
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
