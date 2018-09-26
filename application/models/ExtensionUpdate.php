<?php

/*
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
 * Thin wrapper around extension update info.
 * Used by VersionFetcher.
 */
class ExtensionUpdate
{
    /**
     * @param string
     */
    protected $extensionType;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var boolean
     */
    protected $isSecurityUpdate;

    /**
     * @param string $type
     * @param string $version
     * @param boolean $security
     */
    public function __construct($type, $version, $security = false)
    {
        $this->extensionType    = $type;
        $this->version          = $version;
        $this->isSecurityUpdate = $security;
    }

    /**
     * The version is stable IF it does not contain
     * alpha, beta or rc suffixes.
     * @return boolean
     */
    public function versionIsStable()
    {
        $suffixes = [
            'alpha',
            'beta',
            'rc',
        ];
        $version = strtolower($this->version);
        foreach ($suffixes as $suffix) {
            if (strpos($version, $suffix) !== false) {
                return false;
            }
        }
        return true;
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
     * 
     */
    public function versionIsSecurityUpdate()
    {
        return $this->isSecurityUpdate;
    }
}
