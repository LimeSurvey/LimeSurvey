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

use SimpleXMLElement;
use Exception;

/**
 * Central Yii component to add and retrieve version fetcher strategies.
 *
 * @since 2018-09-26
 * @author LimeSurvey GmbH
 */
class VersionFetcherServiceLocator
{
    /**
     * Array of callables that return a version fetcher.
     * @var array<string, callable>
     */
    protected $versionFetcherCreators;

    /**
     * All Yii components need an init() method.
     * @return void
     */
    public function init()
    {
        // Add RESTVersionFetcher, available by default.
        $this->addVersionFetcherType(
            'rest',
            function (SimpleXMLElement $updaterXml) {
                $vf = new RESTVersionFetcher($updaterXml);
                return $vf;
            }
        );

        // TODO: Not implemented.
        $this->addVersionFetcherType(
            'git',
            function (SimpleXMLElement $updaterXml) {
                return new GitVersionFetcher($updaterXml);
            }
        );
    }

    /**
     * Creates a version fetcher based on xml $updaterXml.
     * @param SimpleXMLElement $updaterXml <updater> tag from config.xml.
     * @return VersionFetcher
     * @throws Exception if version fetcher is not found.
     */
    public function createVersionFetcher(SimpleXMLElement $updaterXml)
    {
        $this->validateXml($updaterXml);

        $type = (string) $updaterXml->type;

        if (isset($this->versionFetcherCreators[$type])) {
            $versionFetcher =  $this->versionFetcherCreators[$type]($updaterXml);
            return $versionFetcher;
        } else {
            throw new Exception('Did not find version fetcher of type ' . json_encode($type));
        }
    }

    /**
     * @param string $name
     * @param callable $vfCreator
     * @return void
     * @throws Exception if version fetcher with name $name already exists.
     */
    public function addVersionFetcherType(string $name, callable $vfCreator)
    {
        if (isset($this->versionFetcherCreators[$name])) {
            // NB: Internal error, don't need to translate.
            throw new Exception("Version fetcher with name $name already exists");
        }

        $this->versionFetcherCreators[$name] = $vfCreator;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return void
     * @throws Exception on invalid xml.
     */
    protected function validateXml(SimpleXMLElement $xml)
    {
        if (empty((string) $xml->type)) {
            throw new Exception(gT('Missing type tag in updater xml'));
        }

        if (empty((string) $xml->source)) {
            throw new Exception(gT('Missing source tag in updater xml'));
        }

        if ((string) $xml->stable === '') {
            throw new Exception(gT('Missing stable tag in updater xml'));
        }
    }
}
