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

class Questions extends CActiveRecord
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
		return '{{questions}}';
	}

	/**
	 * Returns the primary key of this table
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'qid';
	}

	/**
	 * This function returns an array of the advanced attributes for the particular question including their values set in the database
	 *
	 * @access public
	 * @param mixed $iQuestionID  The question ID - if 0 then all settings will use the default value
	 * @param mixed $sQuestionType  The question type
	 * @param mixed $sLanguage  If you give a language then only the attributes for that language are returned
	 * @return array
	 */
	public function getAdvancedSettingsWithValues($iQuestionID, $sQuestionType , $iSurveyID, $sLanguage=null)
	{
		if (is_null($sLanguage))
		{
			$aLanguages=array_merge(array(GetBaseLanguageFromSurveyID($iSurveyID)),GetAdditionalLanguagesFromSurveyID($iSurveyID));
		}
		else
		{
			$aLanguages=array($sLanguage);
		}
		if ($iQuestionID!=0) {
			$aAttributeValues=getQuestionAttributeValues($iQuestionID, $sQuestionType);
		}
		$aAttributeNames=questionAttributes();
		$aAttributeNames=$aAttributeNames[$sQuestionType];
		uasort($aAttributeNames,'CategorySort');
		foreach  ($aAttributeNames as $iKey=>$aAttribute)
		{
			if ($aAttribute['i18n']==false)
			{
				if (isset($aAttributeValues[$aAttribute['name']]))
				{
					$aAttributeNames[$iKey]['value']=$aAttributeValues[$aAttribute['name']];
				}
				else
				{
					$aAttributeNames[$iKey]['value']=$aAttribute['default'];
				}
			}
			else
			{
				foreach ($aLanguages as $sLanguage)
				{
					if (isset($aAttributeValues[$aAttribute['name']][$sLanguage]))
					{
						$aAttributeNames[$iKey][$sLanguage]['value']=$aAttributeValues[$aAttribute['name']][$sLanguage];
					}
					else
					{
						$aAttributeNames[$iKey][$sLanguage]['value']=$aAttribute['default'];
					}
				}
			}
		}
		return $aAttributeNames;
	}

    function getQuestions($sid,$gid,$language)
    {
		return Yii::app()->db->createCommand()
			->select()
			->from(self::tableName())
			->where(array('and', 'sid='.$sid, 'gid='.$gid, 'language=:language', 'parent_qid=0'))
			->order('question_order asc')
			->bindParam(":language", $language, PDO::PARAM_STR)
			->query();
    }

    function getSubQuestions($parent_qid)
    {
		return Yii::app()->db->createCommand()
			->select()
			->from(self::tableName())
			->where(array('and', 'parent_qid='.$parent_qid))
			->order('question_order asc')
			->query();
    }

	function insertRecords($data)
    {
        $questions = new self;
		foreach ($data as $k => $v)
			$questions->$k = $v;
		return $questions->save();
}
}
?>
