<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * This is the model class for table "{{plugins}}".
 */
class Plugin extends CActiveRecord {

    /**
     * @param type $className
     * @return Plugin
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
    public function tableName() {
        return '{{plugins}}';
    }

    public function getConfig() {
        $file = Yii::app()->basePath
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'plugins'
            . DIRECTORY_SEPARATOR . $this->name
            . DIRECTORY_SEPARATOR . 'config.php';

        // No config file, just return empty array
        if (!file_exists($file)) {
            return array();
        }

        $config = include($file);

        if (!isset($config['version'])) {
            throw new Exception("Config file need a version number");
        }

        // Compare with 0.0.1-dev, which is the lowest possible version.
        $isPhpVersion = version_compare($config['version'], '0.0.1-dev');

        if ($isPhpVersion === -1) {
            throw new Exception("Version in config is not a PHP version: " . $config['version']);
        }

        $status = $this->getStatus($config['version']);

        return $config;
    }

    /**
     * Return version status, e.g. "alpha" if
     * version is "1.2.3-alpha"
     *
     * @param string $version
     * @return string
     */
    protected function getStatus($version) {
        $versionAndStatus = explode('-', $version);
        var_dump($versionAndStatus);

        if (count($versionAndStatus) === 1) {
            return "";
        }
        elseif (count($versionAndStatus) === 2) {
            return $versionAndStatus[1];
        }
        else {
            throw new Exception("Invalid version: more than one slash ('-'): " . $version);
        }
    }
}
