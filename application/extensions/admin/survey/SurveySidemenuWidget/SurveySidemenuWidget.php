<?php

class SurveySidemenuWidget extends WhSelect2
{
    public $sid;
    public $sideMenu;
    public $activePanel;
    public $allLanguages;
    public $surveyEntry;
    /**
     * Runs the widget.
     */
    public function run()
    {
        $this->render('sidemenu', [
            'sid' => $this->sid,
            'sideMenu' => $this->sideMenu,
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
                [ 'name' => 'participants' ],
                [ 'name' => 'emailtemplates'],
                [ 'name' => 'failedemail' ],
                [ 'name' => 'quotas' ],
                [ 'name' => 'assessments' ],
                [
                    'name' => 'panelintegration',
                    'route' => 'surveyAdministration/rendersidemenulink/',
                    'params' => array('surveyid' => $this->sid, 'subaction' => 'panelintegration')
                ],
                [
                    'name' => 'responses',
                    'disabled' => $oSurvey->active != 'Y'
                ],
                [
                    'name' => 'statistics',
                    'disabled' => $oSurvey->active != 'Y'
                ],
                [
                    'name' => 'resources',
                    'route' => 'surveyAdministration/rendersidemenulink/',
                    'params' => array('surveyid' => $this->sid, 'subaction' => 'resources'),
                ],
                [
                    'name' => 'plugins',
                    'route' => 'surveyAdministration/rendersidemenulink/',
                    'params' => array('surveyid' => $this->sid, 'subaction' => 'plugins'),
                ],
            ),
            'settings' => array(
                [
                    'name' => 'generalsettings',
                    'route' => 'editorLink/index',
                    'params' => array('route' => 'survey/' . $this->sid . '/settings/generalsettings'),
                ],
                [
                    'name' => 'datasecurity',
                    'route' => 'editorLink/index',
                    'params' => array('route' => 'survey/' . $this->sid . '/settings/datasecurity'),
                ],
                [
                    'name' => 'participants',
                    'route' => 'surveyAdministration/rendersidemenulink/',
                    'params' => array('surveyid' => $this->sid, 'subaction' => 'tokens'),
                ],
                [
                    'name' => 'publication',
                    'route' => 'surveyAdministration/rendersidemenulink/',
                    'params' =>  array('surveyid' => $this->sid, 'subaction' => 'publication'),
                ],
                [
                    'name' => 'notification',
                    'route' => 'surveyAdministration/rendersidemenulink/',
                    'params' =>  array('surveyid' => $this->sid, 'subaction' => 'notification'),
                ]
            ),
            'presentation' => array(
                [
                    'name' => 'presentation',
                    'route' => 'editorLink/index',
                    'params' =>  array('route' => 'survey/' . $this->sid . '/presentation/presentation'),
                ],
                [ 'name' => 'theme_options' ],
            )
        );

        foreach ($sideMenu as $k => $panel) {
            foreach ($panel as $i => $item) {
                if ($entry = $this->getSurveyEntry($item['name'])) {
                    $sideMenu[$k][$i]['name'] = $entry->menu_title;
                    if (!isset($item['route'])) {
                        $sideMenu[$k][$i]['url'] =
                            App()->createUrl($entry->menu_link, array('surveyid' => $this->sid));
                    } else {
                        $sideMenu[$k][$i]['url'] =
                            App()->createUrl($item['route'], $item['params']);
                    }
                } else {
                    unset($sideMenu[$k][$i]);
                }
            }
        }

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
