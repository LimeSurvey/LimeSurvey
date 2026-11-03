<?php

include 'SideMenuActiveItemMapper.php';

class SurveySidemenuWidget extends WhSelect2
{
    /**
     * SVG path data (24x24 viewBox) of the Remix icons used for the sidebar icons,
     * taken from assets/fonts/font-src/remix/remixicon.symbol.svg.
     * Inline SVGs are used instead of the icon font so the icons render exactly like the React editor sidebar.
     */
    const SIDEBAR_ICON_PATHS = [
        'ri-bar-chart-horizontal-line' => 'M12 3V5H3V3H12ZM16 19V21H3V19H16ZM22 11V13H3V11H22Z',
        'ri-settings-3-fill' => 'M9.95401 2.2106C11.2876 1.93144 12.6807 1.92263 14.0449 2.20785C14.2219 3.3674 14.9048 4.43892 15.9997 5.07103C17.0945 5.70313 18.364 5.75884 19.4566 5.3323C20.3858 6.37118 21.0747 7.58203 21.4997 8.87652C20.5852 9.60958 19.9997 10.736 19.9997 11.9992C19.9997 13.2632 20.5859 14.3902 21.5013 15.1232C21.29 15.7636 21.0104 16.3922 20.6599 16.9992C20.3094 17.6063 19.9049 18.1627 19.4559 18.6659C18.3634 18.2396 17.0943 18.2955 15.9997 18.9274C14.9057 19.559 14.223 20.6294 14.0453 21.7879C12.7118 22.067 11.3187 22.0758 9.95443 21.7906C9.77748 20.6311 9.09451 19.5595 7.99967 18.9274C6.90484 18.2953 5.63539 18.2396 4.54272 18.6662C3.61357 17.6273 2.92466 16.4164 2.49964 15.1219C3.41412 14.3889 3.99968 13.2624 3.99968 11.9992C3.99968 10.7353 3.41344 9.60827 2.49805 8.87524C2.70933 8.23482 2.98894 7.60629 3.33942 6.99923C3.68991 6.39217 4.09443 5.83576 4.54341 5.33257C5.63593 5.75881 6.90507 5.703 7.99967 5.07103C9.09364 4.43942 9.7764 3.3691 9.95401 2.2106ZM11.9997 14.9992C13.6565 14.9992 14.9997 13.6561 14.9997 11.9992C14.9997 10.3424 13.6565 8.99923 11.9997 8.99923C10.3428 8.99923 8.99967 10.3424 8.99967 11.9992C8.99967 13.6561 10.3428 14.9992 11.9997 14.9992Z',
        'ri-paint-fill' => 'M19.2277 18.7323L20.9955 16.9645L22.7632 18.7323C23.7395 19.7086 23.7395 21.2915 22.7632 22.2678C21.7869 23.2441 20.204 23.2441 19.2277 22.2678C18.2514 21.2915 18.2514 19.7086 19.2277 18.7323ZM8.87861 1.07971L20.1923 12.3934C20.5828 12.7839 20.5828 13.4171 20.1923 13.8076L11.707 22.2929C11.3165 22.6834 10.6833 22.6834 10.2928 22.2929L1.80754 13.8076C1.41702 13.4171 1.41702 12.7839 1.80754 12.3934L9.58572 4.61525L7.4644 2.49393L8.87861 1.07971ZM10.9999 6.02946L3.92886 13.1005H18.071L10.9999 6.02946Z',
        'ri-file-text-line' => 'M21 8V20.9932C21 21.5501 20.5552 22 20.0066 22H3.9934C3.44495 22 3 21.556 3 21.0082V2.9918C3 2.45531 3.4487 2 4.00221 2H14.9968L21 8ZM19 9H14V4H5V20H19V9ZM8 7H11V9H8V7ZM8 11H16V13H8V11ZM8 15H16V17H8V15Z',
        'ri-group-line' => 'M2 22C2 17.5817 5.58172 14 10 14C14.4183 14 18 17.5817 18 22H16C16 18.6863 13.3137 16 10 16C6.68629 16 4 18.6863 4 22H2ZM10 13C6.685 13 4 10.315 4 7C4 3.685 6.685 1 10 1C13.315 1 16 3.685 16 7C16 10.315 13.315 13 10 13ZM10 11C12.21 11 14 9.21 14 7C14 4.79 12.21 3 10 3C7.79 3 6 4.79 6 7C6 9.21 7.79 11 10 11ZM18.2837 14.7028C21.0644 15.9561 23 18.752 23 22H21C21 19.564 19.5483 17.4671 17.4628 16.5271L18.2837 14.7028ZM17.5962 3.41321C19.5944 4.23703 21 6.20361 21 8.5C21 11.3702 18.8042 13.7252 16 13.9776V11.9646C17.6967 11.7222 19 10.264 19 8.5C19 7.11935 18.2016 5.92603 17.041 5.35635L17.5962 3.41321Z',
        'ri-translate-2' => 'M18.5 10L22.9 21H20.745L19.544 18H15.454L14.255 21H12.101L16.5 10H18.5ZM10 2V4H16V6L14.0322 6.0006C13.2425 8.36616 11.9988 10.5057 10.4115 12.301C11.1344 12.9457 11.917 13.5176 12.7475 14.0079L11.9969 15.8855C10.9237 15.2781 9.91944 14.5524 8.99961 13.7249C7.21403 15.332 5.10914 16.5553 2.79891 17.2734L2.26257 15.3442C4.2385 14.7203 6.04543 13.6737 7.59042 12.3021C6.46277 11.0281 5.50873 9.57985 4.76742 8.00028L7.00684 8.00037C7.57018 9.03885 8.23979 10.0033 8.99967 10.877C10.2283 9.46508 11.2205 7.81616 11.9095 6.00101L2 6V4H8V2H10ZM17.5 12.8852L16.253 16H18.745L17.5 12.8852Z',
    ];

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
