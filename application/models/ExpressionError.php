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
*/

/**
 * Class ExpressionError
 *
 * @property integer $id
 * @property string $errortime
 * @property integer $sid
 * @property integer $gid
 * @property integer $qid
 * @property integer $gseq
 * @property integer $qseq
 * @property string $type
 * @property string $eqn
 * @property string $prettyprint
 *
 */
class ExpressionError extends LSActiveRecord
{
	/**
	 * @static
	 * @access public
     * @param string $class
	 * @return ExpressionError
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
		return '{{expression_errors}}';
	}

	/**
	 * Returns the primary key of this table
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'scid';
	}

	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);
		}

		$data = $this->db->get('expression_errors');

		return $data;
	}

    function insertRecords($data)
    {

        return $this->db->insert('expression_errors',$data);
    }

}
