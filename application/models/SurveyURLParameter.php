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
*/

/**
 * Class SurveyURLParameter
 *
 * @property integer $id
 * @property integer $sid Survey ID
 * @property string $parameter
 * @property integer $targetqid
 * @property integer $targetsqid
 */
class SurveyURLParameter extends LSActiveRecord
{
    /**
     * @inheritdoc
     * @return SurveyURLParameter
     */
    public static function model($class = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($class);
        return $model;
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'id';
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{survey_url_parameters}}';
    }
    /** @inheritdoc */
    public function relations()
    {
        return array(
            'survey' => array(self::BELONGS_TO, 'Survey', 'sid', 'together' => true),
            'question' => array(self::BELONGS_TO, 'Question', array('targetqid' => 'qid')),
            'subquestion' => array(self::BELONGS_TO, 'Question', array('targetsqid' => 'qid'))
        );
    }
    /**
     * @param integer $iSurveyID
     * @return mixed
     */
    public function getParametersForSurvey($iSurveyID)
    {
        return Yii::app()->db->createCommand("select '' as act, up.*,q.title, sq.title as sqtitle, q.question, sq.question as sqquestion from {{survey_url_parameters}} up
            left join {{questions}} q on q.qid=up.targetqid
            left join {{questions}} sq on q.qid=up.targetsqid
            where up.sid=:surveyid")
            ->bindParam(":surveyid", $iSurveyID, PDO::PARAM_INT)
            ->query();
    }

    /**
     * @param array $aConditions
     * @return mixed
     */
    public function deleteRecords($aConditions)
    {
        foreach ($aConditions as $sFieldname=>$sFieldvalue) {
            Yii::app()->db->createCommand()->where($sFieldname, $sFieldvalue);
        }
        return Yii::app()->db->delete('survey_url_parameters'); // Deletes from token
    }

    /**
     * @param array $aData
     * @return mixed
     */
    public function insertRecord($aData)
    {
        return Yii::app()->db->createCommand()->insert('{{survey_url_parameters}}', $aData);
    }

}
