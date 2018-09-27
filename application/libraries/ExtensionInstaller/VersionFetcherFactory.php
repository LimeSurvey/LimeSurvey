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
class VersionFetcherFactory
{
    /**
     * @param SimpleXMLElement $updaterXml <updater> tag from config.xml.
     * @return VersionFetcher
     */
    public function getVersionFetcher(\SimpleXMLElement $updaterXml)
    {
        $this->validateXml($updaterXml);

        $type = (string) $updaterXml->type;

        switch ($type) {
            case 'rest':
                $fetcher = new RESTVersionFetcher();
                break;
            case 'git':
                $fetcher = new GitVersionFetcher();
                break;
            default:
                throw new \InvalidArgumentException('Did not find version fetcher of type ' . json_encode($type));
        }

        $source = (string) $updaterXml->source;
        $stable = (string) $updaterXml->stable;

        $fetcher->setSource($source);
        $fetcher->setStable($stable === "1");

        return $fetcher;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return void
     * @throws Exception on invalid xml.
     */
    protected function validateXml(\SimpleXMLElement $xml)
    {
        if (empty((string) $xml->type)) {
            throw new \Exception('Missing type tag in updater xml');
        }

        if (empty((string) $xml->source)) {
            throw new \Exception('Missing source tag in updater xml');
        }

        if (empty((string) $xml->stable)) {
            throw new \Exception('Missing stable tag in updater xml');
        }
    }
}
