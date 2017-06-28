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

use \ls\pluginmanager\PluginEvent;

/**
 * Class Survey
 *
 * @property integer $sid primary key
 * @property integer $owner_id
 * @property string $admin
 * @property string $active
 * @property string $expires Expiry date
 * @property string $startdate
 * @property string $adminemail
 * @property string $anonymized
 * @property string $faxto
 * @property string $savetimings
 * @property string $template Template name
 * @property string $language
 * @property string $additional_languages
 * @property string $datestamp
 * @property string $usecookie
 * @property string $allowsave
 * @property integer $autonumber_start
 * @property integer $tokenlength
 * @property string $autoredirect
 * @property string $allowprev
 * @property string $printanswers
 * @property string $ipaddr
 * @property string $refurl
 * @property string $datecreated
 * @property string $publicstatistics
 * @property string $publicgraphs
 * @property string $listpublic
 * @property string $sendconfirmation
 * @property string $tokenanswerspersistence
 * @property string $assessments
 * @property string $usecaptcha
 * @property string $usetokens
 * @property string $bounce_email
 * @property string $attributedescriptions
 * @property string $emailresponseto
 * @property integer $emailnotificationto
 * @property string $showxquestions
 * @property string $showgroupinfo
 * @property string $shownoanswer
 * @property string $showqnumcode
 * @property integer $bouncetime
 * @property string $bounceprocessing
 * @property string $bounceaccounttype
 * @property string $bounceaccounthost
 * @property string $bounceaccountpass
 * @property string $bounceaccountencryption
 * @property string $bounceaccountuser
 * @property string $showwelcome
 * @property string $showprogress
 * @property integer $questionindex
 * @property integer $navigationdelay
 * @property string $nokeyboard
 * @property string $alloweditaftercompletion
 * @property string $googleanalyticsstyle
 * @property string $googleanalyticsapikey
 *
 * @property Permission[] $permissions
 * @property SurveyLanguageSetting[] $languagesettings
 * @property User $owner
 * @property QuestionGroup[] $groups
 * @property Quota[] $quotas
 * @property Question[] $quotableQuestions
 *
 * @property array $fullAnswers
 * @property array $partialAnswers
 * @property integer $countFullAnswers
 * @property integer $countPartialAnswers
 * @property integer $countTotalAnswers
 * @property array $surveyinfo
 * @property string creationDate Creation date formatted according to user format
 * @property string startDateFormatted Start date formatted according to user format
 * @property string expiryDateFormatted Expiry date formatted according to user format
 */
class Survey extends LSActiveRecord
{
    /**
     * This is a static cache, it lasts only during the active request. If you ever need
     * to clear it, like on activation of a survey when in the same request a row is read,
     * saved and read again you can use resetCache() method.
     *
     * @var array $findByPkCache
     */
    protected $findByPkCache = array();

    /** @var string  A : All in one, G : Group by group, Q : question by question */
    public $format = 'G';
    /**
     * @property string $htmlemail Y mean all email related to this survey use HTML format
     */
    public $htmlemail='Y';

    // TODO unused??
    /** @var null $full_answers_account */
    public $full_answers_account=null;

    // TODO unused??
    public $partial_answers_account=null;
    /** @var string $searched_value */
    public $searched_value;

    /** @var integer $fac Full-answers count*/
    private $fac;
    /** @var integer $pac Partial-answers count*/
    private $pac;

    private $sSurveyUrl;

    /**
     * Set defaults
     * @inheritdoc
     */
    public function init()
    {
        /** @inheritdoc */
        $this->template = Template::templateNameFilter(Yii::app()->getConfig('defaulttemplate'));
        $validator= new LSYii_Validators;
        $this->language = $validator->languageFilter(Yii::app()->getConfig('defaultlang'));
        $this->attachEventHandler("onAfterFind", array($this,'fixSurveyAttribute'));
    }

    /** @inheritdoc */
    public function attributeLabels() {
        return array(
            'running' => gT('running')
        );
    }

    /**
     * Returns the title of the survey. Uses the current language and
     * falls back to the surveys' default language if the current language is not available.
     * @return string
     */
    public function getLocalizedTitle()
    {
        if (isset($this->languagesettings[App()->language])) {
            return $this->languagesettings[App()->language]->surveyls_title;
        } else {
            return $this->languagesettings[$this->language]->surveyls_title;
        }
    }

    /**
     * Expires a survey. If the object was invoked using find or new surveyId can be ommited.
     * @param int $surveyId
     * @return bool
     */
    public function expire($surveyId = null)
    {
        $dateTime = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig('timeadjust'));
        $dateTime = dateShift($dateTime, "Y-m-d H:i:s", '-1 minute');

        if (!isset($surveyId)) {
            $this->expires = $dateTime;
            if ($this->scenario == 'update') {
                return $this->save();
            }
        } else {
            self::model()->updateByPk($surveyId,array('expires' => $dateTime));
        }

    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{surveys}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'sid';
    }

    /**
    * @inheritdoc
    * @return Survey
    */
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
    }

    /** @inheritdoc */
    public function relations()
    {
        return array(
            'permissions'     => array(self::HAS_MANY, 'Permission', array( 'entity_id'=> 'sid'  ), 'together' => true ), //
            'languagesettings' => array(self::HAS_MANY, 'SurveyLanguageSetting', 'surveyls_survey_id', 'index' => 'surveyls_language', 'together' => true),
            'defaultlanguage' => array(self::BELONGS_TO, 'SurveyLanguageSetting', array('language' => 'surveyls_language', 'sid' => 'surveyls_survey_id'), 'together' => true),
            'correct_relation_defaultlanguage' => array(self::HAS_ONE, 'SurveyLanguageSetting', array('surveyls_language' => 'language', 'surveyls_survey_id' => 'sid'), 'together' => true),
            'owner' => array(self::BELONGS_TO, 'User', 'owner_id', 'together' => true),
            'groups' => array(self::HAS_MANY, 'QuestionGroup', 'sid', 'together' => true),
            'quotas' => array(self::HAS_MANY, 'Quota', 'sid','order'=>'name ASC'),
            'surveymenus' => array(self::HAS_MANY, 'Surveymenu', array('survey_id' => 'sid')),
        );
    }

    /** @inheritdoc */
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

    /** @inheritdoc */
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
     * //FIXME $event input parameter is overridden always remove from implementations
    */
    public function fixSurveyAttribute($event)
    {
        $event = new PluginEvent('afterFindSurvey');
        $event->set('surveyid',$this->sid);
        App()->getPluginManager()->dispatchEvent($event);
        // set the attributes we allow to be fixed
        $allowedAttributes = array( 'template','usecookie', 'allowprev',
            'showxquestions', 'shownoanswer', 'showprogress', 'questionindex',
            'usecaptcha', 'showgroupinfo', 'showqnumcode', 'navigationdelay');
        foreach ($allowedAttributes as $attribute) {
            if (!is_null($event->get($attribute))) {
                $this->{$attribute} = $event->get($attribute);
            }
        }
        $this->template=Template::templateNameFilter($this->template);
    }


    /**
     * filterTemplateSave to fix some template name
     * @param string $sTemplateName
     * @return string
     */
    public function filterTemplateSave($sTemplateName)
    {
        if(!Permission::model()->hasTemplatePermission($sTemplateName)) {
            // Reset to default only if different from actual value
            if(!$this->isNewRecord){
                $oSurvey=self::model()->findByPk($this->sid);
                if($oSurvey->template != $sTemplateName)// No need to test !is_null($oSurvey)
                    $sTemplateName = Yii::app()->getConfig('defaulttemplate');
            } else {
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
        array_unshift($sLanguages,$this->language);
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
        if($attdescriptiondata == null) {
            return array();
        }

        // Catches malformed data
        if ($attdescriptiondata && strpos(key(reset($attdescriptiondata)),'attribute_')===false) {
            // don't know why yet but this breaks normal tokenAttributes functionning
            //$attdescriptiondata=array_flip(GetAttributeFieldNames($this->sid));
        }
        elseif (is_null($attdescriptiondata)) {
            $attdescriptiondata=array();
        }
        // Legacy records support
        if ($attdescriptiondata === false) {
            $attdescriptiondata = explode("\n", $this->attributedescriptions);
            $fields = array();
            $languagesettings = array();
            foreach ($attdescriptiondata as $attdescription) {
                if (trim($attdescription) != '') {
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
        foreach ($attdescriptiondata as $sKey=>$aValues) {
            if (!is_array($aValues)) $aValues=array();
            if(preg_match("/^attribute_[0-9]{1,}$/",$sKey)) {
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

    /**
     * @return string
     */
    public function getHasTokens()
    {
        $hasTokens = $this->hasTokens($this->sid) ;
        if($hasTokens) {
            return gT('Yes');
        } else {
            return gT('No');
        }
    }

     /**
     * Returns the value for the SurveyEdit GoogleAnalytics API-Key UseGlobal Setting
     * @return string
     */
    public function getGoogleanalyticsapikeysetting(){
        if($this->googleanalyticsapikey === "9999useGlobal9999") {
            return "G";
        } else if($this->googleanalyticsapikey == "") {
            return "N";
        } else {
            return "Y";
        }
    }

    /**
     * @param string $value
     */
    public function setGoogleanalyticsapikeysetting($value){
        if($value == "G") {
            $this->googleanalyticsapikey = "9999useGlobal9999";
        } else if($value == "N") {
           $this->googleanalyticsapikey = "";
        }
    }

     /**
     * Returns the value for the SurveyEdit GoogleAnalytics API-Key UseGlobal Setting
      * @return string
     */
    public function getGoogleanalyticsapikey(){
        if($this->googleanalyticsapikey === "9999useGlobal9999") {
            return getGlobalSetting('googleanalyticsapikey');
        } else {
            return $this->googleanalyticsapikey;
        }
    }


    private function _getDefaultSurveyMenu(){
        $oDefaultMenu = Surveymenu::model()->findByPk(1);
        //Posibility to add more languages to the database is given, so it is possible to add a call by language
        //Also for peripheral menues we may add submenus someday.
        $defaultMenuEntries = $oDefaultMenu->surveymenuEntries;
        $aResult = [
            "title" => $oDefaultMenu->title,
            "description" => $oDefaultMenu->description,
            "entries" => [
                [
                    'id'=> "0",
                    'link'=> App()->getController()->createUrl("admin/survey/sa/view",['surveyid' => $this->sid]),
                    'menu_class'=> "",
                    'menu_description'=> "Survey overwiew",
                    'menu_icon'=> "list",
                    'menu_icon_type'=> "fontawesome",
                    'menu_id'=> "1",
                    'menu_title'=> "Overview",
                    'name'=> "overview",
                    'title'=> "General overview",
                ]
            ]
        ];
        foreach($defaultMenuEntries as $menuEntry){
            $aEntry = $menuEntry->attributes;
            $aEntry['link'] = App()->getController()->createUrl("admin/survey/sa/rendersidemenulink",['surveyid' => $this->sid, 'subaction' => $aEntry['name'] ]);
            $aResult["entries"][] = $aEntry;
        }  
        return $aResult;
    }   


    /**
     * Get surveymenu configuration
     * This will be made bigger in future releases, but right now it only collects the default menu-entries
     */
    public function getSurveyMenus(){
        
        $aSurveyMenus = [];

        //Get the default menu
        $aSurveyMenus[] = $this->_getDefaultSurveyMenu();

        //get all survey specific menus
        foreach($this->surveymenus as $menu){
            $aMenuResult = [
                "title" => $menu->title,
                "description" => $menu->description,
                "entries" => []
            ];
            
            foreach($menu->surveymenuEntries as $menuEntry){
                $aEntry = $menuEntry->attributes;
                $aEntry['link'] = App()->getController()->createUrl("admin/survey/sa/rendersidemenulink",['surveyid' => $this->sid, 'subaction' => $aEntry['name'] ]);
                $aMenuResult["entries"][] = $aEntry;
            }
            $aSurveyMenus[] = $aMenuResult;
        }

        //soon to come => Event to add menus for plugins

        return $aSurveyMenus;
    }

    /**
    * Creates a new survey - does some basic checks of the suppplied data
    *
    * @param array $aData Array with fieldname=>fieldcontents data
    * @return integer The new survey id
    */
    public function insertNewSurvey($aData)
    {
        do {
            // if wishSID is set check if it is not taken already
            if (isset($aData['wishSID'])) {
                $aData['sid'] = $aData['wishSID'];
                unset($aData['wishSID']);
            }
            else{
                $aData['sid'] = randomChars(6, '123456789');
            }

            $isresult = self::model()->findByPk($aData['sid']);
        }
        while (!is_null($isresult));

        $survey = new self;
        foreach ($aData as $k => $v)
            $survey->$k = $v;
        $sResult= $survey->save();

        if (!$sResult) {
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

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'survey', 'delete')) {
            if ( Survey::model()->deleteByPk($iSurveyID) ) {
                if ($recursive == true) {
                    //delete the survey_$iSurveyID table
                    if (tableExists("{{survey_".intval($iSurveyID)."}}")) {
                        Yii::app()->db->createCommand()->dropTable("{{survey_".intval($iSurveyID)."}}");
                    }
                    //delete the survey_$iSurveyID_timings table
                    if (tableExists("{{survey_".intval($iSurveyID)."_timings}}")) {
                        Yii::app()->db->createCommand()->dropTable("{{survey_".intval($iSurveyID)."_timings}}");
                    }
                    //delete the tokens_$iSurveyID table
                    if (tableExists("{{tokens_".intval($iSurveyID)."}}")) {
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
                    // pgsql need casting, unsure for mssql
                    if(Yii::app()->db->getDriverName() == 'pgsql') {
                        $oCriteria->addInCondition('CAST(stg_value as '.App()->db->schema->getColumnType("integer").')',$aGroupId);
                    }
                    //mysql App()->db->schema->getColumnType("integer") give int(11), mssql seems to have issue if cast alpha to numeric
                    else {
                        $oCriteria->addInCondition('stg_value',$aGroupId);
                    }
                    SettingGlobal::model()->deleteAll($oCriteria);
                    // All Question id from this survey for ALL users
                    $aQuestionId=CHtml::listData(Question::model()->findAll(array('select'=>'qid','condition'=>'sid=:sid','params'=>array(':sid'=>$iSurveyID))),'qid','qid');
                    $oCriteria = new CDbCriteria();
                    $oCriteria->compare('stg_name','last_question_%',true,'OR',false);
                    if(Yii::app()->db->getDriverName() == 'pgsql') {
                        $oCriteria->addInCondition('CAST(stg_value as '.App()->db->schema->getColumnType("integer").')',$aQuestionId);
                    } else {
                        $oCriteria->addInCondition('stg_value',$aQuestionId);
                    }
                    SettingGlobal::model()->deleteAll($oCriteria);

                    $oResult = Question::model()->findAllByAttributes(array('sid' => $iSurveyID));
                    foreach ($oResult as $aRow) {
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

    /** @inheritdoc */
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
     * @param string $value
     */
    public function setAllowjumps($value)
    {
        if ($value === 'Y') {
            $this->questionindex = 1;
        } else {
            $this->questionindex = 0;
        }
    }

    /**
     * @return array
     */
    public function getSurveyinfo()
    {
        $iSurveyID = $this->sid;

        //// TODO : replace this with a HAS MANY relation !
        $sumresult1 = Survey::model()->with(
            array(
                'languagesettings' => array(
                    'condition' => 'surveyls_language = language'
                )
            ))->find(
                'sid = :surveyid',
                array(':surveyid' => $iSurveyID)
            ); //$sumquery1, 1) ; //Checked
        if (is_null($sumresult1))
        {
            Yii::app()->session['flashmessage'] = gT("Invalid survey ID");
            Yii::app()->getController()->redirect(array("admin/index"));
        } //  if surveyid is invalid then die to prevent errors at a later time

        $surveyinfo = $sumresult1->attributes;
        $surveyinfo = array_merge($surveyinfo, $sumresult1->defaultlanguage->attributes);
        $surveyinfo = array_map('flattenText', $surveyinfo);
        //$surveyinfo["groups"] = $this->groups;
        return $surveyinfo;
    }


    /**
     * @param string $attribute date attribute name
     * @return string formatted date
     */
    private function getDateFormatted($attribute)
    {
        $dateformatdata=getDateFormatData(Yii::app()->session['dateformat']);
        if($this->$attribute){
            return convertDateTimeFormat($this->$attribute, 'Y-m-d', $dateformatdata['phpdate']);
        }
        return null;
    }


    /**
     * @return string formatted date
     */
    public function getCreationDate()
    {
        return $this->getDateFormatted('datecreated');
    }


    /**
     * @return string formatted date
     */
    public function getStartDateFormatted()
    {
        return $this->getDateFormatted('startdate');
    }


    /**
     * @return string formatted date
     */
    public function getExpiryDateFormatted()
    {
        return $this->getDateFormatted('expires');
    }


    /**
     * @return string
     */
    public function getAnonymizedResponses()
    {
        return ($this->anonymized == 'Y') ? gT('Yes') : gT('No');
    }

    /**
     * @return string
     */
    public function getActiveWord()
    {
        return ($this->active == 'Y') ? gT('Yes') : gT('No');
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
        if($this->active == 'N') {
            return 'inactive';
        } elseif ($this->expires != '' || $this->startdate != '') {
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

            if ($bExpired) {
                return 'expired';
            } elseif ($bWillRun) {
                return 'willRun';
            } else {
                return 'willExpire';
            }
        }
        // If it's active, and doesn't have expire date, it's running
        else {
            return 'running';
        }
    }


    /**
     * @todo Document code, please.
     * @return string
     */
    public function getRunning()
    {

        // If the survey is not active, no date test is needed
        if($this->active == 'N') {
            $running = '<a href="'.App()->createUrl('/admin/survey/sa/view/surveyid/'.$this->sid).'" class="survey-state" data-toggle="tooltip" title="'.gT('Inactive').'"><span class="fa fa-stop text-warning"></span></a>';
        }
        // If it's active, then we check if not expired
        elseif ($this->expires != '' || $this->startdate != '') {
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
            $sIconRunning = '<a href="'.App()->createUrl('/admin/survey/sa/view/surveyid/'.$this->sid).'" class="survey-state" data-toggle="tooltip" title="'.sprintf(gT('End: %s'),$sStop).'"><span class="fa  fa-play text-success"></span><span class="sr-only">'.sprintf(gT('End: %s'),$sStop).'</span></a>';
            $sIconExpired = '<a href="'.App()->createUrl('/admin/survey/sa/view/surveyid/'.$this->sid).'" class="survey-state" data-toggle="tooltip" title="'.sprintf(gT('Expired: %s'),$sStop).'"><span class="fa fa fa-step-forward text-warning"></span><span class="sr-only">'.sprintf(gT('Expired: %s'),$sStop).'</span></a>';
            $sIconFuture  = '<a href="'.App()->createUrl('/admin/survey/sa/view/surveyid/'.$this->sid).'" class="survey-state" data-toggle="tooltip" title="'.sprintf(gT('Start: %s'),$sStart).'"><span class="fa  fa-clock-o text-warning"></span><span class="sr-only">'.sprintf(gT('Start: %s'),$sStart).'</span></a>';
 
            // Icon parsing
            if ( $bExpired || $bWillRun ) {
                // Expire prior to will start
                $running = ($bExpired)?$sIconExpired:$sIconFuture;
            } else {
                $running = $sIconRunning;
            }
        }
        // If it's active, and doesn't have expire date, it's running
        else {
            $running = '<a href="'.App()->createUrl('/admin/survey/sa/view/surveyid/'.$this->sid).'" class="survey-state" data-toggle="tooltip" title="'.gT('Active').'"><span class="fa fa-play text-success"></span></a>';
            //$running = '<div class="survey-state"><span class="fa fa-play text-success"></span></div>';
        }

        return $running;

    }

    /**
     * @return array|null
     */
    public function getPartialAnswers()
    {
        $table = '{{survey_' . $this->sid . '}}';
        Yii::app()->cache->flush();
        if (!Yii::app()->db->schema->getTable($table)) {
            return null;
        } else {
            $answers = Yii::app()->db->createCommand()
                ->select('*')
                ->from($table)
                ->where('submitdate IS NULL')
                ->queryAll();

            return $answers;
        }
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return ($this->active === 'Y');
    }

    /**
     * @return array|null
     */
    public function getFullAnswers()
    {
        $table = '{{survey_' . $this->sid . '}}';
        Yii::app()->cache->flush();
        if (!Yii::app()->db->schema->getTable($table)) {
            return null;
        } else {
            $answers = Yii::app()->db->createCommand()
                ->select('*')
                ->from($table)
                ->where('submitdate IS NOT NULL')
                ->queryAll();

            return $answers;
        }
    }

    /**
     * @return int|string
     */
    public function getCountFullAnswers()
    {
        if($this->fac!==null) {
            return $this->fac;
        } else {
            $sResponseTable = '{{survey_' . $this->sid . '}}';
            Yii::app()->cache->flush();
            if ($this->active!='Y') {
                $this->fac = 0;
                // TODO Why string?
                return '0';
            } else {
                $answers = Yii::app()->db->createCommand('select count(*) from '.$sResponseTable.' where submitdate IS NOT NULL')->queryScalar();
                $this->fac = $answers;
                return $answers;
            }
        }
    }

    /**
     * @return int
     */
    public function getCountPartialAnswers()
    {
        if($this->pac!==null) {
            return $this->pac;
        } else {
            $table = '{{survey_' . $this->sid . '}}';
            Yii::app()->cache->flush();
            if ($this->active!='Y') {
                $this->pac = 0;
                return 0;
            } else {
                $answers = Yii::app()->db->createCommand('select count(*) from '.$table.' where submitdate IS NULL')->queryScalar();
                $this->pac = $answers;
                return $answers;
            }
        }
    }

    /**
     * @return int
     */
    public function getCountTotalAnswers()
    {
        // TODO why we have pac & fac & then countFullAnswers etc? same thing!
        if ($this->pac!==null && $this->fac!==null) {
            return ($this->pac + $this->fac);
        } else {
            return ($this->countFullAnswers + $this->countPartialAnswers);
        }
    }

    /**
     * @return string
     */
    public function getbuttons()
    {
        $sEditUrl     = App()->createUrl("/admin/survey/sa/editlocalsettings/surveyid/".$this->sid);
        $sStatUrl     = App()->createUrl("/admin/statistics/sa/simpleStatistics/surveyid/".$this->sid);
        $sAddGroup    = App()->createUrl("/admin/questiongroups/sa/add/surveyid/".$this->sid);;
        $sAddquestion = App()->createUrl("/admin/questions/sa/newquestion/surveyid/".$this->sid);;

        $button = '';

        if (Permission::model()->hasSurveyPermission($this->sid, 'survey', 'update')) {
            $button .= '<a class="btn btn-default" href="'.$sEditUrl.'" role="button" data-toggle="tooltip" title="'.gT('General settings & texts').'"><span class="glyphicon glyphicon-cog" ></span><span class="sr-only">'.gT('General settings & texts').'</span></a>';
        }

        if(Permission::model()->hasSurveyPermission($this->sid, 'statistics', 'read') && $this->active=='Y' ) {
            $button .= '<a class="btn btn-default" href="'.$sStatUrl.'" role="button" data-toggle="tooltip" title="'.gT('Statistics').'"><span class="glyphicon glyphicon-stats text-success" ></span><span class="sr-only">'.gT('Statistics').'</span></a>';
        }

        if (Permission::model()->hasSurveyPermission($this->sid, 'survey', 'create')) {
            if($this->active!='Y') {
                $groupCount = QuestionGroup::model()->countByAttributes(array('sid' => $this->sid, 'language' => $this->language)); //Checked
                if($groupCount > 0) {
                    $button .= '<a class="btn btn-default" href="'.$sAddquestion.'" role="button" data-toggle="tooltip" title="'.gT('Add new question').'"><span class="icon-add text-success" ></span><span class="sr-only">'.gT('Add new question').'</span></a>';
                } else {
                    $button .= '<a class="btn btn-default" href="'.$sAddGroup.'" role="button" data-toggle="tooltip" title="'.gT('Add new group').'"><span class="icon-add text-success" ></span><span class="sr-only">'.gT('Add new group').'</span></a>';
                }
            }
        }

        //$previewUrl = Yii::app()->createUrl("survey/index/sid/");
        //$previewUrl .= '/'.$this->sid;
        //$button = '<a class="btn btn-default open-preview" aria-data-url="'.$previewUrl.'" aria-data-language="'.$this->language.'" href="# role="button" ><span class="glyphicon glyphicon-eye-open"  ></span></a> ';

        return $button;
    }

    /**
     * @return CActiveDataProvider
     */
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
        if(isset($this->active)) {
            if($this->active == 'N' || $this->active == "Y") {
                $criteria->compare("t.active", $this->active, false);
            } else {
                // Time adjust
                $sNow = date("Y-m-d H:i:s", strtotime(Yii::app()->getConfig('timeadjust'), strtotime(date("Y-m-d H:i:s"))) );

                if($this->active == "E") {
                    $criteria->compare("t.active",'Y');
                    $criteria->addCondition("t.expires <'$sNow'");
                } if($this->active == "S") {
                    $criteria->compare("t.active",'Y');
                    $criteria->addCondition("t.startdate >'$sNow'");
                }

                if($this->active == "R")
                {
                    $criteria->compare("t.active",'Y');
                    $subCriteria1 = new CDbCriteria;
                    $subCriteria2 = new CDbCriteria;
                    $subCriteria1->addCondition("'{$sNow}' > t.startdate", 'OR');
                    $subCriteria2->addCondition("'{$sNow}' < t.expires", 'OR');
                    $subCriteria1->addCondition('t.expires IS NULL', "OR");
                    $subCriteria1->addCondition("'{$sNow}' < t.expires", 'OR');
                    $subCriteria2->addCondition('t.startdate IS NULL', "OR");
                    $criteria->mergeWith($subCriteria1);
                    $criteria->mergeWith($subCriteria2);
                }
            }
        }


        $criteria->with=$aWithRelations;

        // Permission
        // Note: reflect Permission::hasPermission
        if(!Permission::model()->hasGlobalPermission("surveys",'read')) {
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

        if ($surveyaccess && $registration && $saveandload) {
            return 'A';
        } elseif ($surveyaccess && $registration) {
            return 'B';
        } elseif ($surveyaccess && $saveandload) {
            return 'C';
        } elseif ($registration && $saveandload) {
            return 'D';
        } elseif ($surveyaccess) {
            return 'X';
        } elseif ($registration) {
            return 'R';
        } elseif ($saveandload) {
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
        return ceil(($subQuestions + $baseQuestions) * $time_per_question);
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

    /**
     * Fix invalid question in this survey
     */
    public function fixInvalidQuestions()
    {
        /* Delete invalid questions (don't exist in primary language) using qid like column name*/
        $validQuestion = Question::model()->findAll(array(
            'select'=>'qid',
            'condition'=>'sid=:sid AND language=:language AND parent_qid = 0',
            'params'=>array('sid' => $this->sid,'language' => $this->language)
        ));
        $criteria = new CDbCriteria;
        $criteria->compare('sid',$this->sid);
        $criteria->addCondition('parent_qid = 0');
        $criteria->addNotInCondition('qid', CHtml::listData($validQuestion,'qid','qid'));
        Question::model()->deleteAll($criteria);// Must log count of deleted ?

        /* Delete invalid Sub questions (don't exist in primary language) using title like column name*/
        $validSubQuestion = Question::model()->findAll(array(
            'select'=>'title',
            'condition'=>'sid=:sid AND language=:language AND parent_qid != 0',
            'params'=>array('sid' => $this->sid,'language' => $this->language)
        ));
        $criteria = new CDbCriteria;
        $criteria->compare('sid',$this->sid);
        $criteria->addCondition('parent_qid != 0');
        $criteria->addNotInCondition('title', CHtml::listData($validSubQuestion,'title','title'));
        Question::model()->deleteAll($criteria);// Must log count of deleted ?
    }

    public function getsSurveyUrl()
    {
        if ($this->sSurveyUrl==''){
            if(!in_array(App()->language,$this->getAllLanguages())){
                $surveylang=$this->language;
            }else{
                $surveylang=App()->language;
            }
            $this->sSurveyUrl = App()->createUrl('survey/index', array('sid' => $this->sid, 'lang' => $surveylang));
        }
        return $this->sSurveyUrl;
    }


    /**
     * @return Question[]
     */
    public function getQuotableQuestions()
    {
        $criteria = $this->getQuestionOrderCriteria();

        $criteria->addColumnCondition(array(
            't.sid' => $this->sid,
            't.language' => $this->language,
            'parent_qid' => 0,

        ));

        $criteria->addInCondition('t.type',Question::getQuotableTypes());

        /** @var Question[] $questions */
        $questions = Question::model()->findAll($criteria);
        return $questions;
    }

    /**
     * Get the DB criteria to get questions as ordered in survey
     * @return CDbCriteria
     */
    private function getQuestionOrderCriteria(){
        $criteria=new CDbCriteria;
        $criteria->select = Yii::app()->db->quoteColumnName('t.*');
        $criteria->with=array(
            'survey.groups',
        );
        $criteria->order =Yii::app()->db->quoteColumnName('groups.group_order').','
            .Yii::app()->db->quoteColumnName('t.question_order');
        $criteria->addCondition('`groups`.`gid` =`t`.`gid`','AND');
        return $criteria;

    }

}
