<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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
 */

/**
 * Class SurveyLink
 *
 * @property integer $participant_id
 * @property integer $token_id
 * @property integer $survey_id
 * @property string $date_created
 * @property string $date_invited
 * @property string $date_completed
 *
 * @property Participant $participant
 * @property Survey $survey
 * @property string $dateFormat
 * @property string|boolean $isSubmitted
 * @property string $checkbox
 * @property array $columns
 * @property string $surveyIdLink
 * @property string $lastInvited
 * @property string $lastReminded
 */
class SurveyLink extends LSActiveRecord
{

    /**
     * @inheritdoc
     * @return SurveyLink
     */
    public static function model($class = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($class);
        return $model;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{survey_links}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return array('participant_id', 'token_id', 'survey_id');
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'participant' => array(self::HAS_ONE, 'Particiant', 'participant_id'),
            'survey' => array(self::HAS_ONE, 'Survey', array('sid'=>'survey_id'))
        );
    }

    /**
     * @param integer $participantId
     * @return CActiveRecord[]
     */
    public function getLinkInfo($participantId)
    {
        return self::model()->findAllByAttributes(array('participant_id' => $participantId));
    }


    /**
     * @param integer $iSurveyId
     * @return CDbDataReader
     */
    public function rebuildLinksFromTokenTable($iSurveyId)
    {
        $this->deleteLinksBySurvey($iSurveyId);
        $tableName = "{{tokens_".$iSurveyId."}}";
        $dateCreated = date('Y-m-d H:i:s', time());
        $query = "INSERT INTO ".SurveyLink::tableName()." (participant_id, token_id, survey_id, date_created) SELECT participant_id, tid, '".$iSurveyId."', '".$dateCreated."' FROM ".$tableName." WHERE participant_id IS NOT NULL";
        return Yii::app()->db->createCommand($query)
                    ->query();
    }

    /**
     * Delete a single survey_link based on a survey participants table entry(by token_id and survey_id)
     *
     * An entry in the survey_links table must be unique by the combination of Token_ID
     * (which is unique within a tokens table) and survey_id (which limits to one single
     * survey participants table).
     *
     * @param int[] $aTokenIds the unique ids of the entry in the survey participants table being deleted
     * @param int $surveyId the id of the survey for the link being deleted
     *
     * @return bool|CDbDataReader
     */
    function deleteTokenLink($aTokenIds, $surveyId)
    {
        $query = "DELETE FROM ".SurveyLink::tableName()
            ." WHERE token_id IN (".implode(", ", $aTokenIds).") AND survey_id=:survey_id";
        return Yii::app()->db->createCommand($query)
                    ->bindParam(":survey_id", $surveyId)
                    ->query();
    }

    /**
     * Delete all entries in the survey_link table that link to a particular survey_id
     * This function is used when a tokens_table is being dropped, and therefore all
     * links must be removed
     *
     * @param int $surveyId the SID of the survey whose tokens table is being dropped
     * @return bool|CDbDataReader
     */
    public function deleteLinksBySurvey($surveyId)
    {
        $query = "DELETE FROM ".SurveyLink::tableName()." WHERE survey_id = :survey_id";
        return Yii::app()->db->createCommand($query)
                    ->bindParam(":survey_id", $surveyId)
                    ->query();
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        $dateFormat = getDateFormatData(Yii::app()->session['dateformat']);
        return $dateFormat['phpdate'];
    }


    /**
     * @return TokenDynamic
     */
    public function getTokenDynamicModel()
    {
        $TokenDynamic = TokenDynamic::model($this->survey_id);
        return $TokenDynamic->findByPk($this->token_id);
    }

    /**
     * @return string
     * // TODO this should be in survey model
     */
    public function getSurveyName()
    {
        return $this->survey->currentLanguageSettings->surveyls_title;
    }

    /**
     * @return string
     */
    public function getLastInvited()
    {
        $inviteDate = $this->tokenDynamicModel['sent'];
        if ($inviteDate != "N") {
            $date = new DateTime($inviteDate);
            return $date->format($this->dateFormat);
        }
    }

    /**
     * @return string
     */
    public function getLastReminded()
    {
        $reminddate = $this->tokenDynamicModel['remindersent'];
        if ($reminddate != "N") {
            $date = new DateTime($reminddate);
            return $date->format($this->dateFormat);
        }
    }

    /**
     * @return string
     */
    public function getFormattedDateCreated()
    {
        $dateCreated = $this->date_created;
        $date = new DateTime($dateCreated);
        return $date->format($this->dateFormat);
    }

    /**
     * @return string
     */
    public function getIsSubmittedHtml()
    {
        if ($this->isSubmitted !== false) {
            $date = new DateTime($this->isSubmitted);
            $submittedAt = $date->format($this->dateFormat);
            return $submittedAt;
        } else {
            return '&#8211;';
        }
    }

    /**
     * @return boolean|string false or submit date
     */
    public function getIsSubmitted()
    {
        $submitDate = $this->tokenDynamicModel['completed'];
        return (($submitDate == "N") ? false : $submitDate);
    }

    /**
     * @return string html
     */
    public function getCheckbox()
    {
        return "<input type='checkbox' class='selector_toggleAllParticipantSurveys' value='[".$this->token_id.",".$this->survey_id.",\"".$this->participant_id."\"]' />";
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return array(
            'survey_id' => gT("Survey ID"),
            'token_id' => gT('Token ID'),
            'participant_id' => gT('Participant'),
            'date_created' => gT('Date added')
        );
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return array(
            /*
            array(
                "name" => 'checkbox',
                "type" => 'raw',
                "header" => "<input type='checkbox' id='action_toggleAllParticipantSurveys' />",
                "sortable" => false,
                "filter" => false
            ),
             */
            array(
                "value" => '$data->surveyName',
                'header' => gT('Survey name'),
                "sortable" => false,
                "filter" => false
            ),
            array(
                "name" => 'survey_id',
                'value' => '$data->surveyIdLink',
                "sortable" => false,
                "filter" => false,
                'type' => 'raw'
            ),
            array(
                "name" => 'token_id',
                "sortable" => false,
                "filter" => false
            ),
            array(
                "name" => 'date_created',
                "value" => '$data->formattedDateCreated',
                "sortable" => false,
                "filter" => false
            ),
            array(
                "header" => gT("Last invited"),
                "value" => '$data->lastInvited',
                "sortable" => false,
                "filter" => false
            ),
            array(
                "header" => gT("Submitted"),
                "value" => '$data->isSubmittedHtml',
                "type" => "raw",
                "sortable" => false,
                "filter" => false
            )
        );
    }

    /**
     * @return CActiveDataProvider
     */
    public function search()
    {
        $criteria = new CDbCriteria;
        $sort = new CSort;

        $criteria->compare('participant_id', $this->participant_id);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
            'sort'=>$sort,
            'pagination' => false
        ));
    }

    /**
     * Link to survey
     * @return string html
     */
    public function getSurveyIdLink()
    {
        $url = Yii::app()->getController()->createUrl('admin/survey/sa/view/surveyid/'.$this->survey_id);
        $link = CHtml::link($this->survey_id, $url);
        return $link;
    }
}
