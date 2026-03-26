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

        $update = (array)$this->getUpdate();

        if ($update && $update['result']) {
            //Default icon class
            $iconClass = "";
            $NotificationText = gT("Update available");

            if ($update[key($update)]->security_update) {
                $NotificationText = gT("Security update available");
            }
            //Append cu-checker class to icon when animate option is true in plugin settings
            if ($this->get('animate_icon', null, null, false)) {
                $iconClass = "cu-checker";
            }

            //Display update notification only for superadmin user
            if (Permission::model()->hasGlobalPermission('superadmin')) {
                $aMenuItemAdminOptions = [
                    'isDivider' => false,
                    'isSmallText' => false,
                    'label' => '<strong class="text-warning">' . $NotificationText . '</strong>',
                    'href' => $this->api->createUrl('admin/update', []),
                    'iconClass' => 'ri-shield-check-fill text-warning ' . $iconClass,
                ];

                $aMenuItems[] = (new \LimeSurvey\Menu\MenuItem($aMenuItemAdminOptions));

                $oNewMenu = new \ComfortUpdateChecker\helpers\CUCMenuClass($aMenuItemAdminOptions);

                //Check if display only for security update is true in plugin settings and display it otherwhise display all
                if ($this->get('only_security_update', null, null, false) && $update[key($update)]->security_update) {
                    $oEvent->append('extraMenus', [$oNewMenu]);
                } elseif (!$this->get('only_security_update', null, null, false)) {
                    $oEvent->append('extraMenus', [$oNewMenu]);
                }
            }
        }
    }

    /**
     * Check if an update is available from the comfort update server.
     * Uses the 'minimum_update_stability' global setting to determine
     * whether to request unstable versions via crosscheck.
     *
     * @return ?stdClass Update info from the server, or null if no update available
     */
    private function getUpdate()
    {
        $updateModel = new UpdateForm();
        // NB: Use getUpdateNotification, since it checks session for datetime to avoid multiple calls.
        $serverAnswer = $updateModel->getUpdateNotification();
        if ($serverAnswer && $serverAnswer->result) {
            $minimumStability = getGlobalSetting('minimum_update_stability') ?: Yii::app()->getConfig('minimum_update_stability');
            $crosscheck = ($minimumStability !== 'stable') ? '1' : '0';
            return $updateModel->getUpdateInfo($crosscheck);
        } else {
            return null;
        }
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
