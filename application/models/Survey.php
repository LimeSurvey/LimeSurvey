<?php

if (!defined('BASEPATH')) {
    die('No direct script access allowed');
}
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

use \LimeSurvey\PluginManager\PluginEvent;

/**
 * Class Survey
 *
 * @property integer $sid Survey ID
 * @property integer $owner_id
 * @property integer $gsid Survey ID
 * @property string $admin Survey Admin's full name
 * @property string $active Whether survey is acive or not (Y/N)
 * @property string $expires Expiry date (YYYY-MM-DD hh:mm:ss)
 * @property string $startdate Survey Start date (YYYY-MM-DD hh:mm:ss)
 * @property string $adminemail Survey administrator email address
 * @property string $anonymized Whether survey is anonymized or not (Y/N)
 * @property string $faxto
 * @property string $format A : All in one, G : Group by group, Q : question by question
 * @property string $savetimings Whether survey timings are saved (Y/N)
 * @property string $template Template name
 * @property string $language Survey base language
 * @property string $additional_languages Survey additional languages delimited by space ' '
 * @property string $datestamp Whether respondents' datestamps will be saved (Y/N)
 * @property string $usecookie Are cookies used to prevent repeated participation (Y/N)
 * @property string $allowregister Allow public registration (Y/N)
 * @property string $allowsave Is participant allowed save and resume later (Y/N)
 * @property integer $autonumber_start
 * @property integer $tokenlength Token length: MIN:5 MAX:36
 * @property string $autoredirect Automatically load URL when survey complete: (Y/N)
 * @property string $allowprev Allow backwards navigation (Y/N)
 * @property string $printanswers Participants may print answers: (Y/N)
 * @property string $ipaddr Whether Participants IP address will be saved: (Y/N)
 * @property string $refurl Save referrer URL: (Y/N)
 * @property string $datecreated Date survey was created (YYYY-MM-DD hh:mm:ss)
 * @property string $publicstatistics Public statistics: (Y/N)
 * @property string $publicgraphs Show graphs in public statistics: (Y/N)
 * @property string $listpublic List survey publicly: (Y/N)
 * @property string $htmlemail Use HTML format for token emails: (Y/N)
 * @property string $sendconfirmation Send confirmation emails:(Y/N)
 * @property string $tokenanswerspersistence Enable token-based response persistence: (Y/N)
 * @property string $assessments Enable assessment mode: (Y/N)
 * @property string $usecaptcha
 * @property string $usetokens
 * @property string $bounce_email Bounce email address
 * @property string $attributedescriptions
 * @property string $emailresponseto e-mail address to send detailed admin notification email to
 * @property string $emailnotificationto Email address to send basic admin notification email to
 * @property string $showxquestions Show "There are X questions in this survey": (Y/N)
 * @property string $showgroupinfo Show group name and/or group description: (Y/N)
 * @property string $shownoanswer Show "No answer": (Y/N)
 * @property string $showqnumcode Show question number and/or code: (Y/N)
 * @property integer $bouncetime
 * @property string $bounceprocessing
 * @property string $bounceaccounttype
 * @property string $bounceaccounthost
 * @property string $bounceaccountpass
 * @property string $bounceaccountencryption
 * @property string $bounceaccountuser
 * @property string $showwelcome Show welcome screen: (Y/N)
 * @property string $showprogress how progress bar: (Y/N)
 * @property integer $questionindex Show question index / allow jumping (0: diabled; 1: Incremental; 2: Full)
 * @property integer $navigationdelay Navigation delay (seconds)
 * @property string $nokeyboard Show on-screen keyboard: (Y/N)
 * @property string $alloweditaftercompletion Allow multiple responses or update responses with one token: (Y/N)
 * @property string $googleanalyticsstyle Google Analytics style: (0: off; 1:Default; 2:Survey-SID/Group)
 * @property string $googleanalyticsapikey Google Analytics Tracking ID
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
 * @property integer $countFullAnswers Full-answers count
 * @property integer $countPartialAnswers Full-answers count
 * @property integer $countTotalAnswers Total-answers count
 * @property integer $groupsCount Number of groups in a survey (in base language)
 * @property array $surveyinfo
 * @property SurveyLanguageSetting $currentLanguageSettings Survey languagesettings in currently active language
 * @property string[] $allLanguages
 * @property string[] $additionalLanguages Additional survey languages
 * @property array $tokenAttributes Additional token attribute names
 * @property string $creationDate Creation date formatted according to user format
 * @property string $startDateFormatted Start date formatted according to user format
 * @property string $expiryDateFormatted Expiry date formatted according to user format
 * @property string $tokensTableName Name of survey tokens table
 * @property string $responsesTableName Name of survey resonses table
 * @property string $timingsTableName Name of survey timings table
 * @property boolean $hasTokensTable Whether survey has a tokens table or not
 * @property boolean $hasResponsesTable Wheteher the survey reponses (data) table exists in DB
 * @property boolean $hasTimingsTable Wheteher the survey timings table exists in DB
 * @property string $googleanalyticsapikeysetting Returns the value for the SurveyEdit GoogleAnalytics API-Key UseGlobal Setting
 * @property integer $countTotalQuestions Count of questions (in that language, without subquestions)
 * @property integer $countInputQuestions Count of questions that need input (skipping text-display etc.)
 * @property integer $countNoInputQuestions Count of questions that DO NOT need input (skipping text-display etc.)
 *
 * All Y/N columns in the model can be accessed as boolean values:
 * @property bool $isActive Whether Survey is active
 * @property bool $isAnonymized Whether survey is anonymized or not
 * @property bool $isSaveTimings Whether survey timings are saved
 * @property bool $isDateStamp Whether respondents' datestamps will be saved
 * @property bool $isUseCookie Are cookies used to prevent repeated participation
 * @property bool $isAllowRegister Allow public registration
 * @property bool $isAllowSave Is participant allowed save and resume later
 * @property bool $isAutoRedirect Automatically load URL when survey complete
 * @property bool $isAllowPrev Allow backwards navigation
 * @property bool $isPrintAnswers Participants may print answers
 * @property bool $isIpAddr Whether Participants IP address will be saved
 * @property bool $isRefUrl Save referrer URL
 * @property bool $isPublicStatistics Public statistics
 * @property bool $isPublicGraphs Show graphs in public statistics
 * @property bool $isListPublic List survey publicly
 * @property bool $isHtmlEmail Use HTML format for token emails
 * @property bool $isSendConfirmation Send confirmation emails
 * @property bool $isTokenAnswersPersistence Enable token-based response persistence
 * @property bool $isAssessments Enable assessment mode
 * @property bool $isShowXQuestions Show "There are X questions in this survey"
 * @property bool $isShowGroupInfo Show group name and/or group description
 * @property bool $isShowNoAnswer Show "No answer"
 * @property bool $isShowQnumCode Show question number and/or code
 * @property bool $isShowWelcome Show welcome screen
 * @property bool $isShowProgress how progress bar
 * @property bool $showsurveypolicynotice Show the security notice
 * @property bool $isNoKeyboard Show on-screen keyboard
 * @property bool $isAllowEditAfterCompletion Allow multiple responses or update responses with one token
 * @property SurveyLanguageSetting $defaultlanguage
 * @method mixed active()
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



    public $searched_value;

    public $showsurveypolicynotice = 0;


    private $sSurveyUrl;

    /**
     * Set defaults
     * @inheritdoc
     */
    public function init()
    {
        /** @inheritdoc */

        // Set the default values
        $this->htmlemail = 'Y';
        $this->format = 'G';

        // Default setting is to use the global Google Analytics key If one exists
        $globalKey = App()->getConfig('googleanalyticsapikey');
        if ($globalKey != "") {
            $this->googleanalyticsapikey = "9999useGlobal9999";
            $this->googleanalyticsapikeysetting = "G";
        }
        /* default template */
        $this->template = Template::templateNameFilter(App()->getConfig('defaulttheme'));
        /* default language */
        $validator = new LSYii_Validators;
        $this->language = $validator->languageFilter(App()->getConfig('defaultlang'));
        /* default user */
        $this->owner_id = 1;
        $this->admin = App()->getConfig('siteadminname');
        $this->adminemail = App()->getConfig('siteadminemail');
        if(!(Yii::app() instanceof CConsoleApplication)) {
            $iUserid = Permission::getUserId();
            if($iUserid) {
                $this->owner_id = $iUserid;
                $oUser = User::model()->findByPk($iUserid);
                if($oUser) {
                    $this->admin = $oUser->full_name;
                    $this->adminemail = $oUser->email;
                }
            }
        }
        $this->attachEventHandler("onAfterFind", array($this, 'afterFindSurvey'));
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return array(
            'running' => gT('running')
        );
    }

    /**
     * @inheritdoc With allow to delete all related models and data and test Permission.
     * @param bool $recursive
     **/
    public function delete($recursive=true)
    {
        if (!Permission::model()->hasSurveyPermission($this->sid, 'survey', 'delete')) {
            return false;
        }
        if(!parent::delete()) {
            return false;
        }
        if ($recursive) {
            //delete the survey_$iSurveyID table
            if (tableExists("{{survey_".$this->sid."}}")) {
                Yii::app()->db->createCommand()->dropTable("{{survey_".$this->sid."}}");
            }
            //delete the survey_$iSurveyID_timings table
            if (tableExists("{{survey_".$this->sid."_timings}}")) {
                Yii::app()->db->createCommand()->dropTable("{{survey_".$this->sid."_timings}}");
            }
            //delete the tokens_$iSurveyID table
            if (tableExists("{{tokens_".$this->sid."}}")) {
                Yii::app()->db->createCommand()->dropTable("{{tokens_".$this->sid."}}");
            }

            /* Remove User/global settings part : need Question and QuestionGroup*/
            // Settings specific for this survey
            $oCriteria = new CDbCriteria();
            $oCriteria->compare('stg_name', 'last_%', true, 'AND', false);
            $oCriteria->compare('stg_value', $this->sid, false, 'AND');
            SettingGlobal::model()->deleteAll($oCriteria);
            // Settings specific for this survey, 2nd part
            $oCriteria = new CDbCriteria();
            $oCriteria->compare('stg_name', 'last_%'.$this->sid.'%', true, 'AND', false);
            SettingGlobal::model()->deleteAll($oCriteria);
            // All Group id from this survey for ALL users
            $aGroupId = CHtml::listData(QuestionGroup::model()->findAll(array('select'=>'gid', 'condition'=>'sid=:sid', 'params'=>array(':sid'=>$this->sid))), 'gid', 'gid');
            $oCriteria = new CDbCriteria();
            $oCriteria->compare('stg_name', 'last_question_gid_%', true, 'AND', false);
            // pgsql need casting, unsure for mssql
            if (Yii::app()->db->getDriverName() == 'pgsql') {
                $oCriteria->addInCondition('CAST(stg_value as '.App()->db->schema->getColumnType("integer").')', $aGroupId);
            }
            //mysql App()->db->schema->getColumnType("integer") give int(11), mssql seems to have issue if cast alpha to numeric
            else {
                $oCriteria->addInCondition('stg_value', $aGroupId);
            }
            SettingGlobal::model()->deleteAll($oCriteria);
            // All Question id from this survey for ALL users
            $aQuestionId = CHtml::listData(Question::model()->findAll(array('select'=>'qid', 'condition'=>'sid=:sid', 'params'=>array(':sid'=>$this->sid))), 'qid', 'qid');
            $oCriteria = new CDbCriteria();
            $oCriteria->compare('stg_name', 'last_question_%', true, 'OR', false);
            if (Yii::app()->db->getDriverName() == 'pgsql') {
                $oCriteria->addInCondition('CAST(stg_value as '.App()->db->schema->getColumnType("integer").')', $aQuestionId);
            } else {
                $oCriteria->addInCondition('stg_value', $aQuestionId);
            }
            SettingGlobal::model()->deleteAll($oCriteria);

            $oResult = Question::model()->findAllByAttributes(array('sid' => $this->sid));
            foreach ($oResult as $aRow) {
                Answer::model()->deleteAllByAttributes(array('qid' => $aRow['qid']));
                Condition::model()->deleteAllByAttributes(array('qid' =>$aRow['qid']));
                QuestionAttribute::model()->deleteAllByAttributes(array('qid' => $aRow['qid']));
                DefaultValue::model()->deleteAllByAttributes(array('qid' => $aRow['qid']));
            }

            Question::model()->deleteAllByAttributes(array('sid' => $this->sid));
            Assessment::model()->deleteAllByAttributes(array('sid' => $this->sid));
            QuestionGroup::model()->deleteAllByAttributes(array('sid' => $this->sid));
            SurveyLanguageSetting::model()->deleteAllByAttributes(array('surveyls_survey_id' => $this->sid));
            Permission::model()->deleteAllByAttributes(array('entity_id' => $this->sid, 'entity'=>'survey'));
            SavedControl::model()->deleteAllByAttributes(array('sid' => $this->sid));
            SurveyURLParameter::model()->deleteAllByAttributes(array('sid' => $this->sid));
            //Remove any survey_links to the CPDB
            SurveyLink::model()->deleteLinksBySurvey($this->sid);
            Quota::model()->deleteQuota(array('sid' => $this->sid), true);
            // Remove all related plugin settings
            PluginSetting::model()->deleteAllByAttributes(array("model" =>'Survey', "model_id" => $this->sid));
            // Delete all uploaded files.
            rmdirr(Yii::app()->getConfig('uploaddir').'/surveys/'.$this->sid);
        }

        // Remove from cache
        if (array_key_exists($this->sid, $this->findByPkCache)) {
            unset ($this->findByPkCache[$this->sid]);
        }
                
        return true;
    }


    /**
     * The Survey languagesettings in currently active language. Falls back to the surveys' default language if the current language is not available.
     * @return SurveyLanguageSetting
     */
    public function getCurrentLanguageSettings()
    {
        if (isset($this->languagesettings[App()->language])) {
            return $this->languagesettings[App()->language];
        } else if(isset($this->languagesettings[$this->language])){
            return $this->languagesettings[$this->language];
        } else {
            throw new Exception('Selected Surveys language not found');
        }
    }

    /**
     * Return the language of the current survey
     * It can be:
     *  - the selected language by user via the language selector (POST then Session)
     *  - the selected language via URL (GET then Session)
     *  - the survey default language
     *
     * @return string the correct language
     */
    public function getLanguageForSurveyTaking()
    {
        // Default: the survey language
        $sLang = $this->language;

        if (Yii::app()->request->getParam('lang', null) !== null) {
            // POST or GET
            $sLang = Yii::app()->request->getParam('lang');
        } else {
            // SESSION
            if (isset(Yii::app()->session['survey_'.$this->sid]['s_lang'])) {
                $sLang = Yii::app()->session['survey_'.$this->sid]['s_lang'];
            }
        }
        return $sLang;
    }

    /**
     * Expires a survey. If the object was invoked using find or new surveyId can be ommited.
     * @param int $surveyId
     * @return boolean|null
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
            self::model()->updateByPk($surveyId, array('expires' => $dateTime));
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
            'permissions'     => array(self::HAS_MANY, 'Permission', array('entity_id'=> 'sid')), //
            'languagesettings' => array(self::HAS_MANY, 'SurveyLanguageSetting', 'surveyls_survey_id', 'index' => 'surveyls_language'),
            'defaultlanguage' => array(self::BELONGS_TO, 'SurveyLanguageSetting', array('language' => 'surveyls_language', 'sid' => 'surveyls_survey_id')),
            'correct_relation_defaultlanguage' => array(self::HAS_ONE, 'SurveyLanguageSetting', array('surveyls_language' => 'language', 'surveyls_survey_id' => 'sid')),
            'owner' => array(self::BELONGS_TO, 'User', 'owner_id',),
            'groups' => array(self::HAS_MANY, 'QuestionGroup', 'sid', 'order'=>'groups.group_order ASC'),
            'quotas' => array(self::HAS_MANY, 'Quota', 'sid', 'order'=>'name ASC'),
            'surveymenus' => array(self::HAS_MANY, 'Surveymenu', array('survey_id' => 'sid')),
            'surveygroup' => array(self::BELONGS_TO, 'SurveysGroups', array('gsid' => 'gsid')),
            'templateModel' => array(self::HAS_ONE, 'Template', array('name' => 'template')),
            'templateConfiguration' => array(self::HAS_ONE, 'TemplateConfiguration', array('sid' => 'sid'))
        );
    }


    /*  public function defaultScope()
    {
        return array('order'=> $this->getTableAlias().'.sid');
    }    */

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
            array('sid', 'numerical', 'integerOnly'=>true,'min'=>1), // max ?
            array('sid', 'unique'),// Not in pk
            array('gsid', 'numerical', 'integerOnly'=>true),
            array('datecreated', 'default', 'value'=>date("Y-m-d")),
            array('startdate', 'default', 'value'=>null),
            array('expires', 'default', 'value'=>null),
            array('admin,faxto', 'LSYii_Validators'),
            array('admin', 'length', 'min' => 1, 'max'=>50),
            array('faxto', 'length', 'min' => 0, 'max'=>20),
            array('adminemail', 'filter', 'filter'=>'trim'),
            array('bounce_email', 'filter', 'filter'=>'trim'),
            array('bounce_email', 'LSYii_EmailIDNAValidator', 'allowEmpty'=>true),
            array('active', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('gsid', 'numerical', 'min'=>'0', 'allowEmpty'=>true),
            array('anonymized', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('savetimings', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('datestamp', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('usecookie', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('allowregister', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('allowsave', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('autoredirect', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('allowprev', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('printanswers', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('ipaddr', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('refurl', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('publicstatistics', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('publicgraphs', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('listpublic', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('htmlemail', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('sendconfirmation', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('tokenanswerspersistence', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('assessments', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('usetokens', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('showxquestions', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('shownoanswer', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('showwelcome', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('showsurveypolicynotice', 'in', 'range'=>array('0', '1', '2'), 'allowEmpty'=>true),
            array('showprogress', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('questionindex', 'numerical', 'min' => 0, 'max' => 2, 'allowEmpty'=>false),
            array('nokeyboard', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('alloweditaftercompletion', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('bounceprocessing', 'in', 'range'=>array('L', 'N', 'G'), 'allowEmpty'=>true),
            array('usecaptcha', 'in', 'range'=>array('A', 'B', 'C', 'D', 'X', 'R', 'S', 'N'), 'allowEmpty'=>true),
            array('showgroupinfo', 'in', 'range'=>array('B', 'N', 'D', 'X'), 'allowEmpty'=>true),
            array('showqnumcode', 'in', 'range'=>array('B', 'N', 'C', 'X'), 'allowEmpty'=>true),
            array('format', 'in', 'range'=>array('G', 'S', 'A'), 'allowEmpty'=>true),
            array('googleanalyticsstyle', 'numerical', 'integerOnly'=>true, 'min'=>'0', 'max'=>'2', 'allowEmpty'=>true),
            array('autonumber_start', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true),
            array('tokenlength', 'default', 'value'=>15),
            array('tokenlength', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>false, 'min'=>'5', 'max'=>'35'),
            array('bouncetime', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true),
            array('navigationdelay', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true),
            array('template', 'filter', 'filter'=>array($this, 'filterTemplateSave')),
            array('language', 'LSYii_Validators', 'isLanguage'=>true),
            array('language', 'required', 'on' => 'insert'),
            array('language', 'filter', 'filter'=>'trim'),
            array('additional_languages', 'filter', 'filter'=>'trim'),
            array('additional_languages', 'LSYii_Validators', 'isLanguageMulti'=>true),
            array('running', 'safe', 'on'=>'search'),
            // Date rules currently don't work properly with MSSQL, deactivating for now
            //  array('expires','date', 'format'=>array('yyyy-MM-dd', 'yyyy-MM-dd HH:mm', 'yyyy-MM-dd HH:mm:ss',), 'allowEmpty'=>true),
            //  array('startdate','date', 'format'=>array('yyyy-MM-dd', 'yyyy-MM-dd HH:mm', 'yyyy-MM-dd HH:mm:ss',), 'allowEmpty'=>true),
            //  array('datecreated','date', 'format'=>array('yyyy-MM-dd', 'yyyy-MM-dd HH:mm', 'yyyy-MM-dd HH:mm:ss',), 'allowEmpty'=>true),
        );
    }


    /**
     * afterFindSurvey to fix and/or add some survey attribute
     * - Fix template name to be sure template exist
     */
    public function afterFindSurvey()
    {
        $event = new PluginEvent('afterFindSurvey');
        $event->set('surveyid', $this->sid);
        App()->getPluginManager()->dispatchEvent($event);
        $aAttributes = array_keys($this->getAttributes());
        foreach ($aAttributes as $attribute) {
            if (!is_null($event->get($attribute))) {
                $this->setAttribute($attribute,$event->get($attribute));
            }
        }
        $this->template = Template::templateNameFilter($this->template);
    }


    /**
     * filterTemplateSave to fix some template name
     * @param string $sTemplateName
     * @return string
     */
    public function filterTemplateSave($sTemplateName)
    {
        if (!Permission::model()->hasTemplatePermission($sTemplateName)) {
            // Reset to default only if different from actual value
            if (!$this->isNewRecord) {
                $oSurvey = self::model()->findByPk($this->sid);
                if ($oSurvey->template != $sTemplateName) {
                    // No need to test !is_null($oSurvey)
                    $sTemplateName = getGlobalSetting('defaulttheme');
                }
            } else {
                $sTemplateName = getGlobalSetting('defaulttheme');
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
     */
    public function permission($loginID)
    {
        $loginID = (int) $loginID;
        if (Permission::model()->hasGlobalPermission('surveys', 'read', $loginID)) {
            // Test global before adding criteria
            return $this;
        }
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
        if ($sLanguages != '') {
                    return explode(' ', $sLanguages);
        } else {
                    return array();
        }
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
        array_unshift($sLanguages, $this->language);
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
        if ($attdescriptiondata == null) {
            return array();
        }

        // Catches malformed data
        if ($attdescriptiondata && strpos(key(reset($attdescriptiondata)), 'attribute_') === false) {
            // don't know why yet but this breaks normal tokenAttributes functionning
            //$attdescriptiondata=array_flip(GetAttributeFieldNames($this->sid));
        } elseif (is_null($attdescriptiondata)) {
            $attdescriptiondata = array();
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
        // Without token table : all extra attribute are only saved on $this->attributedescriptions
        $allKnowAttributes = $attdescriptiondata;
        // Without token table : all attribute $this->attributedescriptions AND real attribute. @see issue #13924
        if($this->getHasTokensTable()){
            $allKnowAttributes = array_intersect_key(
                ( $attdescriptiondata + Token::model($this->sid)->getAttributes()),
                Token::model($this->sid)->getAttributes()
            );
            // We remove deleted attribute even if deleted manually in DB
        }
        $aCompleteData = array();
        foreach ($allKnowAttributes as $sKey=>$aValues) {
            if (preg_match("/^attribute_[0-9]{1,}$/", $sKey)) { // Select only extra attributes here
                if (!is_array($aValues)) {
                    $aValues = array();
                }
                // merge default with attributedescriptions
                $aCompleteData[$sKey] = array_merge(array(
                    'description' => $sKey,
                    'mandatory' => 'N',
                    'show_register' => 'N',
                    'cpdbmap' =>''
                ), $aValues);
            }
        }
        return $aCompleteData;
    }

    /**
     * Return the name of survey tokens table
     * @return string
     */
    public function getTokensTableName()
    {
        return "{{tokens_".$this->primaryKey."}}";
    }

    /**
     * Return the name of survey timigs table
     * @return string
     */
    public function getTimingsTableName()
    {
        return "{{survey_".$this->primaryKey."_timings}}";
    }

    /**
     * Return the name of survey responses (the data) table name
     * @return string
     */
    public function getResponsesTableName()
    {
        return '{{survey_'.$this->primaryKey.'}}';
    }


    /**
     * Returns true in a survey participants table exists for survey
     * @return boolean
     */
    public function getHasTokensTable()
    {
        // Make sure common_helper is loaded
        Yii::import('application.helpers.common_helper', true);
        return tableExists($this->tokensTableName);
    }

    /**
     * Wheteher the survey reponses (data) table exists in DB
     * @return boolean
     */
    public function getHasResponsesTable()
    {
        // Make sure common_helper is loaded
        Yii::import('application.helpers.common_helper', true);
        return tableExists($this->responsesTableName);
    }

    /**
     * Wheteher the survey reponses timings exists in DB
     * @return boolean
     */
    public function getHasTimingsTable()
    {
        // Make sure common_helper is loaded
        Yii::import('application.helpers.common_helper', true);
        return tableExists($this->timingsTableName);
    }

    /**
     * Returns the value for the SurveyEdit GoogleAnalytics API-Key UseGlobal Setting
     * @return string
     */
    public function getGoogleanalyticsapikeysetting()
    {
        if ($this->googleanalyticsapikey === "9999useGlobal9999") {
            return "G";
        } else if ($this->googleanalyticsapikey == "") {
            return "N";
        } else {
            return "Y";
        }
    }

    /**
     * @param string $value
     */
    public function setGoogleanalyticsapikeysetting($value)
    {
        if ($value == "G") {
            $this->googleanalyticsapikey = "9999useGlobal9999";
        } else if ($value == "N") {
            $this->googleanalyticsapikey = "";
        }
    }

    /**
     * Returns the value for the SurveyEdit GoogleAnalytics API-Key UseGlobal Setting
     * @return string
     */
    public function getGoogleanalyticsapikey()
    {
        if ($this->googleanalyticsapikey === "9999useGlobal9999") {
            return getGlobalSetting('googleanalyticsapikey');
        } else {
            return $this->googleanalyticsapikey;
        }
    }

    public function getSurveyTemplateConfiguration()
    {
        return TemplateConfiguration::getInstance(null, null, $this->sid);
    }

    private function __useTranslationForSurveymenu(&$entryData) {
        $entryData['title']             = gT($entryData['title']);
        $entryData['menu_title']        = gT($entryData['menu_title']);
        $entryData['menu_description']  = gT($entryData['menu_description']);
    }

    private function _createSurveymenuArray($oSurveyMenuObjects, $collapsed=false)
    {
        //Posibility to add more languages to the database is given, so it is possible to add a call by language
        //Also for peripheral menues we may add submenus someday.
        $aResultCollected = [];
        foreach ($oSurveyMenuObjects as $oSurveyMenuObject) {
            $entries = [];
            $aMenuEntries = $oSurveyMenuObject->surveymenuEntries;
            $submenus = $this->_getSurveymenuSubmenus($oSurveyMenuObject, $collapsed);
            foreach ($aMenuEntries as $menuEntry) {
                $aEntry = $menuEntry->attributes;
                //Skip menu if not activated in collapsed mode
                if ($collapsed && $aEntry['showincollapse'] == 0 ) {
                    continue;
                }

                //Skip menu if no permission
                 if (!empty($aEntry['permission']) && !empty($aEntry['permission_grade'])){
                     $inArray = array_search($aEntry['permission'],array_keys(Permission::getGlobalBasePermissions()));
                    if($inArray) {
                        $hasPermission = Permission::model()->hasGlobalPermission($aEntry['permission'], $aEntry['permission_grade']);
                    } else {
                        $hasPermission = Permission::model()->hasSurveyPermission($this->sid, $aEntry['permission'], $aEntry['permission_grade']);
                    }

                    if(!$hasPermission) {
                        continue;
                    }
                }

                // Check if a specific user owns this menu.
                if (!empty($aEntry['user_id'])) {
                    $userId = Yii::app()->session['loginID'];
                    if ($userId != $aEntry['user_id']) {
                        continue;
                    }
                }

                //parse the render part of the data attribute
                $oDataAttribute = new SurveymenuEntryData();
                $oDataAttribute->apply($menuEntry, $this->sid);

                if ($oDataAttribute->isActive !== null) {
                    if (($oDataAttribute->isActive == true && $this->active == 'N') || ($oDataAttribute->isActive == false && $this->active == 'Y')) {
                        continue;
                    }
                }

                $aEntry['link'] = $oDataAttribute->linkCreator();
                $aEntry['link_external'] = $oDataAttribute->linkExternal;
                $aEntry['debugData'] = $oDataAttribute->attributes;
                $aEntry['pjax'] = $oDataAttribute->pjaxed;
                $this->__useTranslationForSurveymenu($aEntry);
                $entries[$aEntry['id']] = $aEntry;
            }
            $aResultCollected[$oSurveyMenuObject->id] = [
                "id" => $oSurveyMenuObject->id,
                "title" => gt($oSurveyMenuObject->title),
                "name" => $oSurveyMenuObject->name,
                "ordering" => $oSurveyMenuObject->ordering,
                "level" => $oSurveyMenuObject->level,
                "description" => gT($oSurveyMenuObject->description),
                "entries" => $entries,
                "submenus" => $submenus
            ];
        }
        return $aResultCollected;
    }

    private function _getSurveymenuSubmenus($oParentSurveymenu, $collapsed=false)
    {
        $criteria = new CDbCriteria;
        $criteria->addCondition('survey_id=:surveyid OR survey_id IS NULL');
        $criteria->addCondition('parent_id=:parentid');
        $criteria->addCondition('level=:level');

        if ($collapsed === true) {
            $criteria->addCondition('showincollapse=1');
        }

        $criteria->params = [
            ':surveyid' => $oParentSurveymenu->survey_id,
            ':parentid' =>  $oParentSurveymenu->id,
            ':level'=> ($oParentSurveymenu->level + 1)
        ];

        $oMenus = Surveymenu::model()->findAll($criteria);

        $aResultCollected = $this->_createSurveymenuArray($oMenus, $collapsed);
        return $aResultCollected;
    }

    private function _getDefaultSurveyMenus($position = '')
    {
        $criteria = new CDbCriteria;
        $criteria->condition = 'survey_id IS NULL AND parent_id IS NULL';
        $collapsed = $position==='collapsed';

        if ($position != '' && !$collapsed) {
            $criteria->condition .= ' AND position=:position';
            $criteria->params = array(':position'=>$position);
        }

        if ($collapsed) {
            $criteria->condition .= ' AND (position=:position OR showincollapse=1 )';
            $criteria->params = array(':position'=>$position);
            $collapsed = true;
        }

        $oDefaultMenus = Surveymenu::model()->findAll($criteria);
        $aResultCollected = $this->_createSurveymenuArray($oDefaultMenus, $collapsed);

        return $aResultCollected;
    }


    /**
     * Get surveymenu configuration
     * This will be made bigger in future releases, but right now it only collects the default menu-entries
     */
    public function getSurveyMenus($position = '')
    {
        $collapsed = $position==='collapsed';
        //Get the default menus
        $aDefaultSurveyMenus = $this->_getDefaultSurveyMenus($position);
        //get all survey specific menus
        $aThisSurveyMenues = $this->_createSurveymenuArray($this->surveymenus, $collapsed);
        //merge them
        $aSurveyMenus = $aDefaultSurveyMenus + $aThisSurveyMenues;
        // var_dump($aDefaultSurveyMenus);
        // var_dump($aThisSurveyMenues);
        //soon to come => Event to add menus for plugins

        return $aSurveyMenus;
    }

    /**
     * Creates a new survey - with a random sid
     *
     * @param array $aData Array with fieldname=>fieldcontents data
     * @return \Survey
     */
    public function insertNewSurvey($aData)
    {
        if (!isset($aData['datecreated'])) {
            $aData['datecreated'] = date('Y-m-d H:i:s');
        }
        if(isset($aData['wishSID'])) {
            $aData['sid'] = $aData['wishSID'];
            unset($aData['wishSID']);
        }
        if(empty($aData['sid'])) {
            $aData['sid'] = intval(randomChars(6, '123456789'));
        }
        $survey = new self;
        foreach ($aData as $k => $v) {
            $survey->$k = $v;
        }

        $attempts = 0;
        /* Validate sid : > 1 and unique */
        while(!$survey->validate(array('sid'))) {
            $attempts++;
            $survey->sid = intval(randomChars(6, '123456789'));
            /* If it's happen : there are an issue in server … (or in randomChars function …) */
            if($attempts > 50) {
                throw new Exception("Unable to get a valid survey id after 50 attempts");
            }
        }

        if (!$survey->save()) {
            $survey->sid = null;
        }
        return $survey;
    }

    /**
     * Deletes a survey and all its data
     *
     * @access public
     * @param int $iSurveyID
     * @param bool $recursive
     * @return boolean
     */
    public function deleteSurvey($iSurveyID, $recursive = true)
    {
        $oSurvey = Survey::Model()->findByPk($iSurveyID);
        if (!$oSurvey) {
            return false;
        }
        return $oSurvey->delete($recursive);
    }

    /**
     * @inheritdoc . But use a static var because can be used a lot of time.
     */
    public function findByPk($pk, $condition = '', $params = array())
    {
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
    public function resetCache()
    {
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
     * @param string $attribute date attribute name
     * @return string formatted date
     */
    private function getDateFormatted($attribute)
    {
        $dateformatdata = getDateFormatData(Yii::app()->session['dateformat']);
        if ($this->$attribute) {
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
        if ($this->active == 'N') {
            return 'inactive';
        }
        if ($this->expires != '' || $this->startdate != '') {
            // Time adjust
            $sNow    = date("Y-m-d H:i:s", strtotime(Yii::app()->getConfig('timeadjust'), strtotime(date("Y-m-d H:i:s"))));
            $sStop   = ($this->expires != '') ? date("Y-m-d H:i:s", strtotime(Yii::app()->getConfig('timeadjust'), strtotime($this->expires))) : null;
            $sStart  = ($this->startdate != '') ? date("Y-m-d H:i:s", strtotime(Yii::app()->getConfig('timeadjust'), strtotime($this->startdate))) : null;

            // Time comparaison
            $oNow   = new DateTime($sNow);
            $oStop  = new DateTime($sStop);
            $oStart = new DateTime($sStart);

            $bExpired = (!is_null($sStop) && $oStop < $oNow);
            $bWillRun = (!is_null($oStart) && $oStart > $oNow);

            if ($bExpired) {
                return 'expired';
            }
            if ($bWillRun) {
                // And what happen if $sStop < $sStart : must return something other ?
                return 'willRun';
            }
            if(!is_null($sStop)) {
                return 'willExpire';
            }
        }
        // No returned before : it's running
        return 'running';
    }


    /**
     * @todo Document code, please.
     * @return string
     */
    public function getRunning()
    {

        // If the survey is not active, no date test is needed
        if ($this->active == 'N') {
            $running = '<a href="'.App()->createUrl('/admin/survey/sa/view/surveyid/'.$this->sid).'" class="survey-state" data-toggle="tooltip" title="'.gT('Inactive').'"><span class="fa fa-stop text-warning"></span><span class="sr-only">'.gT('Inactive').'"</span></a>';
        }
        // If it's active, then we check if not expired
        elseif ($this->expires != '' || $this->startdate != '') {
            // Time adjust
            $sNow    = date("Y-m-d H:i:s", strtotime(Yii::app()->getConfig('timeadjust'), strtotime(date("Y-m-d H:i:s"))));
            $sStop   = ($this->expires != '') ?date("Y-m-d H:i:s", strtotime(Yii::app()->getConfig('timeadjust'), strtotime($this->expires))) : $sNow;
            $sStart  = ($this->startdate != '') ?date("Y-m-d H:i:s", strtotime(Yii::app()->getConfig('timeadjust'), strtotime($this->startdate))) : $sNow;

            // Time comparaison
            $oNow   = new DateTime($sNow);
            $oStop  = new DateTime($sStop);
            $oStart = new DateTime($sStart);

            $bExpired = ($oStop < $oNow);
            $bWillRun = ($oStart > $oNow);

            $sStop = convertToGlobalSettingFormat($sStop);
            $sStart = convertToGlobalSettingFormat($sStart);

            // Icon generaton (for CGridView)
            $sIconRunning = '<a href="'.App()->createUrl('/admin/survey/sa/view/surveyid/'.$this->sid).'" class="survey-state" data-toggle="tooltip" title="'.sprintf(gT('End: %s'), $sStop).'"><span class="fa  fa-play text-success"></span><span class="sr-only">'.sprintf(gT('End: %s'), $sStop).'</span></a>';
            $sIconExpired = '<a href="'.App()->createUrl('/admin/survey/sa/view/surveyid/'.$this->sid).'" class="survey-state" data-toggle="tooltip" title="'.sprintf(gT('Expired: %s'), $sStop).'"><span class="fa fa fa-step-forward text-warning"></span><span class="sr-only">'.sprintf(gT('Expired: %s'), $sStop).'</span></a>';
            $sIconFuture  = '<a href="'.App()->createUrl('/admin/survey/sa/view/surveyid/'.$this->sid).'" class="survey-state" data-toggle="tooltip" title="'.sprintf(gT('Start: %s'), $sStart).'"><span class="fa  fa-clock-o text-warning"></span><span class="sr-only">'.sprintf(gT('Start: %s'), $sStart).'</span></a>';

            // Icon parsing
            if ($bExpired || $bWillRun) {
                // Expire prior to will start
                $running = ($bExpired) ? $sIconExpired : $sIconFuture;
            } else {
                $running = $sIconRunning;
            }
        }
        // If it's active, and doesn't have expire date, it's running
        else {
            $running = '<a href="'.App()->createUrl('/admin/survey/sa/view/surveyid/'.$this->sid).'" class="survey-state" data-toggle="tooltip" title="'.gT('Active').'"><span class="fa fa-play text-success"></span><span class="sr-only">'.gT('Active').'"</span></a>';
            //$running = '<div class="survey-state"><span class="fa fa-play text-success"></span></div>';
        }

        return $running;

    }

    /**
     * @return array|null
     */
    public function getPartialAnswers()
    {
        $table = $this->responsesTableName;
        if (method_exists(Yii::app()->cache, 'flush')) {
            Yii::app()->cache->flush();
        }
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
     * @return bool
     */
    public function getIsAnonymized()
    {
        return ($this->anonymized === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsSaveTimings()
    {
        return ($this->savetimings === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsDateStamp()
    {
        return ($this->datestamp === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsUseCookie()
    {
        return ($this->usecookie === 'Y');
    }

    /**
     * @return bool
     */
    public function getIsAllowRegister()
    {
        return ($this->allowregister === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsAllowSave()
    {
        return ($this->allowsave === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsAutoRedirect()
    {
        return ($this->autoredirect === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsAllowPrev()
    {
        return ($this->allowprev === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsPrintAnswers()
    {
        return ($this->printanswers === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsIpAddr()
    {
        return ($this->ipaddr === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsRefUrl()
    {
        return ($this->refurl === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsPublicStatistics()
    {
        return ($this->publicstatistics === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsPublicGraphs()
    {
        return ($this->publicgraphs === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsListPublic()
    {
        return ($this->listpublic === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsHtmlEmail()
    {
        return ($this->htmlemail === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsSendConfirmation()
    {
        return ($this->sendconfirmation === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsTokenAnswersPersistence()
    {
        return ($this->tokenanswerspersistence === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsAssessments()
    {
        return ($this->assessments === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsShowXQuestions()
    {
        return ($this->showxquestions === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsShowGroupInfo()
    {
        return ($this->showgroupinfo === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsShowNoAnswer()
    {
        return ($this->shownoanswer === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsShowQnumCode()
    {
        return ($this->showqnumcode === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsShowWelcome()
    {
        return ($this->showwelcome === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsShowProgress()
    {
        return ($this->showprogress === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsNoKeyboard()
    {
        return ($this->nokeyboard === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsAllowEditAfterCompletion()
    {
        return ($this->alloweditaftercompletion === 'Y');
    }

    /**
     * Returns the title of the survey. Uses the current language and
     * falls back to the surveys' default language if the current language is not available.
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
     * @return array|null
     */
    public function getFullAnswers()
    {
        $table = $this->responsesTableName;
        if (method_exists(Yii::app()->cache, 'flush')) {
            Yii::app()->cache->flush();
        }
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
        $sResponseTable = $this->responsesTableName;
        if (method_exists(Yii::app()->cache, 'flush')) {
            Yii::app()->cache->flush();
        }
        if ($this->active != 'Y') {
            return 0;
        } else {
            $answers = Yii::app()->db->createCommand()
                ->select('count(*)')
                ->from($sResponseTable)
                ->where('submitdate IS NOT NULL')
                ->queryScalar();
            return $answers;
        }
    }

    /**
     * @return int
     */
    public function getCountPartialAnswers()
    {
        $table = $this->responsesTableName;
        if (method_exists(Yii::app()->cache, 'flush')) {
            Yii::app()->cache->flush();
        }
        if ($this->active != 'Y') {
            return 0;
        } else {
            $answers = Yii::app()->db->createCommand()
                ->select('count(*)')
                ->from($table)
                ->where('submitdate IS NULL')
                ->queryScalar();
            return $answers;
        }
    }

    /**
     * decodes the attributedescriptions to be used anywhere necessary
     * @return Array
     */
    public function getDecodedAttributedescriptions()
    {
        return decodeTokenAttributes($this->attributedescriptions);
    }

    /**
     * @return int
     */
    public function getCountTotalAnswers()
    {
        return ($this->countFullAnswers + $this->countPartialAnswers);
    }

    /**
     * @return string
     */
    public function getbuttons()
    {
        $sEditUrl     = App()->createUrl("/admin/survey/sa/editlocalsettings/surveyid/".$this->sid);
        $sStatUrl     = App()->createUrl("/admin/statistics/sa/simpleStatistics/surveyid/".$this->sid);
        $sAddGroup    = App()->createUrl("/admin/questiongroups/sa/add/surveyid/".$this->sid); ;
        $sAddquestion = App()->createUrl("/admin/questions/sa/newquestion/surveyid/".$this->sid); ;

        $button = '';

        if (Permission::model()->hasSurveyPermission($this->sid, 'survey', 'update')) {
            $button .= '<a class="btn btn-default" href="'.$sEditUrl.'" role="button" data-toggle="tooltip" title="'.gT('General settings & texts').'"><span class="fa fa-cog" ></span><span class="sr-only">'.gT('General settings & texts').'</span></a>';
        }

        if (Permission::model()->hasSurveyPermission($this->sid, 'statistics', 'read') && $this->active == 'Y') {
            $button .= '<a class="btn btn-default" href="'.$sStatUrl.'" role="button" data-toggle="tooltip" title="'.gT('Statistics').'"><span class="fa fa-bar-chart text-success" ></span><span class="sr-only">'.gT('Statistics').'</span></a>';
        }

        if (Permission::model()->hasSurveyPermission($this->sid, 'survey', 'create')) {
            if ($this->active != 'Y') {
                $groupCount = QuestionGroup::model()->countByAttributes(array('sid' => $this->sid, 'language' => $this->language)); //Checked
                if ($groupCount > 0) {
                    $button .= '<a class="btn btn-default" href="'.$sAddquestion.'" role="button" data-toggle="tooltip" title="'.gT('Add new question').'"><span class="icon-add text-success" ></span><span class="sr-only">'.gT('Add new question').'</span></a>';
                } else {
                    $button .= '<a class="btn btn-default" href="'.$sAddGroup.'" role="button" data-toggle="tooltip" title="'.gT('Add new group').'"><span class="icon-add text-success" ></span><span class="sr-only">'.gT('Add new group').'</span></a>';
                }
            }
        }

        //$previewUrl = Yii::app()->createUrl("survey/index/sid/");
        //$previewUrl .= '/'.$this->sid;
        //$button = '<a class="btn btn-default open-preview" aria-data-url="'.$previewUrl.'" aria-data-language="'.$this->language.'" href="# role="button" ><span class="fa fa-eye"  ></span></a> ';

        return $button;
    }

    /**
     * @return CActiveDataProvider
     */
    public function search()
    {
        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);

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

            'group'=>array(
                'asc'  => 'surveygroup.title asc',
                'desc' => 'surveygroup.title desc',
            ),

        );
        $sort->defaultOrder = array('creation_date' => CSort::SORT_DESC);

        $criteria = new LSDbCriteria;
        $aWithRelations = array('correct_relation_defaultlanguage');

        // Search filter
        $sid_reference = (Yii::app()->db->getDriverName() == 'pgsql' ? ' t.sid::varchar' : 't.sid');
        $aWithRelations[] = 'owner';
        $aWithRelations[] = 'surveygroup';
        $criteria->compare($sid_reference, $this->searched_value, true);
        $criteria->compare('t.admin', $this->searched_value, true, 'OR');
        $criteria->compare('owner.users_name', $this->searched_value, true, 'OR');
        $criteria->compare('correct_relation_defaultlanguage.surveyls_title', $this->searched_value, true, 'OR');
        $criteria->compare('surveygroup.title', $this->searched_value, true, 'OR');

        // Survey group filter
        if (isset($this->gsid)) {
            $criteria->compare("t.gsid", $this->gsid, false);
        }

        // show only surveys belonging to selected survey group
        if (!empty(Yii::app()->request->getParam('id'))) {
            $criteria->addCondition("t.gsid = " . sanitize_int(Yii::app()->request->getParam('id')), 'AND');
        }

        // Active filter
        if (isset($this->active)) {
            if ($this->active == 'N' || $this->active == "Y") {
                $criteria->compare("t.active", $this->active, false);
            } else {
                // Time adjust
                $sNow = date("Y-m-d H:i:s", strtotime(Yii::app()->getConfig('timeadjust'), strtotime(date("Y-m-d H:i:s"))));

                if ($this->active == "E") {
                    $criteria->compare("t.active", 'Y');
                    $criteria->addCondition("t.expires <'$sNow'");
                } if ($this->active == "S") {
                    $criteria->compare("t.active", 'Y');
                    $criteria->addCondition("t.startdate >'$sNow'");
                }

                if ($this->active == "R") {
                    $criteria->compare("t.active", 'Y');
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


        $criteria->with = $aWithRelations;

        // Permission
        // Note: reflect Permission::hasPermission
        if (!Permission::model()->hasGlobalPermission("surveys", 'read')) {
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
        $dataProvider = new CActiveDataProvider('Survey', array(
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
    public static function transcribeCaptchaOptions()
    {
        // TODO POST handling should be done in controller!
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
     * Transcribe from 3 checkboxes to 1 char for captcha usages
     * Uses variables from $_POST and transferred Surveyobject
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
    public static function saveTranscribeCaptchaOptions(Survey $oSurvey)
    {
        // TODO POST handling should be done in controller!
        $surveyaccess = App()->request->getPost('usecaptcha_surveyaccess', null);
        $registration = App()->request->getPost('usecaptcha_registration', null);
        $saveandload = App()->request->getPost('usecaptcha_saveandload', null);

        if ($surveyaccess === null && $registration === null && $saveandload === null) {
            return $oSurvey->usecaptcha;
        }

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
    public function calculateEstimatedTime()
    {
        //@TODO make the time_per_question variable user configureable
        $time_per_question = 0.5;
        $criteria = new CDbCriteria();
        $criteria->addCondition('sid = '.$this->sid);
        $criteria->addCondition('parent_qid = 0');
        $criteria->addCondition('language = \''.$this->language.'\'');
        $baseQuestions = Question::model()->count($criteria);
        // Note: An array questions with one sub question is fetched as 1 base question + 1 sub question
        $criteria = new CDbCriteria();
        $criteria->addCondition('sid = '.$this->sid);
        $criteria->addCondition('parent_qid != 0');
        $criteria->addCondition('language = \''.$this->language.'\'');
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
        $surveys = array_filter($surveys, function($s)
        {
return $s->hasTokensTable; });
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
            'params'=>array('sid' => $this->sid, 'language' => $this->language)
        ));
        $criteria = new CDbCriteria;
        $criteria->compare('sid', $this->sid);
        $criteria->addCondition('parent_qid = 0');
        $criteria->addNotInCondition('qid', CHtml::listData($validQuestion, 'qid', 'qid'));
        Question::model()->deleteAll($criteria); // Must log count of deleted ?

        /* Delete invalid Sub questions (don't exist in primary language) using title like column name*/
        $validSubQuestion = Question::model()->findAll(array(
            'select'=>'title',
            'condition'=>'sid=:sid AND language=:language AND parent_qid != 0',
            'params'=>array('sid' => $this->sid, 'language' => $this->language)
        ));
        $criteria = new CDbCriteria;
        $criteria->compare('sid', $this->sid);
        $criteria->addCondition('parent_qid != 0');
        $criteria->addNotInCondition('title', CHtml::listData($validSubQuestion, 'title', 'title'));
        Question::model()->deleteAll($criteria); // Must log count of deleted ?
    }

    public function getsSurveyUrl()
    {
        if ($this->sSurveyUrl == '') {
            if (!in_array(App()->language, $this->getAllLanguages())) {
                $surveylang = $this->language;
            } else {
                $surveylang = App()->language;
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

        $criteria->addInCondition('t.type', Question::getQuotableTypes());

        /** @var Question[] $questions */
        $questions = Question::model()->findAll($criteria);
        return $questions;
    }

    /**
     * Get the DB criteria to get questions as ordered in survey
     * @return CDbCriteria
     */
    private function getQuestionOrderCriteria()
    {
        $criteria = new CDbCriteria;
        $criteria->select = Yii::app()->db->quoteColumnName('t.*');
        $criteria->with = array(
            'survey.groups',
        );

        if (Yii::app()->db->driverName == 'sqlsrv' || Yii::app()->db->driverName == 'dblib'){
            $criteria->order = Yii::app()->db->quoteColumnName('t.question_order');
        } else {
            $criteria->order = Yii::app()->db->quoteColumnName('groups.group_order').','.Yii::app()->db->quoteColumnName('t.question_order');
        }
        $criteria->addCondition('groups.gid=t.gid', 'AND');
        return $criteria;

    }
    /**
     * Gets number of groups inside a particular survey
     */
    public function getGroupsCount()
    {
        //$condn = "WHERE sid=".$surveyid." AND language='".$lang."'"; //Getting a count of questions for this survey
        $condn = array('sid'=>$this->sid, 'language'=>$this->language);
        $sumresult3 = QuestionGroup::model()->countByAttributes($condn); //Checked)
        return $sumresult3;
    }

    /**
     * @return integer
     */
    public function getCountTotalQuestions()
    {
        $condn = array('sid'=>$this->sid, 'language'=>$this->language, 'parent_qid'=>0);
        $sumresult = Question::model()->countByAttributes($condn);
        return (int) $sumresult;
    }

    /**
     * Get the coutn of questions that do not need input (skipping text-display etc.)
     * @return integer
     */
    public function getCountNoInputQuestions()
    {
        $condn = array(
            'sid'=>$this->sid,
            'language'=>$this->language,
            'parent_qid'=>0,
            'type'=>['X', '*'],
        );
        $sumresult = Question::model()->countByAttributes($condn);
        return (int) $sumresult;
    }

    /**
     * Get the coutn of questions that need input (skipping text-display etc.)
     * @return integer
     */
    public function getCountInputQuestions()
    {
        return $this->countTotalQuestions - $this->countNoInputQuestions;
    }


    /**
     * Returns true if this survey has any question of type $type.
     * @param string $type Question type, like 'L', 'T', etc.
     * @param boolean $includeSubquestions If true, will also check the types of subquestions.
     * @return boolean
     * @throws CException
     */
    public function hasQuestionType($type, $includeSubquestions = false)
    {
        if (!is_string($type) || strlen($type) !== 1) {
            throw new InvalidArgumentException('$type must be a string of length 1');
        }

        if ($includeSubquestions) {
            $joinCondition =
                '{{questions.sid}} = {{surveys.sid}} AND {{questions.type}} = :type';
        } else {
            $joinCondition =
                '{{questions.sid}} = {{surveys.sid}} AND {{questions.parent_qid}} = 0 AND {{questions.type}} = :type';
        }

        $result = Yii::app()->db->createCommand()
            ->select('{{surveys.sid}}')
            ->from('{{surveys}}')
            ->join(
                '{{questions}}',
                $joinCondition,
                array(':type' => $type)
            )
            ->where('{{surveys.sid}} = :sid', array(':sid' => $this->sid))
            ->queryRow();
        return $result !== false;
    }

    public static function replacePolicyLink($dataSecurityNoticeLabel, $surveyId) {

        $STARTPOLICYLINK = "";
        $ENDPOLICYLINK = "";

        if(self::model()->findByPk($surveyId)->showsurveypolicynotice == 2){
            $STARTPOLICYLINK = "<a href='#data-security-modal-".$surveyId."' data-toggle='collapse'>";
            $ENDPOLICYLINK = "</a>";
            if(!preg_match('/(\{STARTPOLICYLINK\}|\{ENDPOLICYLINK\})/', $dataSecurityNoticeLabel)){
                $dataSecurityNoticeLabel.= "<br/> {STARTPOLICYLINK}".gT("Show policy")."{ENDPOLICYLINK}";
            }
        }



        $dataSecurityNoticeLabel =  preg_replace('/\{STARTPOLICYLINK\}/', $STARTPOLICYLINK ,$dataSecurityNoticeLabel);

        $countEndLabel = 0;
        $dataSecurityNoticeLabel =  preg_replace('/\{ENDPOLICYLINK\}/', $ENDPOLICYLINK ,$dataSecurityNoticeLabel, -1, $countEndLabel);
        if($countEndLabel == 0){
            $dataSecurityNoticeLabel .= '</a>';
        }

        return $dataSecurityNoticeLabel;

    }

    /**
     * @return string
     */
    public function getOwnerUserName()
    {
        return isset($this->owner["users_name"]) ? $this->owner["users_name"] : "";
    }

}
