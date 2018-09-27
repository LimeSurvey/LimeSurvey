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
    protected $versionFetchers;

    /**
     * Extension model, e.g. Theme or Plugin class.
     * @todo Create super class ExtensionModel that all extension model classes inherit from.
     * @var mixed
     */
    protected $model;

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
     * Use the version fetcher to get info about available updates for 
     * this extension.
     * @return ExtensionUpdate[]
     */
    public function getAvailableUpdates()
    {
        if (empty($this->versionFetchers)) {
            // No fetchers, can't fetch remote version.
            return [];
        }

        foreach ($this->versionFetcher as $fetcher) {
            die('here');
        }
    }

    /**
     * @return void
     */
    public function setUseUnstable()
    {
        $this->useUnstable = true;
    }

    /**
     * @param VersionFetcher $vf
     * @return void
     */
    public function addVersionFetcher(VersionFetcher $vf)
    {
        $this->versionFetchers[] = $vf;
    }

    /**
     * @return array [ExtensionUpdater[] $updaters, string[] $errorMessages]
     */
    abstract public static function createUpdaters();
}
