<?php
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
class survey_url_parameters_model extends CI_Model{
    function getParametersForSurvey($iSurveyID)
    {
        $oQuery=$this->db->query("select '' as act, up.*,q.title, sq.title as sqtitle, q.question, sq.question as sqquestion from {$this->db->dbprefix}survey_url_parameters up
                            left join {$this->db->dbprefix}questions q on q.qid=up.targetqid
                            left join {$this->db->dbprefix}questions sq on q.qid=up.targetqid
                            where up.sid={$iSurveyID}");
        ;
        return $oQuery;
    }

    function deleteRecords($aConditions)
    {
        foreach  ($aConditions as $sFieldname=>$sFieldvalue)
        {
           $this->db->where($sFieldname,$sFieldvalue);
        }
        return $this->db->delete('survey_url_parameters');// Deletes from token
    }

    function insertRecord($aData)
    {

            $this->db->insert('survey_url_parameters',$aData);
     }

}

?>
