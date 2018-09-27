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

namespace LimeSurvey\ExtensionInstaller;

/**
 * @since 2018-09-26
 * @author Olle Haerstedt
 */
class PluginUpdater extends ExtensionUpdater
{
    /**
     * Create a PluginUpdater for every plugin installed.
     * @return array [ExtensionUpdater[] $updaters, string[] $errorMessages]
     */
    public static function createUpdaters()
    {
        // Get all installed plugins (both active and non-active).
        $plugins = \Plugin::model()->findAll();

        $updaters = [];
        $errors   = [];
        foreach ($plugins as $plugin) {
            try {
                $updater = new PluginUpdater($plugin);
                list($fetchers, $fetcherErrors) = $updater->getVersionFetchers();
                $errors = array_merge($fetcherErrors, $errors);

                if ($fetchers) {
                    $updater->setVersionFetchers($fetchers);
                    $updaters[] = $updater;
                } else {
                    $errors[] = gT('No version fetcher found for plugin ' . $plugin->name);
                }

            } catch (\Exception $ex) {
                $errors[] = $ex->getMessage();
            }
        }

        return [$updaters, $errors];
    }

    /**
     * Read config of this plugin and return version fetcher type.
     * @return array [VersionFetcher[] $fetchers, string[] $errors]
     */
    public function getVersionFetchers()
    {
        if (empty($this->model)) {
            throw new \InvalidArgumentException('No model');
        }

        $config = new \ExtensionConfig($this->model->getConfig());
        return $config->getVersionFetchers();
    }
}
