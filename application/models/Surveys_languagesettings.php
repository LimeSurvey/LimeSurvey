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
 *	$Id$
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
			'survey' => array(self::BELONGS_TO, 'Survey', '', 'on' => 't.surveyls_survey_id = survey.sid'),
            'owner' => array(self::BELONGS_TO, 'User', '', 'on' => 'survey.owner_id = owner.uid'),
		);
	}

    /**
     * Returns the token's captions
     *
     * @access public
     * @return array
     */
    public function getAttributeCaptions()
    {
        $captions = @unserialize($this->surveyls_attributecaptions);
        return $captions !== false ? $captions : array();
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
		return Yii::app()->db->createCommand()->select('surveyls_dateformat')
            ->from('{{surveys_languagesettings}}')
            ->join('{{surveys}}','{{surveys}}.sid = {{surveys_languagesettings}}.surveyls_survey_id AND surveyls_survey_id = :surveyid')
            ->where('surveyls_language = :langcode')
            ->bindParam(":langcode", $languagecode, PDO::PARAM_STR)
			->bindParam(":surveyid", $surveyid, PDO::PARAM_INT)
            ->queryScalar();
    }

    function getAllSurveys($hasPermission = FALSE)
    {
        $this->db->select('a.*, surveyls_title, surveyls_description, surveyls_welcometext, surveyls_url');
        $this->db->from('surveys AS a');
        $this->db->join('surveys_languagesettings','surveyls_survey_id=a.sid AND surveyls_language=a.language');

        if ($hasPermission)
        {
            $this->db->where('a.sid IN (SELECT sid FROM {{survey_permissions}} WHERE uid=:uid AND permission=\'survey\' and read_p=1) ')->bindParam(":uid", $this->session->userdata("loginID"), PDO::PARAM_INT);
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
        $lang = Yii::app()->session['adminlang'];
        return Yii::app()->db->createCommand()->select('surveyls_title')->from('{{surveys_languagesettings}}')->where('surveyls_language = :adminlang AND surveyls_survey_id = :surveyid')->bindParam(":adminlang", $lang, PDO::PARAM_STR)->bindParam(":surveyid", $surveyid, PDO::PARAM_INT)->queryAll();
    }

    function updateRecords($data,$condition=FALSE, $xssfiltering = false)
    {
        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }
        if (isset($data['surveyls_url']) && $data['surveyls_url']== 'http://') {$data['surveyls_url']="";}
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
