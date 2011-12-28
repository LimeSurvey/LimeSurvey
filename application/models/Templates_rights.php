<?php

if (!defined('BASEPATH'))
    die('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * 	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */

class Templates_rights extends CActiveRecord
{
    /**
     * Returns the static object for this model
     *
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
        return '{{templates_rights}}';
    }

    /**
     * Returns this table's primary key
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return 'uid';
    }
    
    /**
	 * Insert records from $data array
	 *
	 * @access public
	 * @param array $data
	 * @return boolean
	 */
	public function insertRecords($data)
    {
        $record = new self;
		foreach ($data as $k => $v)
		{
			$search = array('`', "'");
			$k = str_replace($search, '', $k);
			$v = str_replace($search, '', $v);
			$record->$k = $v;
		}
		return $record->save();
	}
}
