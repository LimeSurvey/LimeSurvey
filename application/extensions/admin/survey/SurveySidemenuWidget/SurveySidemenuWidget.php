<?php

include 'SideMenuActiveItemMapper.php';

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

    /**
     * Initializes the widget.
     */
    public function init()
    {
        $this->registerClientScript();
        $this->surveyEntry = SurveymenuEntries::model();

        $this->sideMenu = $this->getSideMenu();
        $this->highlightActiveMenuItem();
        $this->activePanel = $this->getActivePanel();
        $this->allLanguages = Survey::model()->findByPk($this->sid)->allLanguages;
    }

    /**
     * Gets the active panel.
     *
     * @return string ID of the panel
     */
    public function getActivePanel()
    {
        // Checked by controller: The path also contains the parameters if the URL is path formatted (e.g. after saving)
        $controllerId = App()->getController()->id;
        if ($controllerId == 'quickTranslation') {
            return 'survey-quick-translation';
        } elseif ($controllerId == 'surveyPermissions') {
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
        // Fall back to the first panel the user has entries in
        foreach (['settings', 'menu', 'presentation'] as $panel) {
            if (!empty($this->sideMenu[$panel])) {
                return "survey-$panel-panel";
            }
        }
        return '';
    }

    /**
     * Gets survey entry by name.
     */
    public function getSurveyEntry($entryName)
    {
        return $this->surveyEntry->find(
            'name=:name',
            array(':name' => $entryName)
        );
    }

    /**
     * Highlights the active menu item.
     */
    public function highlightActiveMenuItem()
    {
        $currentUrl = App()->request->requestUri;

        foreach ($this->sideMenu as $menutype => $menu) {
            foreach ($menu as $i => $menuItem) {
                if ((new SideMenuActiveItemMapper())->match($menuItem['url'], $menuItem['name'], $this->sid)) {
                    $this->sideMenu[$menutype][$i]['selected'] = true;
                }
            }
        }
    }

    /**
     * Gets the side menu structure.
     */
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
                    'disabled' => $oSurvey->active != 'Y',
                    'route' => 'editorLink/index',
                    'params' => array('route' => 'responses/' . $this->sid . '/results/responses'),
                ],
                [
                    'name' => 'statistics',
                    'disabled' => $oSurvey->active != 'Y',
                    'route' => 'editorLink/index',
                    'params' => array('route' => 'responses/' . $this->sid . '/results/statistics'),
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
                    // Participant settings: The permission comes from the participant settings entry
                    'permissionEntry' => 'tokens',
                    'route' => 'editorLink/index',
                    'params' => array('route' => 'survey/' . $this->sid . '/settings/tokens'),
                ],
                [
                    'name' => 'publication',
                    'route' => 'editorLink/index/',
                    'params' => array('route' => 'survey/' . $this->sid . '/settings/publication'),
                ],
                [
                    'name' => 'notification',
                    'route' => 'editorLink/index/',
                    'params' => array('route' => 'survey/' . $this->sid . '/settings/notification'),
                ]
            ),
            'presentation' => array(
                [
                    'name' => 'theme_options',
                    'route' => 'editorLink/index',
                    'params' =>  array('route' => 'survey/' . $this->sid . '/presentation/theme_options'),
                ],
                [
                    'name' => 'presentation',
                    'route' => 'editorLink/index',
                    'params' =>  array('route' => 'survey/' . $this->sid . '/presentation/presentation'),
                ],
            )
        );

        foreach ($sideMenu as $k => $panel) {
            foreach ($panel as $i => $item) {
                $entry = $this->getSurveyEntry($item['name']);
                $permissionEntry = isset($item['permissionEntry']) ? $this->getSurveyEntry($item['permissionEntry']) : $entry;
                if ($entry && $permissionEntry && $permissionEntry->userHasPermission($this->sid)) {
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
     * Gets the URL a panel icon leads to: The first enabled entry of the panel.
     *
     * @param string $panel Key of the panel in the side menu
     * @return string|null Null if the panel has no enabled entry
     */
    public function getPanelUrl($panel)
    {
        foreach ($this->sideMenu[$panel] as $item) {
            if (empty($item['disabled'])) {
                return $item['url'];
            }
        }
        return null;
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
