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

namespace LimeSurvey\ExtensionInstaller;

/**
 * @since 2018-09-26
 * @author Olle Haerstedt
 */
abstract class VersionFetcher
{
    /**
     * @var string
     */
    protected $source;

    /**
     * @var boolean
     */
    protected $stable;

    /**
     * @var string
     */
    protected $extensionName;

    /**
     * @var string
     */
    protected $extensionType;

    /**
     * Set source to fetch version information. Can be URL to REST API, git repo, etc.
     * @param string $source
     * @return void
     */
    public function setSource(string $source)
    {
        $this->source = $source;
    }

    /**
     * @param boolean $stable
     * @return void
     */
    public function setStable(bool $stable)
    {
        $this->stable = $stable;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setExtensionName(string $name)
    {
        $this->extensionName = $name;
    }

    /**
     * @param string $type
     */
    public function setExtensionType(string $type)
    {
        $this->extensionType = $type;
    }

    /**
     * Get latest version for configured source.
     * @return string Semantic versioning string.
     */
    abstract public function getLatestVersion();

    /**
     * Get latest security version for configured source.
     * @return string Semantic versioning string.
     */
    abstract public function getLatestSecurityVersion();
}
