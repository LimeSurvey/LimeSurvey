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

/**
 * Class QuestionAttribute
 * @property string $language
 * @property string $attribute
 * @property string $value
 */
class QuestionAttribute extends ActiveRecord
{
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
    * Defines the relations for this model
    *
    * @access public
    * @return array
    */
    public function relations()
    {
		return [
            'qid' => [self::BELONGS_TO, Question::class, 'qid']
        ];
    }

    /**
    * Returns this model's validation rules
    *
    */
    public function rules()
    {
        return [
            [['qid', 'attribute'], 'required'],
            ['value', 'LSYii_Validators'],
        ];
    }

    /**
     * @param $iQuestionID
     * @param $sAttributeName
     * @param $sValue
     * @return mixed
     * @deprecated
     */
    public function setQuestionAttribute($iQuestionID,$sAttributeName, $sValue)
    {
        $oModel = new self;
        $aResult=$oModel->findAll('attribute=:attributeName and qid=:questionID',array(':attributeName'=>$sAttributeName,':questionID'=>$iQuestionID));
        if (!empty($aResult))
        {
            $oModel->updateAll(array('value'=>$sValue),'attribute=:attributeName and qid=:questionID',array(':attributeName'=>$sAttributeName,':questionID'=>$iQuestionID));
        }
        else
        {
            $oModel = new self;
            $oModel->attribute=$sAttributeName;
            $oModel->value=$sValue;
            $oModel->qid=$iQuestionID;
            $oModel->save();
        }
        return Yii::app()->db->createCommand()
            ->select()
            ->from($this->tableName())
            ->where(array('and', 'qid=:qid'))->bindParam(":qid", $qid)
            ->order('qaid asc')
            ->query();
    }

    /**
     * @param $data
     * @return bool
     * @deprecated
     */

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

