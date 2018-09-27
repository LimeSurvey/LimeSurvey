<?php

/**
 * LimeSurvey
 * Copyright (C) 2007-2018 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Thin wrapper class around extension config.xml file.
 */
class ExtensionConfig
{
    /**
     * @var SimpleXMLElement
     */
    public $xml;

    /**
     * 
     */
    public function __construct(SimpleXMLElement $xml)
    {
        $this->xml = $xml;
    }

    /**
     * Check basic properties of the config.xml.
     * @return boolean
     * @todo Get detailed error message.
     */
    public function validate()
    {
        return isset($this->xml->metadata)
            && isset($this->xml->metadata->name)
            && isset($this->xml->metadata->description)
            && isset($this->xml->metadata->author)
            && isset($this->xml->metadata->license)
            && isset($this->xml->metadata->version)
            && isset($this->xml->compatibility)
            && isset($this->xml->metadata->type);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->xml->metadata->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->xml->metadata->description;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->xml->metadata->author;
    }

    /**
     * @return string
     */
    public function getLicense()
    {
        return $this->xml->metadata->license;
    }

    /**
     * Version is a string, not number, due to semantic versioning.
     * @return string
     */
    public function getVersion()
    {
        return $this->xml->metadata->version;
    }

    /**
     * Returns true if this extension config is compatible with this version of LS.
     * @return boolean
     */
    public function isCompatible()
    {
        if (!isset($this->xml->compatibility)) {
            return false;
        }

        if (!isset($this->xml->compatibility->version)) {
            return false;
        }

        $lsVersion = require \Yii::app()->getBasePath() . '/config/version.php';
        foreach ($this->xml->compatibility->version as $version) {
            if (substr($lsVersion['versionnumber'], 0, 1) != substr($version, 0, 1)) {
                // 2 is not compatible with 3, etc.
                continue;
            } elseif (version_compare($lsVersion['versionnumber'], $version) >= 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $file Full file path.
     * @return ExtensionConfig
     */
    public static function loadConfigFromFile($file)
    {
        if (!file_exists($file)) {
            return null;
        } else {
            libxml_disable_entity_loader(false);
            $xml = simplexml_load_file(realpath($file));
            libxml_disable_entity_loader(true);
            $config = new \ExtensionConfig($xml);
            return $config;
        }
    }

    /**
     * Parse <updater> tag in config.xml and return objects and potential error messages.
     * @return array [VersionFetcher[] $fetchers, string[] $errorMessages]
     */
    public function getVersionFetchers()
    {
        if (empty($this->xml->updaters)) {
            return [[], []];
        }

        $fetchers = [];
        $errors   = [];

        $factory = new LimeSurvey\ExtensionInstaller\VersionFetcherFactory();

        foreach ($this->xml->updaters->updater as $updaterXml) {
            try {
                $fetchers[] = $factory->getVersionFetcher($updaterXml);
            } catch (\Exception $ex) {
                $errors[] = $this->getName() . ': ' . $ex->getMessage();
            }
        }

        return [$fetchers, $errors];
    }
}
