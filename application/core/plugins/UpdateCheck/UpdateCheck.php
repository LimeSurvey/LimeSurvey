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

/**
 * Plugin to check for extension updates after a super admin logs in.
 * Uses the ExtensionInstaller library.
 *
 * @since 2018-10-04
 * @author Olle Haerstedt
 */
class UpdateCheck extends PluginBase
{
    /**
     * @return void
     */
    public function init()
    {
        $this->subscribe('afterSuccessfulLogin');
        $this->subscribe('beforeControllerAction');
    }

    /**
     * @return void
     */
    public function afterSuccessfulLogin()
    {
        if (Permission::model()->hasGlobalPermission('superadmin')) {
            // Set flag.
            Yii::app()->session['do_update_check'] = true;
        }
    }

    /**
     * @return void
     */
    public function beforeControllerAction()
    {
        $controller = $this->getEvent()->get('controller');
        $doUpdateCheck = Yii::app()->session['do_update_check'];

        if ($controller == 'admin' && $doUpdateCheck) {
            $this->spitOutUrl();
            $this->registerScript();
            // Unset flag.
            Yii::app()->session['do_update_check'] = false;
        }
    }

    /**
     * Used to check for available updates for all plugins.
     * This method should be run at super admin login, max once every day.
     * Run by Ajax to avoid increased page load time.
     * @return void
     */
    public function checkAll()
    {
        $service = \Yii::app()->extensionUpdaterServiceLocator;

        // Get one updater class for each extension type (PluginUpdater, ThemeUpdater, etc).
        // Only static methods will be used for this updaters.
        list($updaters, $errors) = $service->getAllUpdaters();

        /** @var string[] */
        $messages = [];

        foreach ($updaters as $updater) {
            try {
                list($extensionName, $extensionType, $availableVersions) = $updater->getAvailableUpdates();
                if ($availableVersions) {
                    $messages[] = sprintf(
                        gT('There are updates available for %s %s, new version number(s): %s.'),
                        $extensionType,
                        $extensionName,
                        implode(', ', $availableVersions)
                    );
                }
            } catch (\Throwable $ex) {
                $errors[] = $ex->getMessage();
            }
        }

        if ($messages) {
            $superadmins = User::model()->getSuperAdmins();
            UniqueNotification::broadcast(
                [
                    'title' => gT('Updates available'),
                    'message' => implode('<br/>', $messages) . '<br/>' . implode('<br/>', $errors)
                ],
                $superadmins
            );
        }
    }

    /**
     * @return void
     */
    protected function spitOutUrl()
    {
        $data = [
            'url' => Yii::app()->createUrl(
                'admin/pluginhelper',
                array(
                    'sa'     => 'ajax',
                    'plugin' => 'updateCheck',
                    'method' => 'checkAll'
                )
            ),
            'notificationUpdateUrl' => Notification::getUpdateUrl()
        ];
        echo $this->api->renderTwig(__DIR__ . '/views/index.twig', $data);
    }

    /**
     * @return void
     */
    protected function registerScript()
    {
        $assetsUrl = Yii::app()->assetManager->publish(dirname(__FILE__) . '/assets/js');
        Yii::app()->clientScript->registerScriptFile($assetsUrl . '/updateCheck.js');
    }
}
