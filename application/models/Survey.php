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
*/

class Survey extends LSActiveRecord
{
    /**
     * This is a static cache, it lasts only during the active request. If you ever need
     * to clear it, like on activation of a survey when in the same request a row is read,
     * saved and read again you can use resetCache() method.
     *
     * @var array
     */
    protected $findByPkCache = array();

    /**
     * Returns the title of the survey. Uses the current language and
     * falls back to the surveys' default language if the current language is not available.
     */
    public function getLocalizedTitle()
    {
        if (isset($this->languagesettings[App()->lang->langcode]))
        {
            return $this->languagesettings[App()->lang->langcode]->surveyls_title;
        }
        else
        {
            return $this->languagesettings[$this->language]->surveyls_title;
        }
    }
    /**
     * Expires a survey. If the object was invoked using find or new surveyId can be ommited.
     * @param int $surveyId
     */
    public function expire($surveyId = null)
    {
        $dateTime = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig('timeadjust'));
        $dateTime = dateShift($dateTime, "Y-m-d H:i:s", '-1 day');

        if (!isset($surveyId))
        {
            $this->expires = $dateTime;
            if ($this->scenario == 'update')
            {
                return $this->save();
            }
        }
        else
        {
            self::model()->updateByPk($surveyId,array('expires' => $dateTime));
        }

    }

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
    * @return Survey
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
		$alias = $this->getTableAlias();
        return array(
			'languagesettings' => array(self::HAS_MANY, 'SurveyLanguageSetting', 'surveyls_survey_id', 'index' => 'surveyls_language'),
            'defaultlanguage' => array(self::BELONGS_TO, 'SurveyLanguageSetting', array('language' => 'surveyls_language', 'sid' => 'surveyls_survey_id'), 'together' => true),
            'owner' => array(self::BELONGS_TO, 'User', '', 'on' => "$alias.owner_id = owner.uid"),
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
            'active' => array('condition' => "active = 'Y'"),
            'open' => array('condition' => '(startdate <= :now1 OR startdate IS NULL) AND (expires >= :now2 OR expires IS NULL)', 'params' => array(
                ':now1' => dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig("timeadjust")),
                ':now2' => dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig("timeadjust"))
                )
            ),
            'public' => array('condition' => "listpublic = 'Y'"),
            'registration' => array('condition' => "allowregister = 'Y' AND startdate > :now3 AND (expires < :now4 OR expires IS NULL)", 'params' => array(
                ':now3' => dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig("timeadjust")),
                ':now4' => dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig("timeadjust"))
            ))
        );
    }

    /**
    * Returns this model's validation rules
    *
    */
    public function rules()
    {
        return array(
        array('datecreated', 'default','value'=>date("Y-m-d")),
        array('startdate', 'default','value'=>NULL),
        array('expires', 'default','value'=>NULL),
        array('admin,adminemail,bounce_email,faxto','LSYii_Validators'),
        array('active', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('anonymized', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('savetimings', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('datestamp', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('usecookie', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('allowregister', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('allowsave', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('autoredirect', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('allowprev', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('printanswers', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('ipaddr', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('refurl', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('publicstatistics', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('publicgraphs', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('listpublic', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('htmlemail', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('sendconfirmation', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('tokenanswerspersistence', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('assessments', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('usetokens', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('showxquestions', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('shownoanswer', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('showwelcome', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('showprogress', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('questionindex', 'numerical','min' => 0, 'max' => 2, 'allowEmpty'=>false),
        array('nokeyboard', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('alloweditaftercompletion', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
        array('bounceprocessing', 'in','range'=>array('L','N','G'), 'allowEmpty'=>true),
        array('usecaptcha', 'in','range'=>array('A','B','C','D','X','R','S','N'), 'allowEmpty'=>true),
        array('showgroupinfo', 'in','range'=>array('B','N','D','X'), 'allowEmpty'=>true),
        array('showqnumcode', 'in','range'=>array('B','N','C','X'), 'allowEmpty'=>true),
        array('format', 'in','range'=>array('G','S','A'), 'allowEmpty'=>true),
        array('googleanalyticsstyle', 'numerical', 'integerOnly'=>true, 'min'=>'0', 'max'=>'2', 'allowEmpty'=>true),
        array('autonumber_start','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
        array('tokenlength','numerical', 'integerOnly'=>true,'allowEmpty'=>true, 'min'=>'5', 'max'=>'36'),
        array('bouncetime','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
        array('navigationdelay','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
      //  array('expires','date', 'format'=>array('yyyy-MM-dd', 'yyyy-MM-dd HH:mm', 'yyyy-MM-dd HH:mm:ss',), 'allowEmpty'=>true),
      //  array('startdate','date', 'format'=>array('yyyy-MM-dd', 'yyyy-MM-dd HH:mm', 'yyyy-MM-dd HH:mm:ss',), 'allowEmpty'=>true),
      //  array('datecreated','date', 'format'=>array('yyyy-MM-dd', 'yyyy-MM-dd HH:mm', 'yyyy-MM-dd HH:mm:ss',), 'allowEmpty'=>true),
      // Date rules currently don't work properly with MSSQL, deactivating for now
        array('template', 'tmplfilter'),
        );
    }

    /**
    * Defines the customs validation rule tmplfilter
    *
    * @param mixed $attribute
    * @param mixed $params
    */
    public function tmplfilter($attribute,$params)
    {
        if(!array_key_exists($this->$attribute,getTemplateList()))
            $this->$attribute = 'default';
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
            'condition' => 'sid IN (SELECT entity_id FROM {{permissions}} WHERE entity = :entity AND  uid = :uid AND permission = :permission AND read_p = 1)
                            OR owner_id = :owner_id',
        ));
        $criteria->params[':uid'] = $loginID;
        $criteria->params[':permission'] = 'survey';
        $criteria->params[':owner_id'] = $loginID;
        $criteria->params[':entity'] = 'survey';

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
    * Returns all languages array
    *
    * @access public
    * @return array
    */
    public function getAllLanguages()
    {
        $sLanguages = self::getAdditionalLanguages();
        $baselang=$this->language;
        array_unshift($sLanguages,$baselang);
        return $sLanguages;
    }

    /**
    * Returns the additional token attributes
    *
    * @access public
    * @return array
    */
    public function getTokenAttributes()
    {
        $attdescriptiondata = decodeTokenAttributes($this->attributedescriptions);
        // checked for invalid data
        if($attdescriptiondata == null)
        {
            return array();
        }
        // Catches malformed data
        if ($attdescriptiondata && strpos(key(reset($attdescriptiondata)),'attribute_')===false)
        {
            // don't know why yet but this breaks normal tokenAttributes functionning
            //$attdescriptiondata=array_flip(GetAttributeFieldNames($this->sid));
        }
        elseif (is_null($attdescriptiondata))
        {
            $attdescriptiondata=array();
        }
        // Legacy records support
        if ($attdescriptiondata === false)
        {
            $attdescriptiondata = explode("\n", $this->attributedescriptions);
            $fields = array();
            $languagesettings = array();
            foreach ($attdescriptiondata as $attdescription)
            {
                if (trim($attdescription) != '')
                {
                    $fieldname = substr($attdescription, 0, strpos($attdescription, '='));
                    $desc = substr($attdescription, strpos($attdescription, '=') + 1);
                    $fields[$fieldname] = array(
                    'description' => $desc,
                    'mandatory' => 'N',
                    'show_register' => 'N',
                    'cpdbmap' =>''
                    );
                    $languagesettings[$fieldname] = $desc;
                }
            }
            $ls = SurveyLanguageSetting::model()->findByAttributes(array('surveyls_survey_id' => $this->sid, 'surveyls_language' => $this->language));
            self::model()->updateByPk($this->sid, array('attributedescriptions' => json_encode($fields)));
            $ls->surveyls_attributecaptions = json_encode($languagesettings);
            $ls->save();
            $attdescriptiondata = $fields;
        }
        $aCompleteData=array();
        foreach ($attdescriptiondata as $sKey=>$aValues)
        {
            if (!is_array($aValues)) $aValues=array();
            $aCompleteData[$sKey]= array_merge(array(
                    'description' => '',
                    'mandatory' => 'N',
                    'show_register' => 'N',
                    'cpdbmap' =>''
                    ),$aValues);
        }
        return $aCompleteData;
    }

    /**
     * Returns true in a token table exists for the given $surveyId
     *
     * @staticvar array $tokens
     * @param int $iSurveyID
     * @return boolean
     */
    public function hasTokens($iSurveyID) {
        static $tokens = array();
        $iSurveyID = (int) $iSurveyID;

        if (!isset($tokens[$iSurveyID])) {
            // Make sure common_helper is loaded
            Yii::import('application.helpers.common_helper', true);

            $tokens_table = "{{tokens_{$iSurveyID}}}";
            if (tableExists($tokens_table)) {
                $tokens[$iSurveyID] = true;
            } else {
                $tokens[$iSurveyID] = false;
            }
        }

        return $tokens[$iSurveyID];
    }


    /**
    * Creates a new survey - does some basic checks of the suppplied data
    *
    * @param array $aData Array with fieldname=>fieldcontents data
    * @return integer The new survey id
    */
    public function insertNewSurvey($aData)
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

        $survey = new self;
        foreach ($aData as $k => $v)
            $survey->$k = $v;
        $sResult= $survey->save();
        if (!$sResult)
        {
            tracevar($survey->getErrors());
            return false;
        }
        else return $aData['sid'];
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

            $oResult = Question::model()->findAllByAttributes(array('sid' => $iSurveyID));
            foreach ($oResult as $aRow)
            {
                Answer::model()->deleteAllByAttributes(array('qid' => $aRow['qid']));
                Condition::model()->deleteAllByAttributes(array('qid' =>$aRow['qid']));
                QuestionAttribute::model()->deleteAllByAttributes(array('qid' => $aRow['qid']));
                DefaultValue::model()->deleteAllByAttributes(array('qid' => $aRow['qid']));
            }

            Question::model()->deleteAllByAttributes(array('sid' => $iSurveyID));
            Assessment::model()->deleteAllByAttributes(array('sid' => $iSurveyID));
            QuestionGroup::model()->deleteAllByAttributes(array('sid' => $iSurveyID));
            SurveyLanguageSetting::model()->deleteAllByAttributes(array('surveyls_survey_id' => $iSurveyID));
            Permission::model()->deleteAllByAttributes(array('entity_id' => $iSurveyID, 'entity'=>'survey'));
            SavedControl::model()->deleteAllByAttributes(array('sid' => $iSurveyID));
            SurveyURLParameter::model()->deleteAllByAttributes(array('sid' => $iSurveyID));
            //Remove any survey_links to the CPDB
            SurveyLink::model()->deleteLinksBySurvey($iSurveyID);
            Quota::model()->deleteQuota(array('sid' => $iSurveyID), true);
        }
    }

    public function findByPk($pk, $condition = '', $params = array()) {
        if (empty($condition) && empty($params)) {
            if (array_key_exists($pk, $this->findByPkCache)) {
                return $this->findByPkCache[$pk];
            } else {
                $result = parent::findByPk($pk, $condition, $params);
                if (!is_null($result)) {
                    $this->findByPkCache[$pk] = $result;
                }

                return $result;
            }
        }

        return parent::findByPk($pk, $condition, $params);
    }

    /**
     * findByPk uses a cache to store a result. Use this method to force clearing that cache.
     */
    public function resetCache() {
        $this->findByPkCache = array();
    }

    /**
     * Attribute renamed to questionindex in dbversion 169
     * Y maps to 1 otherwise 0;
     * @param type $value
     */
    public function setAllowjumps($value)
    {
        if ($value === 'Y') {
            $this->questionindex = 1;
        } else {
            $this->questionindex = 0;
        }
    }
}
