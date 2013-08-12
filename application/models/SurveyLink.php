<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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
  * 	Files Purpose: lots of common functions
 */

class SurveyLink extends LSActiveRecord
{

	/**
	 * Returns the static model of Settings table
	 *
	 * @static
	 * @access public
     * @param string $class
	 * @return SurveyLink
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
        return '{{survey_links}}';
    }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return array('participant_id', 'token_id', 'survey_id');
    }

    function getLinkInfo($participantid)
    {
        return self::model()->findAllByAttributes(array('participant_id' => $participantid));
    }

    /*
     *
     *
     *
     *
     * */
    function rebuildLinksFromTokenTable($iSurveyId) {
        $this->deleteLinksBySurvey($iSurveyId);
        $tableName="{{tokens_".$iSurveyId."}}";
        $dateCreated=date('Y-m-d H:i:s', time());
        $query = "INSERT INTO ".SurveyLink::tableName()." (participant_id, token_id, survey_id, date_created) SELECT participant_id, tid, '".$iSurveyId."', '".$dateCreated."' FROM ".$tableName." WHERE participant_id IS NOT NULL";
        return Yii::app()->db->createCommand($query)
                             ->query();
    }
    /*
     * Delete a single survey_link based on a token table entry(by token_id and survey_id)
     *
     * An entry in the survey_links table must be unique by the combination of Token_ID
     * (which is unique within a tokens table) and survey_id (which limits to one single
     * token table).
     *
     * @param int $participant_id The UUID of the participant whose link is being deleted
     * @param int $token_id the unique id of the entry in the token table being deleted
     * @param int $survey_id the id of the survey for the link being deleted
     *
     * @return true|false
     * */
    function deleteTokenLink($iTokenIds, $surveyId) {
        $query = "DELETE FROM ".SurveyLink::tableName()." WHERE token_id IN (".implode(", ", $iTokenIds).") AND survey_id=:survey_id";
        return Yii::app()->db->createCommand($query)
                             ->bindParam(":survey_id", $surveyId)
                             ->query();
    }

    /*
     * Delete all entries in the survey_link table that link to a particular survey_id
     * This function is used when a tokens_table is being dropped, and therefore all
     * links must be removed
     *
     * @param int $survey_id the SID of the survey whose tokens table is being dropped
     *
     * @return true|false
     * */
    function deleteLinksBySurvey($surveyId) {
        $query = "DELETE FROM ".SurveyLink::tableName(). " WHERE survey_id = :survey_id";
        return Yii::app()->db->createCommand($query)
                             ->bindParam(":survey_id", $surveyId)
                             ->query();
    }

}

?>
