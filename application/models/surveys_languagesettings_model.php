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
class Surveys_languagesettings_model extends CI_Model {

	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);
		}

		$data = $this->db->get('surveys_languagesettings');

		return $data;
	}

	function getSomeRecords($fields,$condition=FALSE)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}
		if ($condition != FALSE)
		{
			$this->db->where($condition);
		}

		$data = $this->db->get('surveys_languagesettings');

		return $data;
	}

    function getDateFormat($surveyid,$languagecode)
    {
        $this->db->select('surveyls_dateformat');
        $this->db->from('surveys_languagesettings');
        $this->db->join('surveys','surveys.sid = surveys_languagesettings.surveyls_survey_id AND surveyls_survey_id = '.$surveyid);
        $this->db->where('surveyls_language = \''.$languagecode.'\'');
        return $this->db->get();
    }

    function getAllSurveys($hasPermission = FALSE)
    {
        $this->db->select('a.*, surveyls_title, surveyls_description, surveyls_welcometext, surveyls_url');
        $this->db->from('surveys AS a');
        $this->db->join('surveys_languagesettings','surveyls_survey_id=a.sid AND surveyls_language=a.language');

        if ($hasPermission)
        {
            $this->db->where('a.sid IN (SELECT sid FROM '.$this->db->dbprefix("survey_permissions").' WHERE uid='.$this->session->userdata("loginID").' AND permission=\'survey\' and read_p=1) ');
        }
        $this->db->order_by('active DESC, surveyls_title');
        return $this->db->get();
    }

	function update($data, $condition=FALSE)
	{

		if ($condition != FALSE)
		{
			$this->db->where($condition);
		}

		$this->db->update('surveys_languagesettings', $data);

	}

    function getAllData($sid,$lcode)
    {
    	$query = 'SELECT * FROM '. $this->db->dbprefix('surveys') .', '. $this->db->dbprefix('surveys_languagesettings') .' WHERE sid=? AND surveyls_survey_id=? AND surveyls_language=?';
        return $this->db->query($query, array($sid, $sid, $lcode));
    }

    function insertNewSurvey($data)
    {
        if (isset($data['surveyls_url']) && $data['surveyls_url']== 'http://') {$data['surveyls_url']="";}
        return $this->db->insert('surveys_languagesettings', $data);
    }
    function getSurveyNames($surveyid)
    {
        $this->db->select('surveyls_title')->from('surveys_languagesettings')->where('surveyls_language',$this->session->userdata('adminlang'))->where('surveyls_survey_id',$surveyid);
        $query = $this->db->get();
        return $query;
    }

}
