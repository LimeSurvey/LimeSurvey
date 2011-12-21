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
class Questions_model extends CI_Model {

    function getAllRecords($condition=FALSE)
    {
        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }

        $data = $this->db->get('questions');

        return $data;
    }

    function getSomeRecords($fields,$condition=FALSE,$order=FALSE)
    {
        if(is_array($fields))
        {
            foreach ($fields as $field)
            {
                $this->db->select($field);
            }
        }
        else
        {
            $this->db->select($fields);
        }
        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }
        if ($order != FALSE)
        {
            $this->db->order_by($order);
        }

        $data = $this->db->get('questions');

        return $data;
    }

    function getQuestions($sid,$gid,$language)
    {

        $this->db->where('sid',$sid);
        $this->db->where('gid',$gid);
        $this->db->where('language',$language);
        $this->db->where('parent_qid',0);
        $this->db->order_by("question_order","asc");
        $data = $this->db->get('questions');

        return $data;
    }

    function getQuestionsWithSubQuestions($iSurveyID, $sLanguage, $sCondition=FALSE)
    {

        $this->db->select('questions.*, q.qid as sqid, q.title as sqtitle,  q.question as sqquestion, groups.*',FALSE);
        $this->db->join('questions q', "q.parent_qid = questions.qid AND q.language = {$this->db->dbprefix}questions.language",'LEFT');
        $this->db->join('groups', "groups.gid = questions.gid  AND {$this->db->dbprefix}questions.language = {$this->db->dbprefix}groups.language");
        $this->db->where('questions.sid',$iSurveyID);
        $this->db->where('questions.language',$sLanguage);
        $this->db->where('questions.parent_qid',0);
        if ($sCondition != FALSE)
        {
            $this->db->where($sCondition);
        }
        $this->db->order_by("groups.group_order","asc");
        $this->db->order_by("questions.question_order","asc");
        $data = $this->db->get('questions');

        return $data;
    }

    function getQuestionID($sid,$gid,$language)
    {
        $this->db->select('qid');
        $this->db->where('sid',$sid);
        $this->db->where('gid',$gid);
        $this->db->where('language',$language);
        $this->db->where('parent_qid',0);
        $this->db->order_by("question_order","asc");
        $data = $this->db->get('questions');

        return $data;
    }

    function getMaximumQuestionOrder($gid,$language)
    {
        $this->db->select_max('question_order','max');
        $this->db->where('gid',$gid);
        $this->db->where('language',$language);
        $data = $this->db->get('questions');

        return $data;
    }

    function updateQuestionOrder($gid,$lang,$position=0)
    {
        $this->db->select('qid');
        $this->db->where('gid',$gid);
        $this->db->where('language',$lang);
        $this->db->order_by('question_order, title ASC');
        $data = $this->db->get('questions');

        foreach($data->result_array() as $row)
        {
            $datatoupdate = array('question_order' => $position);
            $this->db->where('qid',$row['qid']);
            $this->db->update('questions',$datatoupdate);
            $position++;
        }
    }

    function getQuestionType($qid)
    {
        return $this->db->query('SELECT type FROM '.$this->db->dbprefix('questions').' WHERE qid='.((int)$qid).' and parent_qid=0 group by type');
    }

    function getSubQuestions($sid,$sLanguage)
    {
        //Used by getSubQuestions helper
        $query = "SELECT sq.*, q.other FROM ".$this->db->dbprefix('questions')." as sq, ".$this->db->dbprefix('questions')." as q"
        ." WHERE sq.parent_qid=q.qid AND q.sid=".$sid
        ." AND sq.language='".$sLanguage. "' "
        ." AND q.language='".$sLanguage. "' "
        ." ORDER BY sq.parent_qid, q.question_order,sq.scale_id , sq.question_order";
        return $this->db->query($query);
    }

    function update($data, $condition=FALSE)
    {

        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }

        return $this->db->update('questions', $data);

    }

    function valueLabel($field,$language)
    {
        if (isset($field['scale_id']))
        {
            $query = "SELECT answers.code, answers.answer, questions.type FROM answers, questions WHERE";
            $query .= " answers.scale_id = ? AND";
            $query .= " answers.qid = ? AND questions.language = ? AND  answers.language = ? AND questions.qid = ? ORDER BY sortorder ASC";
            return $this->db->query($query, array((int) $field['scale_id'], $field["qid"], $language, $language, $field['qid']));
        } else
        {
            $query = "SELECT answers.code, answers.answer, questions.type FROM answers, questions WHERE";

            $query .= " answers.qid = ? AND questions.language = ? AND  answers.language = ? AND questions.qid = ? ORDER BY sortorder ASC";
            return $this->db->query($query, array($field["qid"], $language, $language, $field['qid']));
        }


    }

    function insertRecords($data)
    {
        return $this->db->insert('questions',$data);
    }

    /**
    * This function returns an array of the advanced attributes for the particular question including their values set in the database
    *
    * @param mixed $iQuestionID  The question ID - if 0 then all settings will use the default value
    * @param mixed $sQuestionType  The question type
    * @param mixed $sLanguage  If you give a language then only the attributes for that language are returned
    */
    function getAdvancedSettingsWithValues($iQuestionID, $sQuestionType , $iSurveyID, $sLanguage=null)
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

}
