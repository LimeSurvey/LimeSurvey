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
abstract class ExtensionUpdater
{
    /**
     * Extension model, e.g. Theme or Plugin class.
     * @todo Create interface ExtensionModelInterface that all extension model classes implement
     * @var mixed
     */
    protected $model = null;

    /**
     * If true, fetch stable version info.
     * @var boolean
     */
    protected $useStable = true;

    /**
     * If true, fetch unstable version info.
     * @var boolean
     */
    protected $useUnstable = false;

    /**
     * @param mixed $model Plugin model, theme model, etc, depending on extension type.
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * @return void
     */
    public function setUseUnstable()
    {
        $this->useUnstable = true;
    }

    /**
     * Returns true if $newVersion is strictly higher than currently installed version
     *
     * @param string $newVersion
     * @return bool
     */
    public function versionHigherThan($newVersion)
    {
        return version_compare($newVersion, $this->getCurrentVersion(), '>');
    }

    /**
     * Returns true if $version is stable.
     * The version is stable IF it does not contain
     * alpha, beta or rc suffixes.
     * @param string $version
     * @return boolean
     */
    public function versionIsStable($version)
    {
        if (empty($version)) {
            return false;
        }

        $suffixes = [
            'alpha',
            'beta',
            'rc',
        ];
        $version = strtolower($version);
        foreach ($suffixes as $suffix) {
            if (strpos($version, $suffix) !== false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns true if $versions contain a security version.
     * @param array $versions Each version has keys 'version' and 'isSecurityVersion'.
     * @return boolean
     */
    public function foundSecurityVersion(array $versions)
    {
        foreach ($versions as $version) {
            if ($version['isSecurityVersion']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Implode $versions into string, stripping security version field.
     * @param array $versions Each version has keys 'version' and 'isSecurityVersion'.
     * @return string
     */
    public function implodeVersions(array $versions)
    {
        $versions = array_map(
            function ($version) {
                return $version['version'];
            },
            $versions
        );
        return implode(', ', $versions);
    }

    /**
     * Compose version message to display of $versions.
     * @param array $versions Each version has keys 'version' and 'isSecurityVersion', etc.
     * @return string
     */
    public function getVersionMessage(array $versions)
    {
        $extensionName = $this->getExtensionName();
        $extensionType = $this->getExtensionType();
        if ($this->foundSecurityVersion($versions)) {
            $message = '<b>' . gT('There are security updates available for %s (type: %s).', 'js') . '</b>';
        } else {
            $message = gT('There are updates available for %s (type: %s).', 'js');
        }

        $message = sprintf(
            $message,
            $extensionName,
            $this->convertExtensionType($extensionType)
        );

        $latestVersion = $this->getLatestVersion($versions);

        $message .= ' ' . sprintf(
            gT('The latest available version is %s.', 'js'),
            $latestVersion['version']
        );

        if (!empty($latestVersion['manualUpdateUrl'])) {
            $message .= ' ' . sprintf(
                gT('Please visit %s to download the update.', 'js'),
                '<a href="' . $latestVersion['manualUpdateUrl'] . '">' . $latestVersion['manualUpdateUrl'] . '</a>'
            );
        }

        return '<p>' . $message . '</p>';
    }

    /**
     * @param array $versions
     * @return array|null
     */
    public function getLatestVersion(array $versions)
    {
        if (empty($versions)) {
            return null;
        }

        $highestVersion = $versions[0];
        foreach ($versions as $version) {
            if (version_compare($version['version'], $highestVersion['version'], '>')) {
                $highestVersion = $version;
            }
        }
        return $highestVersion;
    }

    /**
     * Fetch all new available version from each version fetcher.
     * @return array $versions
     * @todo Move to parent class?
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
     * Convert from single char $type to fullword.
     *
     * @param string $type
     * @return string
     */
    public function convertExtensionType($type)
    {
        switch ($type) {
            case 'p':
                return gT('Plugin');
            case 't':
                return gT('Theme');
            case 's':
                return gT('Survey template');
            case 'q':
                return gT('Question template');
            default:
                throw new Exception();
        }
    }

    /**
     * Create an updater object for every extension of corresponding type.
     * @return array [ExtensionUpdater[] $updaters, string[] $errorMessages]
     */
    abstract public static function createUpdaters();

    /**
     * Fetch extension name from extension model.
     * Extension type specific implementation.
     * @return string
     */
    abstract public function getExtensionName();

    /**
     * Fetch extension type from extension model.
     * Extension type specific implementation.
     * @return string
     */
    abstract public function getExtensionType();

    /**
     * Get extension config object for this extension.
     * @return ExtensionConfig
     */
    abstract public function getExtensionConfig();

    /**
     * Returns currently installed version of this extension
     * @return string
     */
    abstract public function getCurrentVersion();
}
