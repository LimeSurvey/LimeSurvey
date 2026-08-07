<?php

/**
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

namespace LimeSurvey\ExtensionInstaller;

use Exception;
use ExtensionConfig;

/**
 * @since 2018-09-26
 * @author LimeSurvey GmbH
 */
class PluginUpdater extends ExtensionUpdater
{
    /**
     * Create a PluginUpdater for every plugin installed.
     * @return array [ExtensionUpdater[] $updaters, string[] $errorMessages]
     */
    public static function createUpdaters(): array
    {
        // Get all installed plugins (both active and non-active).
        $plugins = \Plugin::model()->findAll();

        $updaters = [];
        $errors   = [];
        foreach ($plugins as $plugin) {
            try {
                $updaters[] = new PluginUpdater($plugin);
            } catch (Exception $ex) {
                $errors[] = $ex->getMessage();
            }
        }

        return [$updaters, $errors];
    }

    /**
     * @return string
     */
    public function getExtensionName()
    {
        return $this->model->name;
    }

    /**
     * @return string
     */
    public function getExtensionType()
    {
        return 'p';
    }

    /**
     * @return ExtensionConfig
     */
    public function getExtensionConfig()
    {
        return $this->model->extensionConfig;
    }

    /**
     * Get this extension's current version.
     * @return string
     */
    public function getCurrentVersion()
    {
        return $this->model->version;
    }
}
