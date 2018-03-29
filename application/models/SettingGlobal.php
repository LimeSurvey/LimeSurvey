<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
   * LimeSurvey
   * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
   * All rights reserved.
   * License: GNU/GPL License v2 or later, see LICENSE.php
   * LimeSurvey is free software. This version may have been modified pursuant
   * to the GNU General Public License, and as distributed it includes or
   * is derivative of works licensed under the GNU General Public License or
   * other free or open source software licenses.
   * See COPYRIGHT.php for copyright notices and details.
   *
     *	Files Purpose: lots of common functions
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
    /**
     * @inheritdoc
     * @return CActiveRecord
     */
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
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


    /**
     * @param string $settingname
     * @param string $settingvalue
     * @return int
     */
    public function updateSetting($settingname, $settingvalue)
    {

        $data = array(
            'stg_name' => $settingname,
            'stg_value' => $settingvalue
        );

        $user = Yii::app()->db->createCommand()->from("{{settings_global}}")->where("stg_name = :setting_name")->bindParam(":setting_name", $settingname, PDO::PARAM_STR);
        $query = $user->queryRow('settings_global');
        $user1 = Yii::app()->db->createCommand()->from("{{settings_global}}")->where("stg_name = :setting_name")->bindParam(":setting_name", $settingname, PDO::PARAM_STR);
        if (count($query) == 0) {
            return $user1->insert('{{settings_global}}', $data);
        } else {
            $user2 = Yii::app()->db->createCommand()->from("{{settings_global}}")->where('stg_name = :setting_name')->bindParam(":setting_name", $settingname, PDO::PARAM_STR);
            return $user2->update('{{settings_global}}', array('stg_value' => $settingvalue));
        }

    }

    /**
     * Increase the custom asset version number in DB
     * This will force the refresh of the assets folders content
     */
    static public function increaseCustomAssetsversionnumber()
    {
        $iCustomassetversionnumber = getGlobalSetting('customassetversionnumber');
        $iCustomassetversionnumber++;
        setGlobalSetting('customassetversionnumber', $iCustomassetversionnumber);
        return;
    }


    /**
     * Increase the asset version number in version.php
     * This will force the refresh of the assets folders content
     */
    static public function increaseAssetsversionnumber()
    {
        @ini_set('auto_detect_line_endings', true);
        $sRootdir      = Yii::app()->getConfig("rootdir");
        $versionlines = file($sRootdir.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'version.php');
        $handle       = fopen($sRootdir.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'version.php', "w");
        $iAssetNumber = self::generateAssetVersionNumber(Yii::app()->getConfig("assetsversionnumber"));

        foreach ($versionlines as $line) {
            if (strpos($line, 'assetsversionnumber') !== false) {
                $line = '$config[\'assetsversionnumber\'] = \''.$iAssetNumber.'\';'."\r\n";
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
    static public function generateAssetVersionNumber($iAssetNumber)
    {
        while ( $iAssetNumber == Yii::app()->getConfig("assetsversionnumber")) {
            if ($iAssetNumber > 100000){
                $iAssetNumber++;
            }else{
                $iAssetNumber = Yii::app()->getConfig("assetsversionnumber") + 100000;
            }
        }
        return $iAssetNumber;
    }
}
