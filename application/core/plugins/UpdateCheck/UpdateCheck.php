<?php

/**
 * LimeSurvey
 * Copyright (C) 2007-2015 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

use LimeSurvey\Menu\Menu;

/**
 * Plugin to check for extension updates after a super admin logs in.
 * Uses the ExtensionInstaller library.
 *
 * @since 2018-10-04
 * @author LimeSurvey GmbH
 */
class UpdateCheck extends PluginBase
{
    /**
     * Where to save plugin settings etc.
     * @var string
     */
    protected $storage = 'DbStorage';

    /** @inheritdoc this plugin didn't have any public method */
    public $allowedPublicMethods = array('checkAll');

    /**
     * @return void
     */
    public function init()
    {
        $this->subscribe('afterSuccessfulLogin');
        $this->subscribe('beforeControllerAction');
        $this->subscribe('beforePluginManagerMenuRender');
    }


    /**
     * Checks if an extension update check is due after a super admin successfully logs in.
     *
     * This method is triggered after a successful user login. It verifies if the current user
     * has super admin permissions. If so, it retrieves the timestamp of the last extension update
     * check and compares it with the current date and time. If the next scheduled check date has
     * passed or is today, a flag is set in the session to trigger an extension update check.
     *
     * @return void
     */
    public function afterSuccessfulLogin()
    {
        if (Permission::model()->hasGlobalPermission('superadmin')) {
            // NB: $nextCheck will be set to "now" if next_extension_update_check is empty.
            // Hence it needs to be initialised *before* today.
            $value = $this->get('next_extension_update_check');
            $nextCheck = $value ? new DateTime($value) : new DateTime();
            $today = new DateTime("now");
            if ($nextCheck <= $today) {
                // Set flag.
                Yii::app()->session['do_extensions_update_check'] = true;
            }
        }
    }

    /**
     * If we're in an admin controller and the flag is set, render the JavaScript that
     * will Ajax the checkAll() URL and push a notification.
     * @return void
     */
    public function beforeControllerAction()
    {
        $controller = $this->getEvent()->get('controller');
        $doUpdateCheckFlag = Yii::app()->session['do_extensions_update_check'];

        if ($controller == 'admin' && $doUpdateCheckFlag) {
            // Render some JavaScript that will Ajax call update check.
            $this->spitOutUrl();
            $this->registerMyScript();

            // Unset flag.
            Yii::app()->session['do_extensions_update_check'] = false;

            // Set date for next check.
            $today = new DateTime("now");
            $this->set('next_extension_update_check', $today->add(new DateInterval('P1D'))->format('Y-m-d H:i:s'));
        }
    }

    /**
     * @return void
     */
    public function beforePluginManagerMenuRender()
    {
        $notificationUpdateUrl = Notification::getUpdateUrl();
        $event = $this->event;
        $event->append(
            'extraMenus',
            [
                new Menu(
                    [
                        'href'      => $this->getCheckUrl(),
                        'iconClass' => 'ri-refresh-line',
                        'label'     => gT('Find updates'),
                        'tooltip'   => gT('Check all extensions for available updates.'),
                        'onClick'   => <<<JS
$("#ls-loading").show();
$.ajax(
    {
        url: this.href,
        data: {},
        method: "GET",
        success: function() {
            $("#ls-loading").hide();
            LS.updateNotificationWidget("$notificationUpdateUrl");
        },
    }
);
return false;
JS
                    ]
                )
            ]
        );
    }

    /**
     * Used to check for available updates for all plugins.
     * This method should be run at super admin login, max once every day.
     * Run by Ajax to avoid increased page load time.
     * This method can also be run manually for testing.
     * @return void
     */
    public function checkAll()
    {
        $service = \Yii::app()->extensionUpdaterServiceLocator;

        // Get one updater class for each extension.
        list($updaters, $errors) = $service->getAllUpdaters();

        /** @var string[] */
        $messages = [];

        /** @var boolean */
        $foundSecurityVersion = false;

        foreach ($updaters as $updater) {
            try {
                $versions = $updater->fetchVersions();
                if ($updater->foundSecurityVersion($versions)) {
                    $foundSecurityVersion = true;
                }
                if ($versions) {
                    $messages[] = $updater->getVersionMessage($versions);
                }
            } catch (\Throwable $ex) {
                $errors[] = $updater->getExtensionName() . ': ' . $ex->getMessage();
            }
        }

        // Compose notification.
        if ($messages || $errors) {
            $this->composeNotification($messages, $errors, $foundSecurityVersion);
        }
    }

    /**
     * Compose messages and errors into a nice notification message. Extra annoying if
     * $foundSecurityVersion is set to true.
     * @param string[] $messages
     * @param string[] $errors
     * @param bool $foundSecurityVersion
     * @return void
     */
    protected function composeNotification(array $messages, array $errors, bool $foundSecurityVersion)
    {
        $superadmins = User::model()->getSuperAdmins();
        $title        = $foundSecurityVersion ? gT('Security updates available') : gT('Updates available');
        $displayClass = $foundSecurityVersion ? 'danger' : '';
        $importance   = $foundSecurityVersion ? Notification::HIGH_IMPORTANCE : Notification::NORMAL_IMPORTANCE;
        $message = implode($messages);
        if ($errors) {
            $message .= '<hr/><i class="ri-alert-fil"></i>&nbsp;'
                . gT('Errors happened during the update check. Please notify the extension authors for support.')
                . '<ul>'
                . '<li>' . implode('</li><li>', $errors) . '</li>';
        }
        UniqueNotification::broadcast(
            [
                'title'         => $title,
                'display_class' => $displayClass,
                'message'       => $message,
                'importance'    => $importance
            ],
            $superadmins
        );
    }

    /**
     * @return void
     */
    protected function spitOutUrl()
    {
        $url = $this->getCheckUrl();
        $notificationUpdateUrl = Notification::getUpdateUrl();

        $script = <<<JS
// Namespace
var LS = LS || {};
LS.plugin = LS.plugin || {};
LS.plugin.updateCheck = LS.plugin.updateCheck || {};

LS.plugin.updateCheck.url = '$url';
LS.plugin.updateCheck.notificationUpdateUrl = '$notificationUpdateUrl';
JS;

        Yii::app()->clientScript->registerScript(
            'updatecheckurls',
            $script,
            CClientScript::POS_HEAD
        );
    }

    /**
     * @return string
     */
    protected function getCheckUrl()
    {
        return Yii::app()->createUrl(
            'admin/pluginhelper',
            [
                'sa'     => 'ajax',
                'plugin' => 'updateCheck',
                'method' => 'checkAll'
            ]
        );
    }

    /**
     * @return void
     */
    protected function registerMyScript()
    {
        $assetsUrl = Yii::app()->assetManager->publish(dirname(__FILE__) . '/assets/js');
        Yii::app()->clientScript->registerScriptFile($assetsUrl . '/updateCheck.js');
    }
}
