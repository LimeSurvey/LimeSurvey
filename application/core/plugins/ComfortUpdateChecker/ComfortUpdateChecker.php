<?php

/*
 * Update Checker for Comfort Update users
 * Copyright (C) LimeSurvey GmbH
 * License: GNU/GPL License v2 http://www.gnu.org/licenses/gpl-2.0.html
 * A plugin of LimeSurvey, a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

class ComfortUpdateChecker extends PluginBase
{
    protected $storage = 'DbStorage';

    protected static $description = 'Update Checker for Comfort Update users';

    protected static $name = 'ComfortUpdateChecker';

    /** @inheritdoc this plugin didn't have any public method */
    public $allowedPublicMethods = array();

    protected $settings = [
        'only_security_update' => array(
            'type' => 'checkbox',
            'label' => 'Notification only for security updates',
            'default' => false,
        ),

        'animate_icon' => array(
            'type' => 'checkbox',
            'label' => 'Animate update icon',
            'default' => false,
        ),

    ];


    public function init()
    {
        Yii::setPathOfAlias(get_class($this), dirname(__FILE__));
        $this->subscribe('beforeAdminMenuRender');
    }

    /**
     * Append new menu item to the admin topbar
     *
     * @return void
     */
    public function beforeAdminMenuRender()
    {
        $oEvent = $this->getEvent();

        //Register css and js script
        $this->registerAssets();

        //Append cu-checker class to icon when animate option is true in plugin settings
        $iconClass = $this->get('animate_icon', null, null, false) ? "cu-checker" : "";

        // Always warn that LimeSurvey 6 is (or is about to be) out of support.
        $oEvent->append('extraMenus', [$this->getOutOfSupportMenu($iconClass)]);

        $updateNotification = $this->getUpdate();

        if ($updateNotification && $updateNotification->result) {
            $NotificationText = gT("Update available");

            if ($updateNotification->security_update) {
                $NotificationText = gT("Security update available");
            }

            // Permission check is already handled in getUpdateNotification() so we can display directly
            $aMenuItemAdminOptions = [
                'isDivider' => false,
                'isSmallText' => false,
                'label' => '<strong class="text-warning">' . $NotificationText . '</strong>',
                'href' => $this->api->createUrl('admin/update', []),
                'iconClass' => 'ri-shield-check-fill text-warning ' . $iconClass,
            ];

            $oNewMenu = new \ComfortUpdateChecker\helpers\CUCMenuClass($aMenuItemAdminOptions);

            //Check if display only for security update is true in plugin settings and display it otherwhise display all
            if ($this->get('only_security_update', null, null, false) && $updateNotification->security_update) {
                $oEvent->append('extraMenus', [$oNewMenu]);
            } elseif (!$this->get('only_security_update', null, null, false)) {
                $oEvent->append('extraMenus', [$oNewMenu]);
            }
        }
    }

    /**
     * Build the permanent "out of support" topbar notice.
     * Before 1 September 2026 it warns support is ending; on and after that date it warns support has ended.
     *
     * @param string $iconClass Extra icon class (e.g. animation class)
     * @return \ComfortUpdateChecker\helpers\CUCMenuClass
     */
    private function getOutOfSupportMenu($iconClass)
    {
        if (time() >= strtotime('2026-09-01 00:00:00')) {
            $NotificationText = gT("LimeSurvey 6 is out of support.") . " - " . gT("Upgrade now!");
        } else {
            $NotificationText = gT("LimeSurvey 6 will be out of support on 1 September 2026.") . " - " . gT("Upgrade now!");
        }

        $aMenuItemAdminOptions = [
            'isDivider' => false,
            'isSmallText' => false,
            'label' => '<strong class="text-warning">' . $NotificationText . '</strong>',
            'href' => $this->api->createUrl('admin/update', []),
            'iconClass' => 'ri-shield-check-fill text-warning ' . $iconClass,
        ];

        return new \ComfortUpdateChecker\helpers\CUCMenuClass($aMenuItemAdminOptions);
    }

    /**
     * Check if an update is available from the comfort update server.
     * Reuses UpdateForm::getUpdateNotification() to preserve permission gate,
     * session caching, and stability filtering (meetsMinimumStability).
     *
     * @return ?stdClass Update notification with aggregated flags (result, security_update, unstable_update), or null
     */
    private function getUpdate()
    {
        $updateModel = new UpdateForm();
        // Reuse the shared notification logic: permission check, once-per-day cache, stability filtering
        $updateNotification = $updateModel->getUpdateNotification();
        return ($updateNotification && $updateNotification->result) ? $updateNotification : null;
    }

    /**
     * Register css and js file
     * @return void
     */
    protected function registerAssets()
    {
        $assetsUrl = Yii::app()->assetManager->publish(dirname(__FILE__) . '/assets');
        Yii::app()->clientScript->registerScriptFile($assetsUrl . '/script.js');
        Yii::app()->clientScript->registerCssFile($assetsUrl . '/style.css');
    }
}
