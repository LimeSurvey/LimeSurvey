<?php

/*
   * LimeSurvey
   * Copyright (C) 2013-2026 The LimeSurvey Project Team
   * All rights reserved.
   * License: GNU/GPL License v2 or later, see LICENSE.php
   * LimeSurvey is free software. This version may have been modified pursuant
   * to the GNU General Public License, and as distributed it includes or
   * is derivative of works licensed under the GNU General Public License or
   * other free or open source software licenses.
   * See COPYRIGHT.php for copyright notices and details.
   *
     *  Files Purpose: lots of common functions
*/

/**
 * Class SettingGlobal
 *
 * @property string $stg_name Setting name
 * @property string $stg_value Setting value
 *
 */
class SettingGlobal extends LSActiveRecord
{
    const DBVERSION_NUMBER = 'DBVersion'; //this is the attribute stg_name in table for db version

    /**
     * @var string[] settings that must only come from php files
     */
    private $disableByDb = array(
        'versionnumber', // Come and leave it in version.php
        'dbversionnumber', // Must keep it out of DB
        'updatable', // If admin with ftp access disable updatable : leave it
        'debug', // Currently not accessible, seem better
        'debugsql', // Currently not accessible, seem better
        'forcedsuperadmin', // This is for security
        'defaultfixedtheme', // Because updating can broke instance
        'demoMode', // No demoMode update via model
        'ssl_emergency_override', // security related
        'ssl_disable_alert', // security related
    );

    /**
     * @inheritdoc
     * @return CActiveRecord
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{settings_global}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'stg_name';
    }

    /** @inheritdoc */
    public function rules()
    {
        $disableByDb = $this->disableByDb;
        /* Specific disable settings for demo mode */
        if (Yii::app()->getConfig("demoMode")) {
            $disableByDb = array_merge($disableByDb, array('sitename','defaultlang','defaulthtmleditormode','filterxsshtml'));
        }
        $aRules = array(
            array('stg_name', 'required'),
            array('stg_name', 'unique'),
            array('stg_value', 'default', 'value' => ''),
            array('stg_name', 'in', 'not' => true,'range' => $disableByDb),
        );

        return $aRules;
    }

    /**
     * Update or set a setting in DB and update current app config if no error happen
     * Return self : then other script can use if($oSetting->hasErrors()) { Do action with $oSetting->getErrors; }
     * @param string $settingname
     * @param mixed $settingvalue
     * @return self
     */
    public static function setSetting($settingname, $settingvalue)
    {
        $setting = self::model()->findByPk($settingname);
        if (empty($setting)) {
            $setting = new self();
            $setting->stg_name = $settingname;
        }
        $setting->stg_value = $settingvalue;
        $setting->save();
        return $setting;
    }

    /** @inheritdoc
     * Always update of current application config after sucessfull save
     **/
    protected function afterSave()
    {
        parent::afterSave();
        Yii::app()->setConfig($this->stg_name, $this->stg_value);
    }

    /**
     * Increase the custom asset version number in DB
     * This will force the refresh of the assets folders content
     */
    public static function increaseCustomAssetsversionnumber()
    {
        $iCustomassetversionnumber = getGlobalSetting('customassetversionnumber');
        $iCustomassetversionnumber++;
        self::setSetting('customassetversionnumber', $iCustomassetversionnumber);
        return;
    }


    /**
     * Increase the asset version number in version.php
     * This will force the refresh of the assets folders content
     */
    public static function increaseAssetsversionnumber()
    {
        @ini_set('auto_detect_line_endings', '1');
        $sRootdir      = Yii::app()->getConfig("rootdir");
        $versionlines = file($sRootdir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'version.php');
        $handle       = fopen($sRootdir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'version.php', "w");
        $iAssetNumber = self::generateAssetVersionNumber(Yii::app()->getConfig("assetsversionnumber"));

        foreach ($versionlines as $line) {
            if (strpos($line, 'assetsversionnumber') !== false) {
                $line = '$config[\'assetsversionnumber\'] = \'' . $iAssetNumber . '\';' . "\r\n";
            }
            fwrite($handle, $line);
        }
        fclose($handle);
        Yii::app()->setConfig("assetsversionnumber", $iAssetNumber);
        return;
    }

    /**
     * with comfortUpate, we increase the asset number by one.
     * so to be sure that the asset number from comfortUpdate will be different from the one generated here, we index it by 100000
     *
     * @param int $iAssetNumber the current asset number
     * @return int the new asset number
     */
    public static function generateAssetVersionNumber($iAssetNumber)
    {
        while ($iAssetNumber == Yii::app()->getConfig("assetsversionnumber")) {
            if ($iAssetNumber > 100000) {
                $iAssetNumber++;
            } else {
                $iAssetNumber = Yii::app()->getConfig("assetsversionnumber") + 100000;
            }
        }
        return $iAssetNumber;
    }

    /**
     * Returns db version number from table settings_global or null if dbversion does not exist.
     *
     * @return int | null
     */
    public static function getDBVersionNumber()
    {
        /**@var SettingGlobal $dbVersion */
        $dbVersion = self::model()->findByAttributes(['stg_name' => self::DBVERSION_NUMBER]);

        return ($dbVersion === null) ? null : (int)$dbVersion->stg_value;
    }
}
