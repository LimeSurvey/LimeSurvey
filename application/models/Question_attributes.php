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

class Question_attributes extends CActiveRecord
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
		return '{{question_attributes}}';
	}

	/**
	 * Returns the primary key of this table
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'qaid';
	}

    public function getQuestionAttributes($qid)
    {
		return Yii::app()->db->createCommand()
			->select()
			->from($this->tableName())
			->where(array('and', 'qid=:qid'))->bindParam(":qid", $qid, PDO::PARAM_STR)
			->order('qaid asc')
			->query();
    }

	public static function insertRecords($data)
    {
        $attrib = new self;
		foreach ($data as $k => $v)
			$attrib->$k = $v;
		return $attrib->save();
    }

    public function getQuestionsForStatistics($fields, $condition, $orderby=FALSE)
    {
        $command = Yii::app()->db->createCommand()
        ->select($fields)
        ->from($this->tableName())
        ->where($condition);
        if ($orderby != FALSE)
        {
            $command->order($orderby);
        }
        return $command->queryAll();
    }
}
?>
