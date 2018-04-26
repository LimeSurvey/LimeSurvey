<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/*
   * LimeSurvey
   * Copyright (C) 2018 The LimeSurvey Project Team / Carsten Schmitz
   * All rights reserved.
   * License: GNU/GPL License v3 or later, see LICENSE.php
   * LimeSurvey is free software. This version may have been modified pursuant
   * to the GNU General Public License, and as distributed it includes or
   * is derivative of works licensed under the GNU General Public License or
   * other free or open source software licenses.
   * See COPYRIGHT.php for copyright notices and details.
   *
*/

/**
 * Class DefaultValue
 *
 * @property string $hash identifier of path
 * @property string $path for reminder
 * @property integer $version number
 */
class AssetVersion extends LSActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->path = "";
        $this->version = 0;
    }
    /** @inheritdoc */
    public function tableName()
    {
        return '{{asset_version}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return array('path');
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('hash', 'required'),
            array('path', 'required'),
            array('version', 'required'),
            array('version', 'numerical', 'integerOnly'=>true),
        );
    }

    /**
     * get current assetVersion
     * @param string $path
     * @return integer
     */
    public static function getAssetVersion($path)
    {
        $hash = hash('sha256', $path);
        $oAssetVersion = self::model()->findByPk($hash);
        if(!$oAssetVersion) {
            return 0;
        }
        return $oAssetVersion->version;
    }

    /**
     * increment (and create if needed) asset version number
     * @param string $path
     * @return integer (current version)
     */
    public static function incrementAssetVersion($path)
    {
        $hash = hash('sha256', $path);
        $oAssetVersion = self::model()->findByPk($hash);
        if(!$oAssetVersion) {
            $oAssetVersion = new self;
            $oAssetVersion->hash = $hash;
            $oAssetVersion->path = $path;
            $oAssetVersion->version = 0;
        }
        $oAssetVersion->version++;
        $oAssetVersion->save(); // Not need to test : can break rules. DB error can happen ?
        return $oAssetVersion->version;
    }
}
