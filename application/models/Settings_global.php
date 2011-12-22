<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
   * LimeSurvey
   * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
   * All rights reserved.
   * License: GNU/GPL License v2 or later, see LICENSE.php
   * LimeSurvey is free software. This version may have been modified pursuant
   * to the GNU General Public License, and as distributed it includes or
   * is derivative of works licensed under the GNU General Public License or
   * other free or open source software licenses.
   * See COPYRIGHT.php for copyright notices and details.
   *
   *	$Id: common_helper.php 11335 2011-11-08 12:06:48Z c_schmitz $
   *	Files Purpose: lots of common functions
*/

class Settings_global extends CActiveRecord
{
	/**
	 * Returns the static model of Settings table
	 *
	 * @static
	 * @access public
	 * @return CActiveRecord
	 */
	public static function model()
	{
		return parent::model(__CLASS__);
	}

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

        $user = Yii::app()->db->createCommand()->from("{{settings_global}}")->where("stg_name ='" . $settingname . "'");
        $query = $user->queryRow('settings_global');
        $user1 = Yii::app()->db->createCommand()->from("{{settings_global}}")->where("stg_name = '" . $settingname . "'");
        if(count($query) == 0)
        {
            return $user1->insert('{{settings_global}}', $data);
        }
        else
        {
            $user2 = Yii::app()->db->createCommand()->from("{{settings_global}}")->where('stg_name =' . $settingname);
            return $user2->update('{{settings_global}}', array('stg_value' => $settingvalue));
        }

    }
}
?>
