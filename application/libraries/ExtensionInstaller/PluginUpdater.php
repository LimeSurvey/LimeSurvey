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
    public static function createUpdaters() : array
    {
        // Get all installed plugins (both active and non-active).
        $plugins = \Plugin::model()->findAll();

        $updaters = [];
        $errors   = [];
        foreach ($plugins as $plugin) {
            try {
                $updaters[] = new PluginUpdater($plugin);
            } catch (\Exception $ex) {
                $errors[] = $ex->getMessage();
            }
        }

        return [$updaters, $errors];
    }

    /**
     * Fetch all new available version from each version fetcher.
     * @return array $versions
     */
    public function fetchVersions()
    {
        $config = $this->getExtensionConfig();
        $versionFetchers = $config->createVersionFetchers();

        if (empty($versionFetchers)) {
            // No fetchers, can't fetch remote version.
            return [];
        }

        $allowUnstable = getGlobalSetting('allow_unstable_extension_update');

        $versions = [];
        foreach ($versionFetchers as $fetcher) {

            // Setup fetcher.
            $fetcher->setExtensionName($this->getExtensionName());
            $fetcher->setExtensionType($this->getExtensionType());

            // Fetch versions.
            $newVersion          = $fetcher->getLatestVersion();
            $lastSecurityVersion = $fetcher->getLatestSecurityVersion();

            if (version_compare($lastSecurityVersion, $this->getCurrentVersion(), '>')) {
                $versions[] = [
                    'isSecurityVersion' => true,
                    'version'           => $lastSecurityVersion,
                    'manualUpdateUrl'   => $fetcher->getManualUpdateUrl()
                ];
            }

            // If this version is unstable and we're not allowed to use it, continue.
            if (!$allowUnstable && !$this->versionIsStable($newVersion)) {
                continue;
            }

            if (version_compare($newVersion, $this->getCurrentVersion(), '>')) {
                $versions[] = [
                    'isSecurityVersion' => false,
                    'version'           => $newVersion,
                    'manualUpdateUrl'   => $fetcher->getManualUpdateUrl()
                ];
            } else {
                // Ignore.
            }
        }

        return $versions;
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
        return 'plugin';
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
