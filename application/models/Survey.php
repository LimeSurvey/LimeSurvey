<?php

if (!defined('BASEPATH'))
    die('No direct script access allowed');
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
* 	$Id$
*/

class Survey extends CActiveRecord
{
    /**
    * Returns the table's name
    *
    * @access public
    * @return string
    */
    public function tableName()
    {
        return '{{surveys}}';
    }

    /**
    * Returns the table's primary key
    *
    * @access public
    * @return string
    */
    public function primaryKey()
    {
        return 'sid';
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
    * Returns this model's relations
    *
    * @access public
    * @return array
    */
    public function relations()
    {
        return array(
            'languagesettings' => array(self::HAS_MANY, 'Surveys_languagesettings', 'surveyls_survey_id'),
            'owner' => array(self::BELONGS_TO, 'User', '', 'on' => 't.owner_id = owner.uid'),
        );
    }

    /**
    * Returns this model's scopes
    *
    * @access public
    * @return array
    */
    public function scopes()
    {
        return array(
            'active' => array(
                'condition' => "active = 'Y'",
            ),
        );
    }

    /**
    * permission scope for this model
    *
    * @access public
    * @param int $loginID
    * @return CActiveRecord
    */
    public function permission($loginID)
    {
        $loginID = (int) $loginID;
        $criteria = $this->getDBCriteria();
        $criteria->mergeWith(array(
            'condition' => 'sid IN (SELECT sid FROM {{survey_permissions}} WHERE uid = :uid AND permission = :permission AND read_p = 1)',
        ));
        $criteria->params[':uid'] = $loginID;
        $criteria->params[':permission'] = 'survey';

        return $this;
    }

    /**
    * Returns additional languages formatted into a string
    *
    * @access public
    * @return array
    */
    public function getAdditionalLanguages()
    {
        $sLanguages = trim($this->additional_languages);
        if ($sLanguages != '')
            return explode(' ', $sLanguages);
        else
            return array();
    }

    /**
    * Returns the additional token attributes
    *
    * @access public
    * @return array
    */
    public function getTokenAttributes()
    {
        // !!! Legacy records support
        if (($attdescriptiondata = @unserialize($this->attributedescriptions)) === false)
        {
            $attdescriptiondata = explode("\n", $this->attributedescriptions);
            $fields = array();
            $languagesettings = array();
            foreach ($attdescriptiondata as $attdescription)
                if (trim($attdescription) != '')
                {
                    $fieldname = substr($attdescription, 0, strpos($attdescription, '='));
                    $desc = substr($attdescription, strpos($attdescription, '=') + 1);
                    $fields[$fieldname] = array(
                    'description' => $desc,
                    'mandatory' => 'N',
                    'show_register' => 'N',
                    );
                    $languagesettings[$fieldname] = $desc;
                }
                $ls = Surveys_languagesettings::model()->findByAttributes(array('surveyls_survey_id' => $this->sid, 'surveyls_language' => $this->language));
            self::model()->updateByPk($this->sid, array('attributedescriptions' => serialize($fields)));
            $ls->surveyls_attributecaptions = serialize($languagesettings);
            $ls->save();
            $attdescriptiondata = $fields;
        }
        return $attdescriptiondata;
    }

    /**
    * !!! Shouldn't this be moved to beforeSave?
    * Creates a new survey - does some basic checks of the suppplied data
    *
    * @param array $aData Array with fieldname=>fieldcontents data
    * @param boolean $xssfiltering Sets if the data for the new survey should be filtered for XSS
    * @return integer The new survey id
    */
    public function insertNewSurvey($aData, $xssfiltering = false)
    {

        do
        {
            if (isset($aData['wishSID'])) // if wishSID is set check if it is not taken already
            {
                $aData['sid'] = $aData['wishSID'];
                unset($aData['wishSID']);
            }
            else
                $aData['sid'] = randomChars(6, '123456789');

            $isresult = self::model()->findByPk($aData['sid']);
        }
        while (!is_null($isresult));

     //   $aData['datecreated'] = date("Y-m-d");
        if (isset($aData['startdate']) && trim($aData['startdate']) == '')
            unset($aData['startdate']);

        if (isset($aData['expires']) && trim($aData['expires']) == '')
            unset($aData['expires']);

        if (!isset($aData['datecreated']))
        {
            $aData['datecreated'] = date("Y-m-d");
        }

        if($xssfiltering)
        {
            $filter = new CHtmlPurifier();
            $filter->options = array('URI.AllowedSchemes'=>array(
            'http' => true,
            'https' => true,
            ));
            $aData["admin"] = $filter->purify($aData["admin"]);
            $aData["adminemail"] = $filter->purify($aData["adminemail"]);
            $aData["bounce_email"] = $filter->purify($aData["bounce_email"]);
            $aData["faxto"] = $filter->purify($aData["faxto"]);
        }

        $survey = new self;
        foreach ($aData as $k => $v)
            $survey->$k = $v;
        $survey->save();

        return $aData['sid'];
    }

    /**
    * Deletes a survey and all its data
    *
    * @access public
    * @param int $iSurveyID
    * @param bool @recursive
    * @return void
    */
    public function deleteSurvey($iSurveyID, $recursive=true)
    {
        Survey::model()->deleteByPk($iSurveyID);

        if ($recursive == true)
        {
            if (tableExists("{{survey_".intval($iSurveyID)."}}"))  //delete the survey_$iSurveyID table
            {
                Yii::app()->db->createCommand()->dropTable("{{survey_".intval($iSurveyID)."}}");
            }

            if (tableExists("{{survey_".intval($iSurveyID)."_timings}}"))  //delete the survey_$iSurveyID_timings table
            {
                Yii::app()->db->createCommand()->dropTable("{{survey_".intval($iSurveyID)."_timings}}");
            }

            if (tableExists("{{tokens_".intval($iSurveyID)."}}")) //delete the tokens_$iSurveyID table
            {
                Yii::app()->db->createCommand()->dropTable("{{tokens_".intval($iSurveyID)."}}");
            }

            $oResult = Questions::model()->findAllByAttributes(array('sid' => $iSurveyID));
            foreach ($oResult as $aRow)
            {
                Answers::model()->deleteAllByAttributes(array('qid' => Yii::app()->db->quoteValue($aRow['qid'])));
                Conditions::model()->deleteAllByAttributes(array('qid' => Yii::app()->db->quoteValue($aRow['qid'])));
                Question_attributes::model()->deleteAllByAttributes(array('qid' => Yii::app()->db->quoteValue($aRow['qid'])));
                Defaultvalues::model()->deleteAllByAttributes(array('qid' => $aRow['qid']));
            }

            Questions::model()->deleteAllByAttributes(array('sid' => $iSurveyID));
            Assessment::model()->deleteAllByAttributes(array('sid' => $iSurveyID));
            Groups::model()->deleteAllByAttributes(array('sid' => $iSurveyID));
            Surveys_languagesettings::model()->deleteAllByAttributes(array('surveyls_survey_id' => $iSurveyID));
            Survey_permissions::model()->deleteAllByAttributes(array('sid' => $iSurveyID));
            Saved_control::model()->deleteAllByAttributes(array('sid' => $iSurveyID));
            Survey_url_parameters::model()->deleteAllByAttributes(array('sid' => $iSurveyID));
            Quota::model()->deleteQuota(array('sid' => $iSurveyID), true);
        }
    }
}
