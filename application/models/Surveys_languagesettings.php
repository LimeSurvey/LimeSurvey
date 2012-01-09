<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');
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
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */
class Surveys_languagesettings extends CActiveRecord
{
	/**
	 * Returns the table's name
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{surveys_languagesettings}}';
	}

	/**
	 * Returns the table's primary key
	 *
	 * @access public
	 * @return array
	 */
	public function primaryKey()
	{
		return array('surveyls_survey_id', 'surveyls_language');
	}

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
	 * Returns the relations of this model
	 *
	 * @access public
	 * @return array
	 */
	public function relations()
	{
		return array(
			'survey' => array(self::BELONGS_TO, 'Survey', '',
				'on' => 't.surveyls_survey_id = survey.sid',
			),
		);
	}

	function getAllRecords($condition=FALSE, $return_query = TRUE)
	{
		$query = Yii::app()->db->createCommand()->select('*')->from('{{surveys_languagesettings}}');
		if ($condition != FALSE)
		{
			$query->where($condition);
		}
        return ( $return_query ) ? $query->queryAll() : $query;
	}

    function getDateFormat($surveyid,$languagecode)
    {
		$query=Yii::app()->db->createCommand();
        $query->select('surveyls_dateformat');
        $query->from('{{surveys_languagesettings}}');
		$query->join('surveys','surveys.sid = surveys_languagesettings.surveyls_survey_id AND surveyls_survey_id = '.$surveyid);
        $query->where('surveyls_language = \''.$languagecode.'\'');
        return $query->query();
    }

    function getAllSurveys($hasPermission = FALSE)
    {
        $this->db->select('a.*, surveyls_title, surveyls_description, surveyls_welcometext, surveyls_url');
        $this->db->from('surveys AS a');
        $this->db->join('surveys_languagesettings','surveyls_survey_id=a.sid AND surveyls_language=a.language');

        if ($hasPermission)
        {
            $this->db->where('a.sid IN (SELECT sid FROM {{survey_permissions}} WHERE uid='.$this->session->userdata("loginID").' AND permission=\'survey\' and read_p=1) ');
        }
        $this->db->order_by('active DESC, surveyls_title');
        return $this->db->get();
    }

    function getAllData($sid,$lcode)
    {
    	$query = 'SELECT * FROM {{surveys}}, {{surveys_languagesettings}} WHERE sid=? AND surveyls_survey_id=? AND surveyls_language=?';
        return $this->db->query($query, array($sid, $sid, $lcode));
    }

    function insertNewSurvey($data, $xssfiltering = false)
    {
        if (isset($data['surveyls_url']) && $data['surveyls_url']== 'http://') {$data['surveyls_url']="";}

		if($xssfiltering)
		{
			$filter = new CHtmlPurifier();
			$filter->options = array('URI.AllowedSchemes'=>array(
  				'http' => true,
  				'https' => true,
			));
			$data["description"] = $filter->purify($data["description"]);
			$data["title"] = $filter->purify($data["title"]);
			$data["welcome"] = $filter->purify($data["welcome"]);
			$data["endtext"] = $filter->purify($data["endtext"]);
		}

		return $this->insertSomeRecords($data);
    }
    function getSurveyNames($surveyid)
    {
        return Yii::app()->db->createCommand()->select('surveyls_title')->from('{{surveys_languagesettings}}')->where('surveyls_language = "'.Yii::app()->session['adminlang'].'" AND surveyls_survey_id = '.$surveyid)->queryAll();
    }

    function updateRecords($data,$condition=FALSE, $xssfiltering = false)
    {
        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }

		if($xssfiltering)
		{
			$filter = new CHtmlPurifier();
			$filter->options = array('URI.AllowedSchemes'=>array(
  				'http' => true,
  				'https' => true,
			));
			if (isset($data["description"]))
				$data["description"] = $filter->purify($data["description"]);
			if (isset($data["title"]))
				$data["title"] = $filter->purify($data["title"]);
			if (isset($data["welcome"]))
				$data["welcome"] = $filter->purify($data["welcome"]);
			if (isset($data["endtext"]))
				$data["endtext"] = $filter->purify($data["endtext"]);
		}

        $this->db->update('surveys_languagesettings',$data);

        if ($this->db->affected_rows() <= 0)
        {
            return false;
        }

        return true;
    }

	function insertSomeRecords($data)
    {
        $lang = new self;
		foreach ($data as $k => $v)
			$lang->$k = $v;
		return $lang->save();
    }
}
