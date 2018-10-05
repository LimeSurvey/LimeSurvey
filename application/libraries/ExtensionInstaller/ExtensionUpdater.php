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
abstract class ExtensionUpdater
{
    /**
     * Extension model, e.g. Theme or Plugin class.
     * @todo Create super class ExtensionModel that all extension model classes inherit from.
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
     * @param mixed $model
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
     * Returns true if this extension update version is higher than $currentVersion.
     * @param string $currentVersion
     * @return int
     */
    public function versionHigherThan($currentVersion)
    {
        return version_compare($this->version, $currentVersion, '>');
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
            $message = gT('There are <b>security updates</b> available for %s <b>%s</b>.', 'js');
        } else {
            $message = gT('There are updates available for %s <b>%s</b>.', 'js');
        }

        $message = sprintf(
            $message,
            $extensionType,
            $extensionName
        );

        $latestVersion = $this->getLatestVersion($versions);

        $message .= ' ' . sprintf(
            gT('The latest available version is <i>%s</i>.', 'js'),
            $latestVersion['version']
        );

        if (!empty($latestVersion['manualUpdateUrl'])) {
            $message .= ' ' . sprintf(
                gT('Please visit <a href="%s">%s</a> to download the update.', 'js'),
                $latestVersion['manualUpdateUrl'],
                $latestVersion['manualUpdateUrl']
            );
        }

        return '<p>' . $message . '</p>';
    }

    /**
     * Get description of how to update to latest version, based on available
     * information in <updater> XML.
     * @return string
     */
    public function getUpdateMethodsDescription(array $versions)
    {
    }

    /**
     * @return array
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
     * Uses the version fetchers to fetch info about available updates for this extension.
     * @return array
     */
    abstract public function fetchVersions();

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
}
