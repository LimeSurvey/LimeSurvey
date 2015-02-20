<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

class SettingGlobal extends LSActiveRecord
{
    /**
     * This caches request for the current request only.
     */
    protected static $requestCache = [];
    
	/**
	 * Returns the setting's table name to be used by the model
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{settings_global}}';
	}

	/**
	 * Returns the primary key of this table
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'stg_name';
	}
	function updateSetting($settingname, $settingvalue)
    {

        $data = array(
            'stg_name' => $settingname,
            'stg_value' => $settingvalue
        );

        $user = Yii::app()->db->createCommand()->from("{{settings_global}}")->where("stg_name = :setting_name")->bindParam(":setting_name", $settingname, PDO::PARAM_STR);
        $query = $user->queryRow('settings_global');
        $user1 = Yii::app()->db->createCommand()->from("{{settings_global}}")->where("stg_name = :setting_name")->bindParam(":setting_name", $settingname, PDO::PARAM_STR);
        if(count($query) == 0)
        {
            return $user1->insert('{{settings_global}}', $data);
        }
        else
        {
            $user2 = Yii::app()->db->createCommand()->from("{{settings_global}}")->where('stg_name = :setting_name')->bindParam(":setting_name", $settingname, PDO::PARAM_STR);
            return $user2->update('{{settings_global}}', array('stg_value' => $settingvalue));
        }

    }
    
    public function getValue() {
        if (substr_compare("__JSON__", $this->stg_value, 0, 8) === 0) {
            return json_decode(substr($this->stg_value, 8), true);
        } else {
            return $this->stg_value;
        }
    }
    
    public function getName() {
        return $this->stg_name;
    }
    public function setName($value) {
        $this->stg_name = $value;
    }
    public function setValue($value) {
        if (is_array($value)) {
            $this->stg_value = "__JSON__" . json_encode($value);
        }
    }
    public static function get($name, $default = null) {
        Yii::trace($name, "SettingGlobal");
        if (!array_key_exists($name, self::$requestCache)) {
            if (null !== $model = self::model()->findByPk($name)) {
                self::$requestCache[$name] = $model->value;
            } else {
                self::$requestCache[$name] = null;
            }
        }
        return isset(self::$requestCache[$name]) ? self::$requestCache[$name] : $default;
    }
    
    public static function set($name, $value, $events = true) {
        if (null === $model = self::model()->findByPk($name)) {
            $model = new SettingGlobal();
            $model->name = $name;
        }
        $model->value = $value;
        if (!$events) {
            $model->detachBehavior('PluginEventBehavior');
        }
        if (false !== $result = $model->save()) {
            self::$requestCache[$name] = $value;
        }
        return $result;
    }
}
?>
