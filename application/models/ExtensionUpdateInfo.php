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

/**
 * Thin wrapper around extension update info.
 * Used by VersionFetcher to push around update info.
 *
 * @since 2018-10-01
 * @author LimeSurvey GmbH
 */
class ExtensionUpdateInfo
{
    /**
     * @param string
     */
    protected $extensionType;

    /**
     * @var array
     */
    protected $versions;

    /**
     * @var boolean
     */
    protected $isSecurityUpdate;

    /**
     * @param string $type
     * @param string $version
     * @param boolean $security
     */
    public function __construct($type, $versions, $security = false)
    {
        $this->extensionType    = $type;
        $this->versions          = $versions;
        $this->isSecurityUpdate = $security;
    }

    /**
     *
     */
    public function versionIsSecurityUpdate()
    {
        return $this->isSecurityUpdate;
    }
}
