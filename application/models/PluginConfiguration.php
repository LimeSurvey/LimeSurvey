<?php

/*
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
 * Thin wrapper class around the plugin config.xml file.
 */
class PluginConfiguration
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
            && isset($this->xml->metadata->type)
            && (string) $this->xml->metadata->type === 'plugin';
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
     * Returns true if this plugin config is compatible with this version of LS.
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
        foreach ($this->xml->compatibility->version as $pluginVersion) {
            if (substr($lsVersion['versionnumber'], 0, 1) != substr($pluginVersion, 0, 1)) {
                // 2 is not compatible with 3, etc.
                continue;
            } elseif (version_compare($lsVersion['versionnumber'], $pluginVersion) >= 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $file Full file path.
     * @return PluginConfiguration
     */
    public static function loadConfigFromFile($file)
    {
        if (!file_exists($file)) {
            return null;
        } else {
            libxml_disable_entity_loader(false);
            $xml = simplexml_load_file(realpath($file));
            libxml_disable_entity_loader(true);
            $pluginConfig = new \PluginConfiguration($xml);
            return $pluginConfig;
        }
    }
}
