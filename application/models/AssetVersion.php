<?php

/*
   * LimeSurvey
   * Copyright (C) 2018-2026 The LimeSurvey Project Team
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
 * Class AssetVersion
 *
 * @property integer $id pk
 * @property string $path
 * @property integer $version number
 */
class AssetVersion extends LSActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
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
    public function rules()
    {
        return array(
            array('path', 'required'),
            array('path', 'unique'),
            array('version', 'required'),
            array('version', 'numerical', 'integerOnly' => true),
        );
    }

    /**
     * get current assetVersion
     * @param string $path
     * @return integer
     */
    public static function getAssetVersion($path)
    {
        if (Yii::app()->getConfig('DBVersion') < 400) {
            return 0;
        }
        $oAssetVersion = self::model()->find('path = :path', array(":path" => $path));
        if (!$oAssetVersion) {
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
        if (Yii::app()->getConfig('DBVersion') < 400) {
            return 0;
        }
        /* This increment case insensitivity , (extend_vanilla at same time than Extend_Vanilla) no real issue (update 2 assets in one) , but â€¦ */
        $oAssetVersion = self::model()->find('path = :path', array(":path" => $path));
        if (!$oAssetVersion) {
            $oAssetVersion = new self();
            $oAssetVersion->path = $path;
            $oAssetVersion->version = 0;
        }
        $oAssetVersion->version++;
        $oAssetVersion->save(); // Not need to test : can not break rules. DB error can happen ?
        return $oAssetVersion->version;
    }

    /**
     * delete assets version related to path
     * @param string $path
     * @return integer (0|1)
     */
    public static function deleteAssetVersion($path)
    {
        if (Yii::app()->getConfig('DBVersion') < 400) {
            return 0;
        }
        return self::model()->deleteAll('path = :path', array(":path" => $path));
    }
}
