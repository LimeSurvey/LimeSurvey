<?php

class SideBarWidget extends CWidget
{
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
            $boxData['title'] = $box->title;
            $boxData['ico'] = $box->getIconName();
            $boxData['description'] = $box->desc;
            $boxData['selected'] = false;

            if (str_contains(App()->request->requestUri, $boxData['url'])) {
                $boxData['selected'] = true;
            }

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
            App()->getConfig("extensionsurl") . 'admin/grid/GridActionsWidget/assets/action_dropdown.js',
            CClientScript::POS_END
        );
        App()->getClientScript()->registerCssFile(
            App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/side_bar.css')
        );
    }
}
