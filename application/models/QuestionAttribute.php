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

class QuestionAttribute extends LSActiveRecord
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

    /**
    * Defines the relations for this model
    *
    * @access public
    * @return array
    */
    public function relations()
    {
		$alias = $this->getTableAlias();
        return array(
        'qid' => array(self::HAS_ONE, 'Questions', '',
            'on' => "$alias.qid = questions.qid",
            ),
        );
    }

    /**
    * Returns this model's validation rules
    *
    */
    public function rules()
    {
        return array(
            array('qid,attribute','required'),
            array('value','LSYii_Validators'),
        );
    }
    
    public function setQuestionAttribute($iQuestionID,$sAttributeName, $sValue)
    {
        $oModel = new self;
        $oModel->updateAll(array('value'=>$sValue),'attribute=:attributeName and qid=:questionID',array(':attributeName'=>$sAttributeName,':questionID'=>$iQuestionID));
        return Yii::app()->db->createCommand()
            ->select()
            ->from($this->tableName())
            ->where(array('and', 'qid=:qid'))->bindParam(":qid", $qid)
            ->order('qaid asc')
            ->query();
    }    

    /**
    * Returns Question attribute array name=>value
    *
    * @access public
    * @param int $iQuestionID
    * @return array
    */
    public function getQuestionAttributes($iQuestionID)
    {
        $iQuestionID=(int)$iQuestionID;
        static $aQuestionAttributesStatic=array();// TODO : replace by Yii::app()->cache
        // Limit the size of the attribute cache due to memory usage
        $aQuestionAttributesStatic=array_splice($aQuestionAttributesStatic,-1000,null,true);
        if(isset($aQuestionAttributesStatic[$iQuestionID]))
        {
            return $aQuestionAttributesStatic[$iQuestionID];
        }
        $aQuestionAttributes=array();
        $oQuestion = Question::model()->find("qid=:qid",array('qid'=>$iQuestionID)); // Maybe take parent_qid attribute before this qid attribute
        if ($oQuestion)
        {
            $aLanguages = array_merge(array(Survey::model()->findByPk($oQuestion->sid)->language), Survey::model()->findByPk($oQuestion->sid)->additionalLanguages);

            // Get all atribute set for this question
            $sType=$oQuestion->type;
            $aAttributeNames = questionAttributes();
            $aAttributeNames = $aAttributeNames[$sType];
            $oAttributeValues = QuestionAttribute::model()->findAll("qid=:qid",array('qid'=>$iQuestionID));
            $aAttributeValues=array();
            foreach($oAttributeValues as $oAttributeValue)
            {
                if($oAttributeValue->language){
                    $aAttributeValues[$oAttributeValue->attribute][$oAttributeValue->language]=$oAttributeValue->value;
                }else{
                    $aAttributeValues[$oAttributeValue->attribute]=$oAttributeValue->value;
                }
            }

            // Fill with aQuestionAttributes with default attribute or with aAttributeValues
            // Can not use array_replace due to i18n
            foreach($aAttributeNames as $aAttribute)
            {
                if ($aAttribute['i18n'] == false)
                {
                    if(isset($aAttributeValues[$aAttribute['name']]))
                    {
                        $aQuestionAttributes[$aAttribute['name']]=$aAttributeValues[$aAttribute['name']];
                    }
                    else
                    {
                        $aQuestionAttributes[$aAttribute['name']]=$aAttribute['default'];
                    }
                }
                else
                {
                    foreach ($aLanguages as $sLanguage)
                    {
                        if (isset($aAttributeValues[$aAttribute['name']][$sLanguage]))
                        {
                            $aQuestionAttributes[$aAttribute['name']][$sLanguage] = $aAttributeValues[$aAttribute['name']][$sLanguage];
                        }
                        else
                        {
                            $aQuestionAttributes[$aAttribute['name']][$sLanguage] = $aAttribute['default'];
                        }
                    }
                }
            }
        }
        else
        {
            return false; // return false but don't set $aQuestionAttributesStatic[$iQuestionID]
        }
        $aQuestionAttributesStatic[$iQuestionID]=$aQuestionAttributes;
        return $aQuestionAttributes;
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
