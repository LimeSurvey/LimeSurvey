<?php

class SideBarWidget extends CWidget
{
    /**
     * SVG path data (24x24 viewBox) of the Remix icons used by the default boxes,
     * taken from assets/fonts/font-src/remix/remixicon.symbol.svg.
     * Inline SVGs are used instead of the icon font so the icons render exactly like the SurveySidemenuWidget
     * and the React editor sidebar. Icons not listed here are still rendered with the icon font.
     */
    const SIDEBAR_ICON_PATHS = [
        'ri-function-fill' => 'M3 4C3 3.44772 3.44772 3 4 3H10C10.5523 3 11 3.44772 11 4V10C11 10.5523 10.5523 11 10 11H4C3.44772 11 3 10.5523 3 10V4ZM3 14C3 13.4477 3.44772 13 4 13H10C10.5523 13 11 13.4477 11 14V20C11 20.5523 10.5523 21 10 21H4C3.44772 21 3 20.5523 3 20V14ZM13 4C13 3.44772 13.4477 3 14 3H20C20.5523 3 21 3.44772 21 4V10C21 10.5523 20.5523 11 20 11H14C13.4477 11 13 10.5523 13 10V4ZM13 14C13 13.4477 13.4477 13 14 13H20C20.5523 13 21 13.4477 21 14V20C21 20.5523 20.5523 21 20 21H14C13.4477 21 13 20.5523 13 20V14Z',
        'ri-settings-3-fill' => 'M9.95401 2.2106C11.2876 1.93144 12.6807 1.92263 14.0449 2.20785C14.2219 3.3674 14.9048 4.43892 15.9997 5.07103C17.0945 5.70313 18.364 5.75884 19.4566 5.3323C20.3858 6.37118 21.0747 7.58203 21.4997 8.87652C20.5852 9.60958 19.9997 10.736 19.9997 11.9992C19.9997 13.2632 20.5859 14.3902 21.5013 15.1232C21.29 15.7636 21.0104 16.3922 20.6599 16.9992C20.3094 17.6063 19.9049 18.1627 19.4559 18.6659C18.3634 18.2396 17.0943 18.2955 15.9997 18.9274C14.9057 19.559 14.223 20.6294 14.0453 21.7879C12.7118 22.067 11.3187 22.0758 9.95443 21.7906C9.77748 20.6311 9.09451 19.5595 7.99967 18.9274C6.90484 18.2953 5.63539 18.2396 4.54272 18.6662C3.61357 17.6273 2.92466 16.4164 2.49964 15.1219C3.41412 14.3889 3.99968 13.2624 3.99968 11.9992C3.99968 10.7353 3.41344 9.60827 2.49805 8.87524C2.70933 8.23482 2.98894 7.60629 3.33942 6.99923C3.68991 6.39217 4.09443 5.83576 4.54341 5.33257C5.63593 5.75881 6.90507 5.703 7.99967 5.07103C9.09364 4.43942 9.7764 3.3691 9.95401 2.2106ZM11.9997 14.9992C13.6565 14.9992 14.9997 13.6561 14.9997 11.9992C14.9997 10.3424 13.6565 8.99923 11.9997 8.99923C10.3428 8.99923 8.99967 10.3424 8.99967 11.9992C8.99967 13.6561 10.3428 14.9992 11.9997 14.9992Z',
        'ri-paint-fill' => 'M19.2277 18.7323L20.9955 16.9645L22.7632 18.7323C23.7395 19.7086 23.7395 21.2915 22.7632 22.2678C21.7869 23.2441 20.204 23.2441 19.2277 22.2678C18.2514 21.2915 18.2514 19.7086 19.2277 18.7323ZM8.87861 1.07971L20.1923 12.3934C20.5828 12.7839 20.5828 13.4171 20.1923 13.8076L11.707 22.2929C11.3165 22.6834 10.6833 22.6834 10.2928 22.2929L1.80754 13.8076C1.41702 13.4171 1.41702 12.7839 1.80754 12.3934L9.58572 4.61525L7.4644 2.49393L8.87861 1.07971ZM10.9999 6.02946L3.92886 13.1005H18.071L10.9999 6.02946Z',
        'ri-group-line' => 'M2 22C2 17.5817 5.58172 14 10 14C14.4183 14 18 17.5817 18 22H16C16 18.6863 13.3137 16 10 16C6.68629 16 4 18.6863 4 22H2ZM10 13C6.685 13 4 10.315 4 7C4 3.685 6.685 1 10 1C13.315 1 16 3.685 16 7C16 10.315 13.315 13 10 13ZM10 11C12.21 11 14 9.21 14 7C14 4.79 12.21 3 10 3C7.79 3 6 4.79 6 7C6 9.21 7.79 11 10 11ZM18.2837 14.7028C21.0644 15.9561 23 18.752 23 22H21C21 19.564 19.5483 17.4671 17.4628 16.5271L18.2837 14.7028ZM17.5962 3.41321C19.5944 4.23703 21 6.20361 21 8.5C21 11.3702 18.8042 13.7252 16 13.9776V11.9646C17.6967 11.7222 19 10.264 19 8.5C19 7.11935 18.2016 5.92603 17.041 5.35635L17.5962 3.41321Z',
        'ri-plug-fill' => 'M13 18V20H19V22H13C11.8954 22 11 21.1046 11 20V18H8C5.79086 18 4 16.2091 4 14V10H20V14C20 16.2091 18.2091 18 16 18H13ZM16 6H19C19.5523 6 20 6.44772 20 7V9H4V7C4 6.44772 4.44772 6 5 6H8V2H10V6H14V2H16V6ZM12 14.5C12.5523 14.5 13 14.0523 13 13.5C13 12.9477 12.5523 12.5 12 12.5C11.4477 12.5 11 12.9477 11 13.5C11 14.0523 11.4477 14.5 12 14.5Z',
    ];

    /** Rendered size in px of the SVG icons, same as in SurveySidemenuWidget and the React editor sidebar */
    const SIDEBAR_ICON_DEFAULT_SIZE = 24;
    const SIDEBAR_ICON_SIZES = [
        'ri-settings-3-fill' => 20,
    ];

    /** Initializes the widget */
    public function init(): void
    {
        $this->registerClientScript();
    }

    /** Executes the widget
     * @throws CException
     */
    public function run(): void
    {
        $this->renderActions();
    }

    /**
     * Renders the sidebar menu on the dashboard page
     * @throws CException
     */
    public function renderActions(): void
    {
        $route = App()->urlManager->parseUrl(App()->request);
        $boxes = Box::model()->findAll(['order' => 'position ASC']);
        $boxesData = [];
        foreach ($boxes as $box) {
            $boxData['position'] = $box->position;
            if (!preg_match("/^(http|https)/", $box->url)) {
                $boxData['url'] = App()->createUrl($box->url);
                $boxData['external'] = false;
            } else {
                $boxData['url'] = $box->url;
                $boxData['external'] = true;
            }
            $boxData['title'] = $box->getLocalizedTitle();
            $boxData['ico'] = $box->getIconName();
            $boxData['svgPath'] = self::SIDEBAR_ICON_PATHS[$boxData['ico']] ?? null;
            $boxData['svgSize'] = self::SIDEBAR_ICON_SIZES[$boxData['ico']] ?? self::SIDEBAR_ICON_DEFAULT_SIZE;
            $boxData['description'] = $box->desc;
            $boxData['selected'] = substr($route, 0, strlen($box->url)) === $box->url;

            // default permission if usergroup is not within expected values
            $canSeeBox = false;
            if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                $canSeeBox = true;
            }
            // check the user group
            $userGroup = UserGroup::model()->findByPk($box->usergroup);
            if ($userGroup && $userGroup->hasUser(App()->user->id)) {
                $canSeeBox = true;
            }
            // everyone can see the box
            if ((int)$box->usergroup === -1) {
                $canSeeBox = true;
            }
            // If the user group is not set, or set to -2, only admin can see the box
            if ((int)$box->usergroup === -2) {
                $canSeeBox = false;
                if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                    $canSeeBox = true;
                }
            }
            // If user group is set to -3, nobody can see the box
            if ((int)$box->usergroup === -3) {
                $canSeeBox = false;
            }
            // pass the boxData to the view only if the user has the necessary permissions
            if ($canSeeBox) {
                $boxesData[] = $boxData;
            }
        }
        $this->render('side_bar', [
                'icons' => $boxesData
        ]);
    }


    /** Registers required script files */
    public function registerClientScript(): void
    {
        App()->getClientScript()->registerScriptFile(
            App()->getAssetManager()->publish(dirname(__FILE__, 2) . '/admin/grid/GridActionsWidget/assets/action_dropdown.js'),
            CClientScript::POS_END
        );
    }
}
