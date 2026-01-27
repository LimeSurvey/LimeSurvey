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
        /** @var array<string, mixed> */
        $tags = [
            'metadata' => [
                'name',
                'description',
                'author',
                'license',
                'version',
                'type'
            ],
            'compatibility' => [],
        ];
        foreach ($tags as $key => $value) {
            if (!isset($this->xml->$key)) {
                throw new Exception(
                    sprintf(
                        gT('Missing tag %s in extension config.xml'),
                        $key
                    )
                );
            }
            if (is_array($value)) {
                foreach ($value as $tag) {
                    if (!isset($this->xml->$key->$tag)) {
                        throw new Exception(
                            sprintf(
                                gT('Missing tag %s in %s in extension config.xml'),
                                $tag,
                                $key
                            )
                        );
                    }
                }
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return (string) $this->xml->metadata->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return (string) $this->xml->metadata->description;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return (string) $this->xml->metadata->author;
    }

    /**
     * @return string
     */
    public function getLicense()
    {
        return (string) $this->xml->metadata->license;
    }

    /**
     * Version is a string, not number, due to semantic versioning.
     *
     * @return string
     */
    public function getVersion()
    {
        return (string) $this->xml->metadata->version;
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
            if (substr((string) $lsVersion['versionnumber'], 0, 1) != substr($version, 0, 1)) {
                // 2 is not compatible with 3, etc.
                continue;
            } elseif (version_compare($lsVersion['versionnumber'], $version) >= 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reads xml from file and creates an instance of ExtensionConfig
     * @param string $file Full file path.
     * @return ExtensionConfig
     */
    public static function loadFromFile($file)
    {
        if (!file_exists($file)) {
            return null;
        } else {
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader(false);
            }
            $xml = simplexml_load_file(realpath($file));
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader(true);
            }
            $config = new self($xml);
            return $config;
        }
    }

    /**
     * Create an ExtensionConfig from config.xml inside zip $filePath
     * config.xml can be in a subfolder.
     *
     * @param string $filePath Full file path.
     * @return ExtensionConfig
     * @throws Exception at error
     */
    public static function loadFromZip($filePath)
    {
        $zip = new ZipArchive();
        $err = $zip->open($filePath);
        if ($err !== true) {
            throw new Exception('Could not open zip file');
        }
        $configFilename = self::findConfigXml($zip);
        $configString = $zip->getFromName($configFilename);
        $zip->close();
        if ($configString === null) {
            throw new Exception('Config file is empty');
        }
        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader(false);
        }
        $xml = simplexml_load_string($configString);
        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader(true);
        }
        return new self($xml);
    }

    /**
     * @param ZipArchive $zip
     * @return string|null
     */
    private static function findConfigXml(ZipArchive $zip)
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (strpos($filename, 'config.xml') !== false) {
                return $filename;
            }
        }
        return null;
    }

    /**
     * Create a version fetcher for every <updater> tag in config.xml.
     * @return array VersionFetcher[]
     */
    public function createVersionFetchers()
    {
        if (empty($this->xml->updaters)) {
            throw new \Exception(
                sprintf(
                    gT('Extension %s has no updater defined in config.xml'),
                    $this->getName()
                )
            );
        }

        // Don't create any fetchers if updaters are disabled.
        if ((string) $this->xml->updaters['disabled'] === 'disabled') {
            return [];
        }

        $fetchers = [];

        $service = \Yii::app()->versionFetcherServiceLocator;

        foreach ($this->xml->updaters->updater as $updaterXml) {
            try {
                $fetchers[] = $service->createVersionFetcher($updaterXml);
            } catch (\Exception $ex) {
                // Include extension name in error message.
                throw new \Exception($this->getName() . ': ' . $ex->getMessage(), 0, $ex);
            }
        }

        return $fetchers;
    }

    /**
     * Returns the $nodeName XML node as an array
     *
     * @param string $nodeName the name of the node to retrieve
     * @return array<mixed> the node contents as an array
     */
    public function getNodeAsArray($nodeName)
    {
        if (empty($this->xml)) {
            throw new Exception(gT("No XML config loaded"));
        }
        $node = json_decode(json_encode((array)$this->xml->$nodeName), true);
        return $node;
    }
}
