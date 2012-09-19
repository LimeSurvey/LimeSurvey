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
   *	$Id$
   *	Files Purpose: lots of common functions
*/

class Quota extends CActiveRecord
{
	/**
	 * Returns the static model of Settings table
	 *
	 * @static
	 * @access public
     * @param string $class
	 * @return CActiveRecord
	 */
	public static function model($class = __CLASS__)
	{
		return parent::model($class);
	}

	/**
	 * Returns the setting's table name to be used by the model
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{quota}}';
	}

	/**
	 * Returns the primary key of this table
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'id';
	}

	/**
	 * Returns the relations
	 *
	 * @access public
	 * @return array
	 */
	public function relations()
	{
		return array(
			'languagesettings' => array(self::HAS_MANY, 'Quota_languagesettings', '',
				'on' => 't.id = languagesettings.quotals_quota_id'),
		);
	}

	function insertRecords($data)
    {
        $quota = new self;
		foreach ($data as $k => $v)
			$quota->$k = $v;
		return $quota->save();
    }

    function deleteQuota($condition = false, $recursive = true)
    {
        if ($recursive == true)
        {
            $oResult = Quota::model()->findAllByAttributes($condition);
            foreach ($oResult as $aRow)
            {
                Quota_languagesettings::model()->deleteAllByAttributes(array('quotals_quota_id' => $aRow['id']));
                Quota_members::model()->deleteAllByAttributes(array('quota_id' => $aRow['id']));
            }
        }

        Quota::model()->deleteAllByAttributes($condition);
    }
}
?>
