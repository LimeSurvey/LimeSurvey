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
     * The version fetchers gets remote update information.
     * The type of version fetcher is configured in config.xml.
     * @var VersionFetcher[]
     */
    protected $versionFetchers = [];

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
     * Uses the version fetcher to get info about available updates for
     * this extension.
     * @return ?
     */
    abstract public function getAvailableUpdates();

    /**
     * @return void
     */
    public function setUseUnstable()
    {
        $this->useUnstable = true;
    }

    /**
     * Parse config.xml and instantiate all version fetchers.
     * @return void
     */
    public function setupVersionFetchers()
    {
        if (empty($this->model)) {
            throw new \InvalidArgumentException('No model');
        }

        $config = new \ExtensionConfig($this->model->getConfig());
        $this->versionFetchers = $config->getVersionFetchers();
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
     * The version is stable IF it does not contain
     * alpha, beta or rc suffixes.
     * @return boolean
     */
    public function versionIsStable($version)
    {
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
}
