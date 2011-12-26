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
class Survey_url_parameters extends CActiveRecord{
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
		return '{{survey_url_parameters}}';
	}
    
    function getParametersForSurvey($iSurveyID)
    {
        return Yii::app()->db->createCommand("select '' as act, up.*,q.title, sq.title as sqtitle, q.question, sq.question as sqquestion from {{survey_url_parameters}} up
                            left join {{questions}} q on q.qid=up.targetqid
                            left join {{questions}} sq on q.qid=up.targetqid
                            where up.sid={$iSurveyID}")->query();
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
