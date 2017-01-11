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
    /* Default settings for new survey */
    /* This settings happen for whole new Survey, not only admin/survey/sa/newsurvey */
    public $format = 'G';
    public $htmlemail='Y';

    public $full_answers_account=null;
    public $partial_answers_account=null;
    public $searched_value;

    private $fac;
    private $pac;

    /**
     * init to set default
     *
     */
    public function init()
    {
        $this->template = Template::templateNameFilter(Yii::app()->getConfig('defaulttemplate'));
        $validator= new LSYii_Validators;
        $this->language = $validator->languageFilter(Yii::app()->getConfig('defaultlang'));
        $this->attachEventHandler("onAfterFind", array($this,'fixSurveyAttribute'));
    }

    /* Add virtual survey attribute labels for gridView*/
    public function attributeLabels() {
        return array(
            /* Your other attribute labels */
            'running' => gT('running')
        );
    }

    /**
     * Returns the title of the survey. Uses the current language and
     * falls back to the surveys' default language if the current language is not available.
     */
    public function getLocalizedTitle()
    {
        if (isset($this->languagesettings[App()->language]))
        {
            return $this->languagesettings[App()->language]->surveyls_title;
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
        $dateTime = dateShift($dateTime, "Y-m-d H:i:s", '-1 minute');

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

            'permissions'     => array(self::HAS_MANY, 'Permission', array( 'entity_id'=> 'sid'  ), 'together' => true ), //
            'languagesettings' => array(self::HAS_MANY, 'SurveyLanguageSetting', 'surveyls_survey_id', 'index' => 'surveyls_language', 'together' => true),
            'defaultlanguage' => array(self::BELONGS_TO, 'SurveyLanguageSetting', array('language' => 'surveyls_language', 'sid' => 'surveyls_survey_id'), 'together' => true),
            'correct_relation_defaultlanguage' => array(self::HAS_ONE, 'SurveyLanguageSetting', array('surveyls_language' => 'language', 'surveyls_survey_id' => 'sid'), 'together' => true),
            'owner' => array(self::BELONGS_TO, 'User', 'owner_id', 'together' => true),
            'groups' => array(self::HAS_MANY, 'QuestionGroup', 'sid', 'together' => true),
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
            array('admin,faxto','LSYii_Validators'),
            array('adminemail','filter', 'filter'=>'trim'),
            array('bounce_email','filter', 'filter'=>'trim'),
            array('bounce_email','LSYii_EmailIDNAValidator', 'allowEmpty'=>true),
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
            array('tokenlength', 'default', 'value'=>15),
            array('tokenlength','numerical', 'integerOnly'=>true,'allowEmpty'=>false, 'min'=>'5', 'max'=>'36'),
            array('bouncetime','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
            array('navigationdelay','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
            array('template', 'filter', 'filter'=>array($this,'filterTemplateSave')),
            array('language','LSYii_Validators','isLanguage'=>true),
            array('language', 'required', 'on' => 'insert'),
            array('language', 'filter', 'filter'=>'trim'),
            array('additional_languages', 'filter', 'filter'=>'trim'),
            array('additional_languages','LSYii_Validators','isLanguageMulti'=>true),
            array('running', 'safe', 'on'=>'search'),
            // Date rules currently don't work properly with MSSQL, deactivating for now
            //  array('expires','date', 'format'=>array('yyyy-MM-dd', 'yyyy-MM-dd HH:mm', 'yyyy-MM-dd HH:mm:ss',), 'allowEmpty'=>true),
            //  array('startdate','date', 'format'=>array('yyyy-MM-dd', 'yyyy-MM-dd HH:mm', 'yyyy-MM-dd HH:mm:ss',), 'allowEmpty'=>true),
            //  array('datecreated','date', 'format'=>array('yyyy-MM-dd', 'yyyy-MM-dd HH:mm', 'yyyy-MM-dd HH:mm:ss',), 'allowEmpty'=>true),
        );
    }


    /**
    * fixSurveyAttribute to fix and/or add some survey attribute
    * - Fix template name to be sure template exist
    */
    public function fixSurveyAttribute($event)
    {
        $this->template=Template::templateNameFilter($this->template);
    }

    /**
    * filterTemplateSave to fix some template name
    */
    public function filterTemplateSave($sTemplateName)
    {
        if(!Permission::model()->hasTemplatePermission($sTemplateName))
        {
            if(!$this->isNewRecord)// Reset to default only if different from actual value
            {
                $oSurvey=self::model()->findByPk($this->sid);
                if($oSurvey->template != $sTemplateName)// No need to test !is_null($oSurvey)
                    $sTemplateName = Yii::app()->getConfig('defaulttemplate');
            }
            else
            {
                $sTemplateName = Yii::app()->getConfig('defaulttemplate');
            }
        }
        return Template::templateNameFilter($sTemplateName);
    }

    /**
    * permission scope for this model
    * Actually only test if user have minimal access to survey (read)
    * @access public
    * @param int $loginID
    * @return CActiveRecord
    *
    * TODO: replace this by a correct relation
    */
    public function permission($loginID)
    {
        $loginID = (int) $loginID;
        if(Permission::model()->hasGlobalPermission('surveys','read',$loginID))// Test global before adding criteria
            return $this;
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
            if(preg_match("/^attribute_[0-9]{1,}$/",$sKey))
            {
              $aCompleteData[$sKey]= array_merge(array(
                      'description' => '',
                      'mandatory' => 'N',
                      'show_register' => 'N',
                      'cpdbmap' =>''
                      ),$aValues);
            }
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

    public function getHasTokens()
    {
        $hasTokens = $this->hasTokens($this->sid) ;
        if($hasTokens)
        {
            return gT('Yes');
        }
        else
        {
            return gT('No');
        }
    }

     /**
     * Returns the value for the SurveyEdit GoogleAnalytics API-Key UseGlobal Setting
     *
     */
    public function getGoogleanalyticsapikeysetting(){
        if($this->googleanalyticsapikey === "9999useGlobal9999")
        {
            return "G";
        } 
        else if($this->googleanalyticsapikey == "")
        {
            return "N";
        }
        else 
        {
            return "Y";
        }
    }
    public function setGoogleanalyticsapikeysetting($value){
        if($value == "G")
        {
            $this->googleanalyticsapikey = "9999useGlobal9999";
        } 
        else if($value == "N")
        {
           $this->googleanalyticsapikey = "";
        }
    }

     /**
     * Returns the value for the SurveyEdit GoogleAnalytics API-Key UseGlobal Setting
     *
     */
    public function getGoogleanalyticsapikey(){
        if($this->googleanalyticsapikey === "9999useGlobal9999")
        {
            return getGlobalSetting(googleanalyticsapikey);
        } 
        else 
        {
            return $this->googleanalyticsapikey;
        }
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
            tracevar($aData);
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
    * @return boolean
    */
    public function deleteSurvey($iSurveyID, $recursive=true)
    {

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'survey', 'delete'))
        {
            if ( Survey::model()->deleteByPk($iSurveyID) )
            {
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

                    /* Remove User/global settings part : need Question and QuestionGroup*/
                    // Settings specific for this survey
                    $oCriteria = new CDbCriteria();
                    $oCriteria->compare('stg_name','last_%',true,'AND',false);
                    $oCriteria->compare('stg_value',$iSurveyID,false,'AND');
                    SettingGlobal::model()->deleteAll($oCriteria);
                    // Settings specific for this survey, 2nd part
                    $oCriteria = new CDbCriteria();
                    $oCriteria->compare('stg_name','last_%'.$iSurveyID.'%',true,'AND',false);
                    SettingGlobal::model()->deleteAll($oCriteria);
                    // All Group id from this survey for ALL users
                    $aGroupId=CHtml::listData(QuestionGroup::model()->findAll(array('select'=>'gid','condition'=>'sid=:sid','params'=>array(':sid'=>$iSurveyID))),'gid','gid');
                    $oCriteria = new CDbCriteria();
                    $oCriteria->compare('stg_name','last_question_gid_%',true,'AND',false);
                    if(Yii::app()->db->getDriverName() == 'pgsql') // pgsql need casting, unsure for mssql
                    {
                        $oCriteria->addInCondition('CAST(stg_value as '.App()->db->schema->getColumnType("integer").')',$aGroupId);
                    }
                    else //mysql App()->db->schema->getColumnType("integer") give int(11), mssql seems to have issue if cast alpha to numeric
                    {
                        $oCriteria->addInCondition('stg_value',$aGroupId);
                    }
                    SettingGlobal::model()->deleteAll($oCriteria);
                    // All Question id from this survey for ALL users
                    $aQuestionId=CHtml::listData(Question::model()->findAll(array('select'=>'qid','condition'=>'sid=:sid','params'=>array(':sid'=>$iSurveyID))),'qid','qid');
                    $oCriteria = new CDbCriteria();
                    $oCriteria->compare('stg_name','last_question_%',true,'OR',false);
                    if(Yii::app()->db->getDriverName() == 'pgsql')
                    {
                        $oCriteria->addInCondition('CAST(stg_value as '.App()->db->schema->getColumnType("integer").')',$aQuestionId);
                    }
                    else
                    {
                        $oCriteria->addInCondition('stg_value',$aQuestionId);
                    }
                    SettingGlobal::model()->deleteAll($oCriteria);

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
                return true;
            }
        }
        return false;
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

    public function getSurveyinfo()
    {
        $iSurveyID = $this->sid;
        $baselang = $this->language;

        $condition = array('sid' => $iSurveyID, 'language' => $baselang);

        //// TODO : replace this with a HAS MANY relation !
        $sumresult1 = Survey::model()->with(array('languagesettings'=>array('condition'=>'surveyls_language=language')))->find('sid = :surveyid', array(':surveyid' => $iSurveyID)); //$sumquery1, 1) ; //Checked
        if (is_null($sumresult1))
        {
            Yii::app()->session['flashmessage'] = gT("Invalid survey ID");
            $this->getController()->redirect(array("admin/index"));
        } //  if surveyid is invalid then die to prevent errors at a later time
        $surveyinfo = $sumresult1->attributes;
        $surveyinfo = array_merge($surveyinfo, $sumresult1->defaultlanguage->attributes);
        $surveyinfo = array_map('flattenText', $surveyinfo);
        //$surveyinfo["groups"] = $this->groups;
        return $surveyinfo;
    }


    public function getCreationDate()
    {
        $dateformatdata=getDateFormatData(Yii::app()->session['dateformat']);
        return convertDateTimeFormat($this->datecreated, 'Y-m-d', $dateformatdata['phpdate']);
    }

    public function getAnonymizedResponses()
    {
        $anonymizedResponses = ($this->anonymized == 'Y')?gT('Yes'):gT('No');
        return $anonymizedResponses;
    }

    public function getActiveWord()
    {
        $activeword = ($this->active == 'Y')?gT('Yes'):gT('No');
        return $activeword;
    }

    /**
     * Get state of survey, which can be one of five:
     * 1. Not active
     * 2. Expired
     * 3. Will expire in the future (running now)
     * 3. Will run in future
     * 4. Running now (no expiration date)
     *
     * Code copied from getRunning below.
     *
     * @return string - 'inactive', 'expired', 'willRun', 'willExpire' or 'running'
     */
    public function getState()
    {
        if($this->active == 'N')
        {
            return 'inactive';
        }
        elseif ($this->expires != '' || $this->startdate != '')
        {
            // Time adjust
            $sNow    = date("Y-m-d H:i:s", strtotime(Yii::app()->getConfig('timeadjust'), strtotime(date("Y-m-d H:i:s"))) );
            $sStop   = ($this->expires != '')?date("Y-m-d H:i:s", strtotime(Yii::app()->getConfig('timeadjust'), strtotime($this->expires)) ):$sNow;
            $sStart  =  ($this->startdate != '')?date("Y-m-d H:i:s", strtotime(Yii::app()->getConfig('timeadjust'), strtotime($this->startdate)) ):$sNow;

            // Time comparaison
            $oNow   = new DateTime($sNow);
            $oStop  = new DateTime($sStop);
            $oStart = new DateTime($sStart);

            $bExpired = ($oStop < $oNow);
            $bWillRun = ($oStart > $oNow);

            if ($bExpired)
            {
                return 'expired';
            }
            elseif ($bWillRun)
            {
                return 'willRun';
            }
            else
            {
                return 'willExpire';
            }
        }
        // If it's active, and doesn't have expire date, it's running
        else
        {
            return 'running';
        }
    }

    /**
     * @todo Document code, please.
     */
    public function getRunning()
    {

        // If the survey is not active, no date test is needed
        if($this->active == 'N')
        {
            $running = '<a href="'.App()->createUrl('/admin/survey/sa/view/surveyid/'.$this->sid).'" class="survey-state" data-toggle="tooltip" title="'.gT('Inactive').'"><span class="fa fa-stop text-warning"></span></a>';
        }
        // If it's active, then we check if not expired
        elseif ($this->expires != '' || $this->startdate != '')
        {
            // Time adjust
            $sNow    = date("Y-m-d H:i:s", strtotime(Yii::app()->getConfig('timeadjust'), strtotime(date("Y-m-d H:i:s"))) );
            $sStop   = ($this->expires != '')?date("Y-m-d H:i:s", strtotime(Yii::app()->getConfig('timeadjust'), strtotime($this->expires)) ):$sNow;
            $sStart  =  ($this->startdate != '')?date("Y-m-d H:i:s", strtotime(Yii::app()->getConfig('timeadjust'), strtotime($this->startdate)) ):$sNow;

            // Time comparaison
            $oNow   = new DateTime($sNow);
            $oStop  = new DateTime($sStop);
            $oStart = new DateTime($sStart);

            $bExpired = ($oStop < $oNow);
            $bWillRun = ($oStart > $oNow);

            $sStop = convertToGlobalSettingFormat( $sStop );
            $sStart = convertToGlobalSettingFormat( $sStart );

            // Icon generaton (for CGridView)
            $sIconRunning = '<a href="'.App()->createUrl('/admin/survey/sa/view/surveyid/'.$this->sid).'" class="survey-state" data-toggle="tooltip" title="'.gT('Expire').': '.$sStop.'"><span class="fa  fa-clock-o text-success"></span></a>';
            $sIconExpired = '<a href="'.App()->createUrl('/admin/survey/sa/view/surveyid/'.$this->sid).'" class="survey-state" data-toggle="tooltip" title="'.gT('Expired').': '.$sStop.'"><span class="fa fa fa-step-forward text-warning"></span></a>';
            $sIconFuture  = '<a href="'.App()->createUrl('/admin/survey/sa/view/surveyid/'.$this->sid).'" class="survey-state" data-toggle="tooltip" title="'.gT('Start').': '.$sStart.'"><span class="fa  fa-clock-o text-warning"></span></a>';

            // Icon parsing
            if ( $bExpired || $bWillRun )
            {
                // Expire prior to will start
                $running = ($bExpired)?$sIconExpired:$sIconFuture;
            }
            else
            {
                $running = $sIconRunning;
            }
        }
        // If it's active, and doesn't have expire date, it's running
        else
        {
            $running = '<a href="'.App()->createUrl('/admin/survey/sa/view/surveyid/'.$this->sid).'" class="survey-state" data-toggle="tooltip" title="'.gT('Active').'"><span class="fa fa-play text-success"></span></a>';
            //$running = '<div class="survey-state"><span class="fa fa-play text-success"></span></div>';
        }

        return $running;

    }

    public function getPartialAnswers()
    {
        $table = '{{survey_' . $this->sid . '}}';
        Yii::app()->cache->flush();
        if (!Yii::app()->db->schema->getTable($table))
        {
            return null;
        }
        else
        {
            $answers = Yii::app()->db->createCommand()
                ->select('*')
                ->from($table)
                ->where('submitdate IS NULL')
                ->queryAll();

            return $answers;
        }
    }

    public function getIsActive()
    {
        return ($this->active === 'Y');
    }

    public function getFullAnswers()
    {
        $table = '{{survey_' . $this->sid . '}}';
        Yii::app()->cache->flush();
        if (!Yii::app()->db->schema->getTable($table))
        {
            return null;
        }
        else
        {
            $answers = Yii::app()->db->createCommand()
                ->select('*')
                ->from($table)
                ->where('submitdate IS NOT NULL')
                ->queryAll();

            return $answers;
        }
    }

    public function getCountFullAnswers()
    {
        if($this->fac!==null)
        {
            return $this->fac;
        }
        else
        {
            $sResponseTable = '{{survey_' . $this->sid . '}}';
            Yii::app()->cache->flush();
            if ($this->active!='Y')
            {
                $this->fac = 0;
                return '0';
            }
            else
            {
                $answers = Yii::app()->db->createCommand('select count(*) from '.$sResponseTable.' where submitdate IS NOT NULL')->queryScalar();
                $this->fac = $answers;
                return $answers;
            }
        }
    }

    public function getCountPartialAnswers()
    {
        if($this->pac!==null)
        {
            return $this->pac;
        }
        else
        {
            $table = '{{survey_' . $this->sid . '}}';
            Yii::app()->cache->flush();
            if ($this->active!='Y')
            {
                $this->pac = 0;
                return 0;
            }
            else
            {
                $answers = Yii::app()->db->createCommand('select count(*) from '.$table.' where submitdate IS NULL')->queryScalar();
                $this->pac = $answers;
                return $answers;
            }
        }
    }

    public function getCountTotalAnswers()
    {
        if ($this->pac!==null && $this->fac!==null)
        {
            return ($this->pac + $this->fac);
        }
        else
        {
                  return ($this->countFullAnswers + $this->countPartialAnswers);
        }
    }

    public function getbuttons()
    {
        $sSummaryUrl  = App()->createUrl("/admin/survey/sa/view/surveyid/".$this->sid);
        $sEditUrl     = App()->createUrl("/admin/survey/sa/editlocalsettings/surveyid/".$this->sid);
        $sDeleteUrl   = App()->createUrl("/admin/survey/sa/delete/surveyid/".$this->sid);
        $sStatUrl     = App()->createUrl("/admin/statistics/sa/simpleStatistics/surveyid/".$this->sid);
        $sAddGroup    = App()->createUrl("/admin/questiongroups/sa/add/surveyid/".$this->sid);;
        $sAddquestion = App()->createUrl("/admin/questions/sa/newquestion/surveyid/".$this->sid);;

        $button = '';

        if (Permission::model()->hasSurveyPermission($this->sid, 'survey', 'update'))
        {
            $button .= '<a class="btn btn-default" href="'.$sEditUrl.'" role="button" data-toggle="tooltip" title="'.gT('General settings & texts').'"><span class="glyphicon glyphicon-cog" ></span></a>';
        }

        if(Permission::model()->hasSurveyPermission($this->sid, 'statistics', 'read') && $this->active=='Y' )
        {
            $button .= '<a class="btn btn-default" href="'.$sStatUrl.'" role="button" data-toggle="tooltip" title="'.gT('Statistics').'"><span class="glyphicon glyphicon-stats text-success" ></span></a>';
        }

        if (Permission::model()->hasSurveyPermission($this->sid, 'survey', 'create'))
        {
            if($this->active!='Y')
            {
                $groupCount = QuestionGroup::model()->countByAttributes(array('sid' => $this->sid, 'language' => $this->language)); //Checked
                if($groupCount > 0)
                {
                    $button .= '<a class="btn btn-default" href="'.$sAddquestion.'" role="button" data-toggle="tooltip" title="'.gT('Add new question').'"><span class="icon-add text-success" ></span></a>';
                }
                else
                {
                    $button .= '<a class="btn btn-default" href="'.$sAddGroup.'" role="button" data-toggle="tooltip" title="'.gT('Add new group').'"><span class="icon-add text-success" ></span></a>';
                }
            }
        }

        $previewUrl = Yii::app()->createUrl("survey/index/sid/");
        $previewUrl .= '/'.$this->sid;

        //$button = '<a class="btn btn-default open-preview" aria-data-url="'.$previewUrl.'" aria-data-language="'.$this->language.'" href="# role="button" ><span class="glyphicon glyphicon-eye-open"  ></span></a> ';

        return $button;
    }

    public function search()
    {
        $pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);

        $sort = new CSort();
        $sort->attributes = array(
          'survey_id'=>array(
            'asc'=>'t.sid asc',
            'desc'=>'t.sid desc',
          ),
          'title'=>array(
            'asc'=>'correct_relation_defaultlanguage.surveyls_title asc',
            'desc'=>'correct_relation_defaultlanguage.surveyls_title desc',
          ),

          'creation_date'=>array(
            'asc'=>'t.datecreated asc',
            'desc'=>'t.datecreated desc',
          ),

          'owner'=>array(
            'asc'=>'owner.users_name asc',
            'desc'=>'owner.users_name desc',
          ),

          'anonymized_responses'=>array(
            'asc'=>'t.anonymized asc',
            'desc'=>'t.anonymized desc',
          ),

          'running'=>array(
            'asc'=>'t.active asc, t.expires asc',
            'desc'=>'t.active desc, t.expires desc',
          ),

        );
        $sort->defaultOrder = array('creation_date' => CSort::SORT_DESC);

        $criteria = new LSDbCriteria;
        $aWithRelations = array('correct_relation_defaultlanguage');

        // Search filter
        $sid_reference = (Yii::app()->db->getDriverName() == 'pgsql' ?' t.sid::varchar' : 't.sid');
        $aWithRelations[] = 'owner';
        $criteria->compare($sid_reference, $this->searched_value, true);
        $criteria->compare('t.admin', $this->searched_value, true, 'OR');
        $criteria->compare('owner.users_name', $this->searched_value, true, 'OR');
        $criteria->compare('correct_relation_defaultlanguage.surveyls_title', $this->searched_value, true, 'OR');



        // Active filter
        if(isset($this->active))
        {
            if($this->active == 'N' || $this->active == "Y")
            {
                $criteria->compare("t.active", $this->active, false);
            }
            else
            {
                // Time adjust
                $sNow = date("Y-m-d H:i:s", strtotime(Yii::app()->getConfig('timeadjust'), strtotime(date("Y-m-d H:i:s"))) );

                if($this->active == "E")
                {
                    $criteria->compare("t.active",'Y');
                    $criteria->addCondition("t.expires <'$sNow'");
                }
                if($this->active == "S")
                {
                    $criteria->compare("t.active",'Y');
                    $criteria->addCondition("t.startdate >'$sNow'");
                }
                if($this->active == "R")
                {
                    $now = new CDbExpression("NOW()");

                    $criteria->compare("t.active",'Y');
                    $subCriteria1 = new CDbCriteria;
                    $subCriteria2 = new CDbCriteria;
                    $subCriteria1->addCondition($now.' > t.startdate', 'OR');
                    $subCriteria2->addCondition($now.' < t.expires', 'OR');
                    $subCriteria1->addCondition('t.expires IS NULL', "OR");
                    $subCriteria2->addCondition('t.startdate IS NULL', "OR");
                    $criteria->mergeWith($subCriteria1);
                    $criteria->mergeWith($subCriteria2);
                }
            }
        }


        $criteria->with=$aWithRelations;

        // Permission
        // Note: reflect Permission::hasPermission
        if(!Permission::model()->hasGlobalPermission("surveys",'read'))
        {
            $criteriaPerm = new CDbCriteria;

            // Multiple ON conditions with string values such as 'survey'
            $criteriaPerm->mergeWith(array(
                'join'=>"LEFT JOIN {{permissions}} AS permissions ON (permissions.entity_id = t.sid AND permissions.permission='survey' AND permissions.entity='survey' AND permissions.uid='".Yii::app()->user->id."') ",
            ));
            $criteriaPerm->compare('t.owner_id', Yii::app()->user->id, false);
            $criteriaPerm->compare('permissions.read_p', '1', false, 'OR');
            $criteria->mergeWith($criteriaPerm, 'AND');
        }
        // $criteria->addCondition("t.blabla == 'blub'");
        $dataProvider=new CActiveDataProvider('Survey', array(
            'sort'=>$sort,
            'criteria'=>$criteria,
            'pagination'=>array(
                'pageSize'=>$pageSize,
            ),
        ));

        $dataProvider->setTotalItemCount($this->count($criteria));

        return $dataProvider;
    }

    /**
     * Transcribe from 3 checkboxes to 1 char for captcha usages
     * Uses variables from $_POST
     *
     * 'A' = All three captcha enabled
     * 'B' = All but save and load
     * 'C' = All but registration
     * 'D' = All but survey access
     * 'X' = Only survey access
     * 'R' = Only registration
     * 'S' = Only save and load
     * 'N' = None
     *
     * @return string One character that corresponds to captcha usage
     * @todo Should really be saved as three fields in the database!
     */
    public static function transcribeCaptchaOptions() {
        $surveyaccess = App()->request->getPost('usecaptcha_surveyaccess');
        $registration = App()->request->getPost('usecaptcha_registration');
        $saveandload = App()->request->getPost('usecaptcha_saveandload');

        if ($surveyaccess && $registration && $saveandload)
        {
            return 'A';
        }
        elseif ($surveyaccess && $registration)
        {
            return 'B';
        }
        elseif ($surveyaccess && $saveandload)
        {
            return 'C';
        }
        elseif ($registration && $saveandload)
        {
            return 'D';
        }
        elseif ($surveyaccess)
        {
            return 'X';
        }
        elseif ($registration)
        {
            return 'R';
        }
        elseif ($saveandload)
        {
            return 'S';
        }

        return 'N';
    }

    /**
    * Method to make an approximation on how long a survey will last
    * Approx is 3 questions each minute.
    * @return double
    */
    public function calculateEstimatedTime ()
    {
        //@TODO make the time_per_question variable user configureable
        $time_per_question = 0.5;
        $criteria = new CDbCriteria();
        $criteria->addCondition('sid = ' . $this->sid);
        $criteria->addCondition('parent_qid = 0');
        $criteria->addCondition('language = \'' . $this->language . '\'');
        $baseQuestions = Question::model()->count($criteria);
        // Note: An array questions with one sub question is fetched as 1 base question + 1 sub question
        $criteria = new CDbCriteria();
        $criteria->addCondition('sid = ' . $this->sid);
        $criteria->addCondition('parent_qid != 0');
        $criteria->addCondition('language = \'' . $this->language . '\'');
        $subQuestions = Question::model()->count($criteria);
        // Subquestions are worth less "time" than base questions
        $subQuestions = intval(($subQuestions - $baseQuestions) / 2);
        $subQuestions = $subQuestions < 0 ? 0 : $subQuestions;
        return ceil(($subQuestions + $baseQuestions)*$time_per_question);
    }

    /**
     * Get all surveys that has participant table
     * @return Survey[]
     */
    public static function getSurveysWithTokenTable()
    {
        $surveys = self::model()->with(array('languagesettings'=>array('condition'=>'surveyls_language=language'), 'owner'))->findAll();
        $surveys = array_filter($surveys, function($s) { return tableExists('{{tokens_' . $s->sid); });
        return $surveys;
    }
}
