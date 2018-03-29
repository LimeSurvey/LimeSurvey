<?php

/**
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

class LSYii_AssetManager extends CAssetManager
{
    /**
     * Generates path segments relative to basePath.
     *
     * This method is used instead of the original, so the hash is taken
     * from LS version number instead of folder/file last modified time.
     * Using file/folder causes a lot of problems due to FTP and other file
     * transfers not updating the time stamp, forcing LS to use touch()
     * in a lot of places instead. touch() can now be removed - the assets
     * will be updated every time a version number is changed.
     *
     * @param string $file for which public path will be created.
     * @param bool $hashByName whether the published directory should be named as the hashed basename.
     * @return string path segments without basePath.
     * @since 1.1.13
     */
    protected function generatePath($file, $hashByName = false)
    {
        $assetsVersionNumber       = Yii::app()->getConfig('assetsversionnumber');
        $versionNumber             = Yii::app()->getConfig('versionnumber');
        $dbVersion                 = Yii::app()->getConfig('dbversionnumber');
        $iCustomassetversionnumber = (function_exists('getGlobalSetting') ) ? getGlobalSetting('customassetversionnumber'):1; // When called from installer, function getGlobalSetting() is not available

        if (empty($assetsVersionNumber)
            || empty($versionNumber)
            || empty($dbVersion)) {
            throw new Exception(
                'Could not create asset manager path hash: One of these configs are empty: assetsversionnumber/versionnumber/dbversionnumber.'
            );
        }

        $lsVersion = $assetsVersionNumber.$versionNumber.$dbVersion.$iCustomassetversionnumber;

        if (is_file($file)) {
            $pathForHashing = $hashByName ? dirname($file) : dirname($file).$lsVersion;
        } else {
            $pathForHashing = $hashByName ? $file : $file.$lsVersion;
        }

        return $this->hash($pathForHashing);
    }
}
