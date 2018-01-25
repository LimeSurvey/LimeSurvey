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
     * @param $key
     * @return static
     */
    public static function findByKey($key){
        /** @var static $model */
        $model = self::model()->find('stg_name = :setting_name',[":setting_name"=>$key]);
        return $model;
    }


    /**
     * @param string $settingname
     * @param string $settingvalue
     * @return int
     */
    public static  function updateSetting($settingname, $settingvalue)
    {
        $model = self::findByKey($settingname);
        if (empty($model)) {
            $model = new SettingGlobal();
            $model->stg_name = $settingname;
        }
        $model->stg_value = $settingvalue;
        return $model->save();
    }
}
