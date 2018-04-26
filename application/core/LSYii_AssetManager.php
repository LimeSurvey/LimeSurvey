<?php
/**
 * LimeSurvey
 * Copyright (C) 2007-2018 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v3 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

class LSYii_AssetManager extends CAssetManager
{
    /* @inheritdoc */
    protected function hash($path)
    {
        $assetsVersionNumber       = Yii::app()->getConfig('assetsversionnumber');
        $versionNumber             = Yii::app()->getConfig('versionnumber');
        $dbVersion                 = Yii::app()->getConfig('dbversionnumber');
        $iCustomassetversionnumber = Yii::app()->getConfig('customassetversionnumber',1);/* This always get by order : from db, from config, from config-default */

        if (empty($assetsVersionNumber)
            || empty($versionNumber)
            || empty($dbVersion)) {
            throw new Exception(
                'Could not create asset manager path hash: One of these configs are empty: assetsversionnumber/versionnumber/dbversionnumber.'
            );
        }
        $lsVersion = $assetsVersionNumber.$versionNumber.$dbVersion.$iCustomassetversionnumber;
        return sprintf('%x',crc32($path.$lsVersion));
    }

    /**
     * @inheritdoc
     * With db asset version used
     */
    protected function generatePath($file,$hashByName=false)
    {
        if (is_file($file)) {
            $pathForHashing=$hashByName ? dirname($file) : dirname($file).filemtime($file).AssetVersion::getAssetVersion($file);
        } else {
            $pathForHashing=$hashByName ? $file : $file.filemtime($file).AssetVersion::getAssetVersion($file);
        }
        tracevar($pathForHashing);
        return $this->hash($pathForHashing);
    }
}
