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
use LimeSurvey\PluginManager\PluginEvent;

/**
 * Class Survey
 *
 * @property integer $sid Survey ID
 * @property integer $owner_id
 * @property integer $gsid survey group id, from which this survey belongs to and inherits values from when set to 'I'
 * @property string $admin Survey Admin's full name
 * @property string $active Whether survey is acive or not (Y/N)
 * @property string|null $expires Expiry date as SQL datetime (YYYY-MM-DD hh:mm:ss)
 * @property string|null $startdate Survey Start date as SQL datetime (YYYY-MM-DD hh:mm:ss)
 * @property string $adminemail Survey administrator email address
 * @property string $anonymized Whether survey is anonymized or not (Y/N)
 * @property string $format A : All in one, G : Group by group, Q : question by question, I : inherit value from survey group
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
 * @property string $ipanonymize Whether id addresses should be anonymized (Y/N)
 * @property string $refurl Save referrer URL: (Y/N)
 * @property string $datecreated Date survey was created  as SQL datetime (YYYY-MM-DD hh:mm:ss)
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
 * @property integer $navigationdelay Navigation delay (seconds) (It shows the number of seconds before the previous,
 * next, and submit buttons are enabled. If none is specified, the option will use the default value, which is "0" (seconds))
 * @property string $nokeyboard Show on-screen keyboard: (Y/N)
 * @property string $alloweditaftercompletion Allow multiple responses or update responses with one token: (Y/N)
 * @property string $googleanalyticsstyle Google Analytics style: (0: off; 1:Default; 2:Survey-SID/Group)
 * @property string $googleanalyticsapikey Google Analytics Tracking ID
 * @property string $tokenencryptionoptions Token encryption options
 *
 * @property Permission[] $permissions
 * @property SurveyLanguageSetting[] $languagesettings
 * @property User $owner
 * @property QuestionGroup[] $groups
 * @property Quota[] $quotas
 * @property Question[] $allQuestions All survey questions including subquestions
 * @property Question[] $baseQuestions Survey questions NOT including subquestions
 * @property Question[] $quotableQuestions
 *
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
 * @property boolean $hasResponsesTable Wheteher the survey responses (data) table exists in DB
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
 * @property bool $isIpAnonymize Whether Participants IP address will be saved
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
 * @property string $access_mode Whether the access mode is open (O) or closed (C), if O token-based participation may be supported, if C, it's enforced
 * @property SurveyLanguageSetting $defaultlanguage
 * @property SurveysGroups $surveygroup
 * @property boolean $isDateExpired Whether survey is expired depending on the current time and survey configuration status
 * @method mixed active()
 */
class Survey extends LSActiveRecord implements PermissionInterface
{
    use PermissionTrait;

    protected static array $findByPkCache = [];

    // survey options
    public $oOptions;
    public $oOptionLabels;
    // used for twig files, same content as $oOptions, but in array format
    public $aOptions = array();

    public $showInherited = 1;

    public $searched_value;

    public $showsurveypolicynotice = 0;

    // Whether to show the option values of the survey or the inherited ones, if applicable.
    public $bShowRealOptionValues = true;


    private $sSurveyUrl;

    /**
     * Set defaults
     * @inheritdoc
     */
    public function init()
    {
        /** @inheritdoc */
        /* Do not set any default when search, reset gsid */
        if ($this->scenario == 'search') {
            $this->gsid = null;
            return;
        }
        if ($this->isNewRecord) {
            $this->setAttributeDefaults();
        }
        $this->attachEventHandler("onAfterFind", array($this, 'afterFindSurvey'));
        $this->attachEventHandler("onAfterSave", array($this, 'unsetFromStaticPkCache'));
    }

    /**
     * Delete from static $findByPkCache
     * return void
     */
    public function unsetFromStaticPkCache()
    {
        unset(self::$findByPkCache[$this->sid]);
    }

    private function setAttributeDefaults()
    {
        // Set the default values
        $this->htmlemail = 'Y';
        $this->format = 'G';
        $this->tokenencryptionoptions = '';

        // Default setting is to use the global Google Analytics key If one exists
        $globalKey = App()->getConfig('googleanalyticsapikey');
        if ($globalKey != "") {
            $this->googleanalyticsapikey = "9999useGlobal9999";
            $this->googleanalyticsapikeysetting = "G";
        }
        /* default template */
        $this->template = 'inherit';
        /* default language */
        $validator = new LSYii_Validators();
        $this->language = $validator->languageFilter(App()->getConfig('defaultlang'));
        /* default user */
        $this->owner_id = 1;
        $this->admin = App()->getConfig('siteadminname');
        $this->adminemail = App()->getConfig('siteadminemail');
        if (!(Yii::app() instanceof CConsoleApplication)) {
            $iUserid = Permission::model()->getUserId();
            if ($iUserid) {
                $this->owner_id = $iUserid;
                $oUser = User::model()->findByPk($iUserid);
                if ($oUser) {
                    $this->admin = $oUser->full_name;
                    $this->adminemail = $oUser->email;
                }
            }
        }
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
    public function delete($recursive = true)
    {
        if (!Permission::model()->hasSurveyPermission($this->sid, 'survey', 'delete')) {
            return false;
        }
        if (!parent::delete()) {
            return false;
        }
        if ($recursive) {
            //delete the survey_$iSurveyID table
            if (tableExists("{{survey_" . $this->sid . "}}")) {
                Yii::app()->db->createCommand()->dropTable("{{survey_" . $this->sid . "}}");
            }
            //delete the survey_$iSurveyID_timings table
            if (tableExists("{{survey_" . $this->sid . "_timings}}")) {
                Yii::app()->db->createCommand()->dropTable("{{survey_" . $this->sid . "_timings}}");
            }
            //delete the tokens_$iSurveyID table
            if (tableExists("{{tokens_" . $this->sid . "}}")) {
                Yii::app()->db->createCommand()->dropTable("{{tokens_" . $this->sid . "}}");
            }

            /* Remove User/global settings part : need Question and QuestionGroup*/
            // Settings specific for this survey
            $oCriteria = new CDbCriteria();
            $oCriteria->compare('stg_name', 'last_survey');
            $oCriteria->compare('stg_value', $this->sid);
            SettingsUser::model()->deleteAll($oCriteria);
            // Settings specific for this survey, 2nd part
            $oCriteria = new CDbCriteria();
            $oCriteria->compare('entity_id', $this->sid);
            $oCriteria->compare('entity', 'Survey');
            SettingsUser::model()->deleteAll($oCriteria);
            // All Question id from this survey for ALL users
            $aQuestionId = CHtml::listData(Question::model()->findAll(array('select' => 'qid', 'condition' => 'sid=:sid', 'params' => array(':sid' => $this->sid))), 'qid', 'qid');
            $oCriteria = new CDbCriteria();
            $oCriteria->compare('stg_name', 'last_question');
            if (Yii::app()->db->getDriverName() == 'pgsql') {
                // Still needed ? : CHtml::listData return only existing qid as integer
                $oCriteria->addInCondition('CAST(NULLIF(stg_value, \'\') AS ' . App()->db->schema->getColumnType("integer") . ')', $aQuestionId);
            } else {
                $oCriteria->addInCondition('stg_value', $aQuestionId);
            }
            SettingsUser::model()->deleteAll($oCriteria);

            $oQuestions = Question::model()->findAllByAttributes(array('sid' => $this->sid));
            foreach ($oQuestions as $aQuestion) {
                // answers
                $oAnswers = Answer::model()->findAllByAttributes(array('qid' => $aQuestion['qid']));
                foreach ($oAnswers as $aAnswer) {
                    AnswerL10n::model()->deleteAllByAttributes(array('aid' => $aAnswer['aid']));
                }
                Answer::model()->deleteAllByAttributes(array('qid' => $aQuestion['qid']));

                Condition::model()->deleteAllByAttributes(array('qid' => $aQuestion['qid']));
                QuestionAttribute::model()->deleteAllByAttributes(array('qid' => $aQuestion['qid']));
                QuestionL10n::model()->deleteAllByAttributes(array('qid' => $aQuestion['qid']));

                // delete defaultvalues and defaultvalueL10ns
                $oDefaultValues = DefaultValue::model()->findAll('qid = :qid', array(':qid' => $aQuestion['qid']));
                foreach ($oDefaultValues as $defaultvalue) {
                    DefaultValue::model()->deleteAll('dvid = :dvid', array(':dvid' => $defaultvalue->dvid));
                    DefaultValueL10n::model()->deleteAll('dvid = :dvid', array(':dvid' => $defaultvalue->dvid));
                };
            }

            Question::model()->deleteAllByAttributes(array('sid' => $this->sid));
            Assessment::model()->deleteAllByAttributes(array('sid' => $this->sid));

            // question groups
            $oQuestionGroups = QuestionGroup::model()->findAllByAttributes(array('sid' => $this->sid));
            foreach ($oQuestionGroups as $aQuestionGroup) {
                QuestionGroupL10n::model()->deleteAllByAttributes(array('gid' => $aQuestionGroup['gid']));
            }
            QuestionGroup::model()->deleteAllByAttributes(array('sid' => $this->sid));

            SurveyLanguageSetting::model()->deleteAllByAttributes(array('surveyls_survey_id' => $this->sid));
            Permission::model()->deleteAllByAttributes(array('entity_id' => $this->sid, 'entity' => 'survey'));
            SavedControl::model()->deleteAllByAttributes(array('sid' => $this->sid));
            SurveyURLParameter::model()->deleteAllByAttributes(array('sid' => $this->sid));
            //Remove any survey_links to the CPDB
            SurveyLink::model()->deleteLinksBySurvey($this->sid);
            Quota::model()->deleteQuota(array('sid' => $this->sid), true);
            // Remove all related plugin settings
            PluginSetting::model()->deleteAllByAttributes(array("model" => 'Survey', "model_id" => $this->sid));
            // Delete all uploaded files.
            rmdirr(Yii::app()->getConfig('uploaddir') . '/surveys/' . $this->sid);
            // Delete all failed email notifications
            FailedEmail::model()->deleteAllByAttributes(array('surveyid' => $this->sid));
        }

        // Remove from cache
        if (array_key_exists($this->sid, self::$findByPkCache)) {
            unset(self::$findByPkCache[$this->sid]);
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
        } elseif (isset($this->languagesettings[$this->language])) {
            return $this->languagesettings[$this->language];
        } else {
            $searchedLanguages = App()->language;
            if ($this->language != App()->language) {
                $searchedLanguages .= ',' . $this->language;
            }
            $errorString = sprintf(gT('Survey language settings (%s) not found. Please run the integrity check from the main menu.'), $searchedLanguages);
            throw new Exception($errorString);
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
            if (isset(Yii::app()->session['survey_' . $this->sid]['s_lang'])) {
                $sLang = Yii::app()->session['survey_' . $this->sid]['s_lang'];
            }
        }
        return $sLang;
    }

    /**
     * Expires a survey. If the object was invoked using find or new surveyId can be ommited.
     *
     * @param int $surveyId Survey ID
     *
     * @return boolean|null
     */
    public function expire($surveyId = null)
    {
        $dateTime = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig('timeadjust'));
        $dateTime = dateShift($dateTime, "Y-m-d H:i:s", '-1 minute');

        $model = $this;

        // Set model based on surveyId, if given
        // If so, set scenario as to be saved later
        if (isset($surveyId)) {
            $model = self::model()->findByPk($surveyId);
            $model->setScenario('update');
        }

        // Avoid setting expiration date before start date
        // If there is a future start date set, set the expiration date to the same date
        if (!empty($model->startdate) && $dateTime < $model->startdate) {
            $dateTime = $model->startdate;
        }

        // Set expiration date
        $model->expires = $dateTime;

        // Save if scenario is update
        if ($model->scenario == 'update') {
            return $model->save();
        }

        return null;
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
    public static function model($className = __CLASS__)
    {
        /** @var Survey $model */
        $model = parent::model($className);
        return $model;
    }

    /** @inheritdoc */
    public function relations()
    {
        return array(
            'permissions'     => array(self::HAS_MANY, 'Permission', array('entity_id' => 'sid')), //
            'languagesettings' => array(self::HAS_MANY, 'SurveyLanguageSetting', 'surveyls_survey_id', 'index' => 'surveyls_language'),
            'defaultlanguage' => array(self::BELONGS_TO, 'SurveyLanguageSetting', array('language' => 'surveyls_language', 'sid' => 'surveyls_survey_id')),
            'correct_relation_defaultlanguage' => array(self::HAS_ONE, 'SurveyLanguageSetting', array('surveyls_language' => 'language', 'surveyls_survey_id' => 'sid')),
            'owner' => array(self::BELONGS_TO, 'User', 'owner_id',),
            'groups' => array(self::HAS_MANY, 'QuestionGroup', 'sid', 'order' => 'groups.group_order ASC', 'together' => true),
            'questions' => array(self::HAS_MANY, 'Question', 'sid', 'order' => 'questions.qid ASC'),
            'quotas' => array(self::HAS_MANY, 'Quota', 'sid', 'order' => 'name ASC'),
            'surveymenus' => array(self::HAS_MANY, 'Surveymenu', array('survey_id' => 'sid')),
            'surveygroup' => array(self::BELONGS_TO, 'SurveysGroups', array('gsid' => 'gsid')),
            'surveysettings' => array(self::BELONGS_TO, SurveysGroupsettings::class, array('gsid' => 'gsid')),
            'templateModel' => array(self::HAS_ONE, 'Template', array('name' => 'template')),
            'templateConfiguration' => array(self::HAS_ONE, 'TemplateConfiguration', array('sid' => 'sid'))
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
            array('sid', 'numerical', 'integerOnly' => true,'min' => 1), // max ?
            array('sid', 'unique'),// Not in pk
            array('gsid', 'numerical', 'integerOnly' => true),
            array('datecreated', 'default', 'value' => date("Y-m-d H:i:s")),
            array('startdate', 'default', 'value' => null),
            array('expires', 'default', 'value' => null),
            array('admin', 'LSYii_Validators'),
            array('admin', 'length', 'min' => 1, 'max' => 50),
            array('adminemail', 'LSYii_FilterValidator', 'filter' => 'trim', 'skipOnEmpty' => true),
            array('adminemail', 'LSYii_EmailIDNAValidator', 'allowEmpty' => true, 'allowInherit' => true),
            array('bounce_email', 'LSYii_FilterValidator', 'filter' => 'trim', 'skipOnEmpty' => true),
            array('bounce_email', 'LSYii_EmailIDNAValidator', 'allowEmpty' => true, 'allowInherit' => true),
            array('active', 'in', 'range' => array('Y', 'N'), 'allowEmpty' => true),
            array('gsid', 'numerical', 'min' => '0', 'allowEmpty' => true),
            array('gsid', 'in', 'range' => array_keys(SurveysGroups::getSurveyGroupsList()), 'allowEmpty' => true, 'message' => gT("You are not allowed to use this group"), 'except' => 'activationStateChange'),
            array('anonymized', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('savetimings', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('datestamp', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('usecookie', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('allowregister', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('allowsave', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('autoredirect', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('allowprev', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('printanswers', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('ipaddr', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('ipanonymize', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('refurl', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('publicstatistics', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('publicgraphs', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('listpublic', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('htmlemail', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('sendconfirmation', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('tokenanswerspersistence', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('assessments', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('usetokens', 'in', 'range' => array('Y', 'N'), 'allowEmpty' => true),
            array('showxquestions', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('shownoanswer', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('showwelcome', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('showsurveypolicynotice', 'in', 'range' => array('0', '1', '2'), 'allowEmpty' => true),
            array('showprogress', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('questionindex', 'numerical', 'min' => -1, 'max' => 2, 'allowEmpty' => false),
            array('nokeyboard', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('alloweditaftercompletion', 'in', 'range' => array('Y', 'N', 'I'), 'allowEmpty' => true),
            array('bounceprocessing', 'in', 'range' => array('L', 'N', 'G'), 'allowEmpty' => true),
            array('usecaptcha', 'in', 'range' => array('A', 'B', 'C', 'D', 'X', 'R', 'S', 'N', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'O', 'P', 'T', 'U', '1', '2', '3', '4', '5', '6'), 'allowEmpty' => true),
            array('showgroupinfo', 'in', 'range' => array('B', 'N', 'D', 'X', 'I'), 'allowEmpty' => true),
            array('showqnumcode', 'in', 'range' => array('B', 'N', 'C', 'X', 'I'), 'allowEmpty' => true),
            array('format', 'in', 'range' => array('G', 'S', 'A', 'I'), 'allowEmpty' => true),
            array('googleanalyticsstyle', 'numerical', 'integerOnly' => true, 'min' => '0', 'max' => '3', 'allowEmpty' => true),
            array('autonumber_start', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('tokenlength', 'default', 'value' => 15),
            array('tokenlength', 'numerical', 'integerOnly' => true, 'allowEmpty' => false, 'min' => '-1', 'max' => Token::MAX_LENGTH, 'tooBig' => gT('Token length cannot be bigger than {max} characters.')),
            array('bouncetime', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('navigationdelay', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('template', 'filter', 'filter' => array($this, 'filterTemplateSave')),
            array('language', 'LSYii_Validators', 'isLanguage' => true),
            array('language', 'required', 'on' => 'insert'),
            array('language', 'LSYii_FilterValidator', 'filter' => 'trim', 'skipOnEmpty' => true),
            array('additional_languages', 'LSYii_FilterValidator', 'filter' => 'trim', 'skipOnEmpty' => true),
            array('additional_languages', 'LSYii_Validators', 'isLanguageMulti' => true),
            array('running', 'safe', 'on' => 'search'),
            array('expires', 'date','format' => ['yyyy-M-d H:m:s.???','yyyy-M-d H:m:s','yyyy-M-d H:m'],'allowEmpty' => true),
            array('startdate', 'date','format' => ['yyyy-M-d H:m:s.???','yyyy-M-d H:m:s','yyyy-M-d H:m'],'allowEmpty' => true),
            array('datecreated', 'date','format' => ['yyyy-M-d H:m:s.???','yyyy-M-d H:m:s','yyyy-M-d H:m'],'allowEmpty' => true),
            array('expires', 'checkExpireAfterStart'),
            // The Google Analytics Tracking ID is inserted in a JS script. If the following rule is changed, make sure
            // that it doesn't render it vulnerable to XSS attacks.
            array('googleanalyticsapikey', 'match', 'pattern' => '/^[a-zA-Z\-\d]*$/',
                'message' => gT('Google Analytics Tracking ID may only contain alphanumeric characters and hyphens.'),
            ),
        );
    }


    /**
     * afterFindSurvey to fix and/or add some survey attribute
     * - event afterFindSurvey (for all attributes)
     * - Fix template name to be sure template exist
     * - setOptions for inherited value
     */
    public function afterFindSurvey()
    {
        $event = new PluginEvent('afterFindSurvey');
        $event->set('surveyid', $this->sid);
        App()->getPluginManager()->dispatchEvent($event);
        $aAttributes = array_keys($this->getAttributes());
        foreach ($aAttributes as $attribute) {
            if (!is_null($event->get($attribute))) {
                $this->setAttribute($attribute, $event->get($attribute));
            }
        }
        if ($this->template != 'inherit') {
            $this->template = Template::templateNameFilter($this->template);
        }
        /* this is fixed, setOptions for inherited after all */
        $this->setOptions($this->gsid);
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
                $sTemplateName = 'inherit';
            }
        }
        if ($sTemplateName == 'inherit') {
            return $sTemplateName;
        } else {
            return Template::templateNameFilter($sTemplateName);
        }
    }

    /**
     * permission scope for this model
     * Actually only test if user have minimal access to survey (read)
     * @see issue https://bugs.limesurvey.org/view.php?id=16799
     * @access public
     * @param int $loginID
     * @return CActiveRecord
     */
    public function permission($loginID)
    {
        $loginID = (int) $loginID;
        $criteria = $this->getDBCriteria();
        $criteriaPerm = self::getPermissionCriteria($loginID);
        $criteria->mergeWith($criteriaPerm, 'AND');
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
        if (is_null($this->additional_languages)) {
            return [];
        }
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
        $attdescriptiondata = decodeTokenAttributes($this->attributedescriptions ?? '');
        if (!is_array(reset($attdescriptiondata))) {
            $attdescriptiondata = null;
        }
        // Catches malformed data
        if ($attdescriptiondata && strpos((string) key(reset($attdescriptiondata)), 'attribute_') === false) {
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
                        'encrypted' => 'N',
                        'show_register' => 'N',
                        'cpdbmap' => ''
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
        if ($this->getHasTokensTable()) {
            $allKnowAttributes = array_intersect_key(
                ( $attdescriptiondata + Token::model($this->sid)->getAttributes()),
                Token::model($this->sid)->getAttributes()
            );
            // We remove deleted attribute even if deleted manually in DB
        }
        $aCompleteData = array();
        foreach ($allKnowAttributes as $sKey => $aValues) {
            if (preg_match("/^attribute_[0-9]{1,}$/", (string) $sKey)) { // Select only extra attributes here
                if (!is_array($aValues)) {
                    $aValues = array();
                }
                // merge default with attributedescriptions
                $aCompleteData[$sKey] = array_merge(array(
                    'description' => $sKey,
                    'mandatory' => 'N',
                    'encrypted' => 'N',
                    'show_register' => 'N',
                    'cpdbmap' => ''
                ), $aValues);
            }
        }
        return $aCompleteData;
    }

    /**
     * This function returns any valid mappings from the survey participant lists to the CPDB
     * in the form of an array [<cpdb_attribute_id>=><participant_table_attribute_name>]
     *
     * @return array Array of mappings
     */
    public function getCPDBMappings()
    {
        $mappings = [];
        foreach ($this->getTokenAttributes() as $name => $attribute) {
            if ($attribute['cpdbmap'] != '') {
                if (ParticipantAttributeName::model()->findByPk($attribute['cpdbmap'])) {
                    $mappings[$attribute['cpdbmap']] = $name;
                }
            }
        }
        return $mappings;
    }

    /**
     * Return the name of survey tokens table
     * @return string
     */
    public function getTokensTableName()
    {
        return "{{tokens_" . $this->primaryKey . "}}";
    }

    /**
     * Return the name of survey timigs table
     * @return string
     */
    public function getTimingsTableName()
    {
        return "{{survey_" . $this->primaryKey . "_timings}}";
    }

    /**
     * Return the name of survey responses (the data) table name
     * @return string
     */
    public function getResponsesTableName()
    {
        return '{{survey_' . $this->primaryKey . '}}';
    }


    /**
     * Returns true in a survey participant list exists for survey
     * @return boolean
     */
    public function getHasTokensTable()
    {
        // Make sure common_helper is loaded
        Yii::import('application.helpers.common_helper', true);
        return tableExists($this->tokensTableName);
    }

    /**
     * Wheteher the survey responses (data) table exists in DB
     * @return boolean
     */
    public function getHasResponsesTable()
    {
        // Make sure common_helper is loaded
        Yii::import('application.helpers.common_helper', true);
        return tableExists($this->responsesTableName);
    }

    /**
     * Wheteher the survey responses timings exists in DB
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
        } elseif ($this->googleanalyticsapikey == "") {
            return "N";
        } else {
            return "Y";
        }
    }

    /**
     * Sets Google Analytics API Key Setting.
     *
     * @param string $value Google Analytics Key
     *
     * @return void
     */
    public function setGoogleanalyticsapikeysetting($value)
    {
        if ($value == "G") {
            $this->googleanalyticsapikey = "9999useGlobal9999";
        } elseif ($value == "N") {
            $this->googleanalyticsapikey = "";
        }
    }

    /**
     * Returns the value for the SurveyEdit GoogleAnalytics API-Key UseGlobal Setting
     * @return string
     */
    public function getGoogleanalyticsapikey()
    {
        $key = null;
        if ($this->googleanalyticsapikey === "9999useGlobal9999") {
            $key = trim((string) Yii::app()->getConfig('googleanalyticsapikey'));
        } else {
            $key = trim((string) $this->googleanalyticsapikey);
        }
        return sanitize_alphanumeric($key);
    }

    /**
     * Returns Survey Template Configuration.
     *
     * @return TemplateConfiguration
     */
    public function getSurveyTemplateConfiguration()
    {
        return TemplateConfiguration::getInstance(null, null, $this->sid);
    }

    /**
     * Returns the name of the template to be used for the survey.
     * It resolves inheritance from group and from default settings.
     *
     * @return string
     *
     * @todo:  Cache this on a private attribute?
     */
    public function getTemplateEffectiveName()
    {
        // Fetch template name from model
        // This was already filtered on afterFind, so if the one at load time is not valid, will be replaced by default one
        // If it is "inherit", means it will inherit from group, so we will replace it.
        $sTemplateName = $this->template;

        // if it is "inherit", get template name form group
        if ($sTemplateName == 'inherit') {
            if (!empty($this->oOptions->template)) {
                $sTemplateName = $this->oOptions->template;
            } else {
                throw new CException("Unable to get a template name from group for survey {$this->sid}");
            }
        }

        return $sTemplateName;
    }

    /**
     * Get surveymenu configuration from table surveymenu and prepares
     *
     * @todo this function can go directly into Surveymenu, why implemted it here? ($this is used here ...)
     * This will be made bigger in future releases, but right now it only collects the default menu-entries
     *
     * @param string $position Position
     *
     * @return array
     */
    public function getSurveyMenus($position = '')
    {
        $collapsed = $position === 'collapsed';
        //Get the default menus
        $aDefaultSurveyMenus = Surveymenu::model()->getDefaultSurveyMenus($position, $this);
        //get all survey specific menus
        $aThisSurveyMenues = Surveymenu::model()->createSurveymenuArray(
            $this->surveymenus,
            $collapsed,
            $this,
            $position
        );

        return $aDefaultSurveyMenus + $aThisSurveyMenues;
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
        if (isset($aData['wishSID'])) {
            $aData['sid'] = $aData['wishSID'];
            unset($aData['wishSID']);
        }
        if (empty($aData['sid'])) {
            $aData['sid'] = intval(randomChars(6, '123456789'));
        }
        $survey = new self();
        /* Remove NULL value (default for not submitted data ) : insert must leave default if not set in POST */
        $aData = array_filter($aData, function ($value) {
            return !is_null($value);
        });
        foreach ($aData as $k => $v) {
            $survey->$k = $v;
        }

        $attempts = 0;
        /* Validate sid : > 1 and unique */
        while (!$survey->validate(array('sid'))) {
            $attempts++;
            $survey->sid = intval(randomChars(6, '123456789'));
            /* If it's happen : there are an issue in server … (or in randomChars function …) */
            if ($attempts > 50) {
                throw new Exception("Unable to get a valid survey ID after 50 attempts");
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
        /** @var self $model */
        if (empty($condition) && empty($params)) {
            if (array_key_exists($pk, self::$findByPkCache)) {
                return self::$findByPkCache[$pk];
            } else {
                $model = parent::findByPk($pk, $condition, $params);
                if (!is_null($model)) {
                    self::$findByPkCache[$pk] = $model;
                }
                return $model;
            }
        }
        $model = parent::findByPk($pk, $condition, $params);
        return $model;
    }

    /**
     * findByPk uses a cache to store a result. Use this method to force clearing that cache.
     */
    public function resetCache(): void
    {
        self::$findByPkCache = [];
    }

    /**
     * Attribute renamed to questionindex in dbversion 169
     * Y maps to 1 otherwise 0;
     * @param string $value
     */
    public function setAllowjumps($value)
    {
        if ($value === 'Y') {
            $this->oOptions->questionindex = 1;
        } else {
            $this->oOptions->questionindex = 0;
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
        return ($this->oOptions->anonymized == 'Y') ? gT('Yes') : gT('No');
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
        if (!empty($this->expires) || !empty($this->startdate)) {
            // Create DateTime for now, stop and start for date comparison
            $oNow = self::shiftedDateTime("now");
            $oStop = self::shiftedDateTime($this->expires);
            $oStart = self::shiftedDateTime($this->startdate);
            $bExpired = (!is_null($oStop) && $oStop < $oNow);
            $bWillRun = (!is_null($oStart) && $oStart > $oNow);

            if ($bExpired) {
                return 'expired';
            }
            if ($bWillRun) {
                // And what happen if $sStop < $sStart : must return something other ?
                return 'willRun';
            }
            if (!is_null($oStop)) {
                return 'willExpire';
            }
        }
        // No returned before : it's running
        return 'running';
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function getIsDateExpired()
    {
        if (!empty($this->expires)) {
            $oNow = self::shiftedDateTime("now");
            $oStop = self::shiftedDateTime($this->expires);
            return !empty($oStop) && $oStop < $oNow;
        }
        return false;
    }


    /**
     * Returns the status of the survey, including and icon and wrapped by a link to the survey
     * @return string
     * @throws Exception
     */
    public function getRunning()
    {
            $onclick = App()->getConfig('editorEnabled')
                ? ' onclick="return  false;" '
                : '';

        // If the survey is not active, no date test is needed
        if ($this->active === 'N') {
            $running = '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $this->sid) . '"' . $onclick . ' class="survey-state disabled" data-bs-toggle="tooltip" title="' . gT('Inactive') . '"><i class="ri-stop-fill text-secondary"></i>' . gT('Inactive') . '</a>';
        } elseif (!empty($this->expires) || !empty($this->startdate)) {
            // Create DateTime for now, stop and start for date comparison
            $oNow = self::shiftedDateTime("now");
            $oStop = self::shiftedDateTime($this->expires);
            $oStart = self::shiftedDateTime($this->startdate);

            $bExpired = (!is_null($oStop) && $oStop < $oNow);
            $bWillRun = (!is_null($oStart) && $oStart > $oNow);

            $sStop = !is_null($oStop) ? convertToGlobalSettingFormat($oStop->format('Y-m-d H:i:s')) : "";
            $sStart = !is_null($oStart) ? convertToGlobalSettingFormat($oStart->format('Y-m-d H:i:s')) : "";

            // Icon generaton (for CGridView)
            $sIconRunNoEx = '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $this->sid) . '"' . $onclick . ' class="survey-state" data-bs-toggle="tooltip" title="' . gT('End: Never') . '"><i class="ri-play-fill text-primary"></i>' . gT('End: Never') . '</a>';
            $sIconRunning = '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $this->sid) . '"' . $onclick . ' class="survey-state" data-bs-toggle="tooltip" title="' . sprintf(gT('End: %s'), $sStop) . '"><i class="ri-play-fill text-primary"></i>' . sprintf(gT('End: %s'), $sStop) . '</a>';
            $sIconExpired = '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $this->sid) . '"' . $onclick . ' class="survey-state disabled" data-bs-toggle="tooltip" title="' . sprintf(gT('Expired: %s'), $sStop) . '"><i class="ri-skip-forward-fill text-secondary"></i>' . sprintf(gT('Expired: %s'), $sStop) . '</a>';
            $sIconFuture  = '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $this->sid) . '"' . $onclick . ' class="survey-state" data-bs-toggle="tooltip" title="' . sprintf(gT('Start: %s'), $sStart) . '"><i class="ri-time-line text-secondary"></i>' . sprintf(gT('Start: %s'), $sStart) . '</a>';

            // Icon parsing
            if ($bExpired || $bWillRun) {
                // Expire prior to will start
                $running = ($bExpired) ? $sIconExpired : $sIconFuture;
            } else {
                if ($sStop === "") {
                    $running = $sIconRunNoEx;
                } else {
                    $running = $sIconRunning;
                }
            }
        } else {
            // If it's active, and doesn't have expire date, it's running
            $running = '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $this->sid) . '"' . $onclick . ' class="survey-state" data-bs-toggle="tooltip" title="' . gT('Active') . '"><i class="ri-play-fill text-primary"></i>' . gT('Active') . '</a>';
        }

        return $running;
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
        return ($this->oOptions->anonymized === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsSaveTimings()
    {
        return ($this->oOptions->savetimings === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsDateStamp()
    {
        return ($this->oOptions->datestamp === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsUseCookie()
    {
        return ($this->oOptions->usecookie === 'Y');
    }

    /**
     * @return bool
     */
    public function getIsAllowRegister()
    {
        return ($this->oOptions->allowregister === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsAllowSave()
    {
        return ($this->oOptions->allowsave === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsAutoRedirect()
    {
        return ($this->oOptions->autoredirect === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsAllowPrev()
    {
        return ($this->oOptions->allowprev === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsPrintAnswers()
    {
        return ($this->oOptions->printanswers === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsIpAddr()
    {
        return ($this->oOptions->ipaddr === 'Y');
    }

    /**
     * @return bool
     */
    public function getIsIpAnonymize()
    {
        return ($this->oOptions->ipanonymize === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsRefUrl()
    {
        return ($this->oOptions->refurl === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsPublicStatistics()
    {
        return ($this->oOptions->publicstatistics === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsPublicGraphs()
    {
        return ($this->oOptions->publicgraphs === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsListPublic()
    {
        return ($this->oOptions->listpublic === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsHtmlEmail()
    {
        return ($this->oOptions->htmlemail === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsSendConfirmation()
    {
        return ($this->oOptions->sendconfirmation === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsTokenAnswersPersistence()
    {
        return ($this->oOptions->tokenanswerspersistence === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsAssessments()
    {
        return ($this->oOptions->assessments === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsShowXQuestions()
    {
        return ($this->oOptions->showxquestions === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsShowGroupInfo()
    {
        return ($this->oOptions->showgroupinfo === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsShowNoAnswer()
    {
        return ($this->oOptions->shownoanswer === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsShowQnumCode()
    {
        return ($this->oOptions->showqnumcode === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsShowWelcome()
    {
        return ($this->oOptions->showwelcome === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsShowProgress()
    {
        return ($this->oOptions->showprogress === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsNoKeyboard()
    {
        return ($this->oOptions->nokeyboard === 'Y');
    }
    /**
     * @return bool
     */
    public function getIsAllowEditAfterCompletion()
    {
        return ($this->oOptions->alloweditaftercompletion === 'Y');
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
     * @return int|string
     */
    public function getCountFullAnswers()
    {
        $sResponseTable = $this->responsesTableName;
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
        return decodeTokenAttributes($this->attributedescriptions ?? '');
    }

    /**
     * @return int
     */
    public function getCountTotalAnswers()
    {
        $table = $this->responsesTableName;
        if ($this->active != 'Y') {
            return 0;
        } else {
            $answers = Yii::app()->db->createCommand()
                ->select('count(*)')
                ->from($table)
                ->queryScalar();
            return $answers;
        }
    }

    /**
     * Returns buttons for gridview.
     * @return string
     * @throws CException
     * @throws Exception
     */
    public function getButtons(): string
    {
        $permissions = [
            'statistics_read'  => Permission::model()->hasSurveyPermission($this->sid, 'statistics', 'read'),
            'survey_update'    => Permission::model()->hasSurveyPermission($this->sid, 'survey', 'update'),
            'responses_create' => Permission::model()->hasSurveyPermission($this->sid, 'responses', 'create'),
        ];

        $dropdownItems = [];
        $dropdownItems[] = [
            'title' => gT('General settings'),
            'url' => App()->getConfig('editorEnabled')
                ? App()->createUrl('editorLink/index', ['route' => 'survey/' . $this->sid . '/settings/generalsettings'])
                : App()->createUrl('surveyAdministration/rendersidemenulink/subaction/generalsettings', ['surveyid' => $this->sid]),
            'enabledCondition' => $permissions['survey_update'],
        ];
        $dropdownItems[] = [
            'title'            => gT('Preview'),
            'url'              => Yii::App()->createUrl(
                "survey/index",
                ['sid' => $this->sid, 'newtest' => "Y", 'lang' => $this->language]
            ),
            'enabledCondition' => $permissions['survey_update'],
            'linkAttributes'   => ['target' => '_blank'],
        ];
        $dropdownItems[] = [
            'title' => gT('Share'),
            'url' => App()->createUrl("/surveyAdministration/view", array('iSurveyID' => $this->sid)),
            'enabledCondition' => $permissions['survey_update'],
        ];
        $dropdownItems[] = [
            'title' => gT('Quick copy'),
            'url' => App()->createUrl("/surveyAdministration/copySimple", ['surveyIdToCopy' => $this->sid]),
            'enabledCondition' => $permissions['survey_update'],
        ];
        $dropdownItems[] = [
            'title' => gT('Custom copy'),
            'linkAttributes'   => [
                'data-bs-toggle' => "modal",
                'data-bs-target' => "#copySurvey_modal",
                'onclick' => "copySurveyOptions($this->sid)",
            ],
            'enabledCondition' => $permissions['survey_update'],
        ];
        $dropdownItems[] = [
            'title' => gT('Add user'),
            'url' => App()->createUrl("/userManagement"),
            'enabledCondition' => $permissions['survey_update'],
        ];

        $dropdownItems[] = [
            'title' => gT('Delete'),
            'url' => App()->createUrl("/surveyAdministration/delete", array('iSurveyID' => $this->sid)),
            'enabledCondition' => $permissions['survey_update'],
        ];

        return App()->getController()->widget('ext.admin.grid.GridActionsWidget.GridActionsWidget', ['dropdownItems' => $dropdownItems], true);
    }

    /**
     * Returns buttons for gridview.
     * @return string
     * @throws CException
     * @throws Exception
     */
    public function getActionButtons(): string
    {
        $permissions = [
            'statistics_read'  => Permission::model()->hasSurveyPermission($this->sid, 'statistics', 'read'),
            'survey_update'    => Permission::model()->hasSurveyPermission($this->sid, 'survey', 'update'),
            'responses_create' => Permission::model()->hasSurveyPermission($this->sid, 'responses', 'create'),
        ];

        $items = [];
        $items[] = [
            'title' => gT('Edit survey'),
            'url' => App()->createUrl('surveyAdministration/view', ['iSurveyID' => $this->sid]),
            'iconClass' => 'ri-edit-line',
            'enabledCondition' => $this->active !== "Y" && $permissions['responses_create']
        ];

        $items[] = [
            'title' => gT('Activate'),
            'url' => App()->createUrl('surveyAdministration/rendersidemenulink/subaction/generalsettings', ['surveyid' => $this->sid]),
            'iconClass' => 'ri-check-line',
            'enabledCondition' =>
                $this->active === "N"
                && $permissions['survey_update']
                && $this->groupsCount > 0
                && $this->getQuestionsCount() > 0
        ];
        $items[] = [
            'title' => gT('Statistics'),
            'url' => App()->createUrl('admin/statistics/sa/simpleStatistics', ['surveyid' => $this->sid]),
            'iconClass' => 'ri-bar-chart-2-line',
            'enabledCondition' =>
                $this->active === "Y"
                && $permissions['statistics_read'],
        ];

        return App()->getController()->widget('ext.admin.grid.BarActionsWidget.BarActionsWidget', ['items' => $items], true);
    }

    public function getColumns(): array
    {
        $columns = [
            [
                'id'                => 'sid',
                'class'             => 'CCheckBoxColumn',
                'selectableRows'    => '100',
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'ls-sticky-column']
            ],
            [
                'header'            => gT('Survey ID'),
                'name'              => 'survey_id',
                'value'             => 'CHtml::link($data->sid, Yii::app()->createUrl("surveyAdministration/view", ["surveyid" => $data->sid]))',
                'type'              => 'raw',
                'headerHtmlOptions' => ['class' => 'd-none d-sm-table-cell text-nowrap'],
                'htmlOptions'       => ['class' => 'd-none d-sm-table-cell has-link'],
            ],
            [
                'header'            => gT('Status'),
                'name'              => 'running',
                'value'             => '$data->running',
                'type'              => 'raw',
                'headerHtmlOptions' => ['class' => 'd-none d-sm-table-cell text-nowrap'],
                'htmlOptions'       => ['class' => 'd-none d-sm-table-cell has-link'],
            ],
            [
                'header'            => gT('Title'),
                'name'              => 'title',
                'value'             => 'isset($data->defaultlanguage) ? CHtml::link(flattenText($data->defaultlanguage->surveyls_title), Yii::app()->createUrl("surveyAdministration/view", ["surveyid" => $data->sid])) : ""',
                'type'              => 'raw',
                'htmlOptions'       => ['class' => 'has-link'],
                'headerHtmlOptions' => ['class' => 'text-nowrap'],
            ],
            [
                'header'            => gT('Created'),
                'name'              => 'creation_date',
                'value'             => '$data->creationdate',
                'headerHtmlOptions' => ['class' => 'd-none d-sm-table-cell text-nowrap'],
                'htmlOptions'       => ['class' => 'd-none d-sm-table-cell has-link'],
            ],
            [
                'header'            => gT('Responses'),
                'name'              => 'responses',
                'value'             => '$data->countFullAnswers',
                'headerHtmlOptions' => ['class' => 'd-md-none d-lg-table-cell'],
                'htmlOptions'       => ['class' => 'd-md-none d-lg-table-cell has-link'],
            ],
            [
                'header' => gT('Action'),
                'name'   => 'actions',
                'value'  => '$data->actionButtons',
                'type'   => 'raw'
            ]
        ];
        return $columns;
    }
    public function getAdditionalColumns(): array
    {
        $additionalColumns = [
            'group' => [
                'header'            => gT('Group'),
                'name'              => 'group',
                'value'             => '$data->surveygroup->title',
                'htmlOptions'       => ['class' => 'has-link'],
                'headerHtmlOptions' => ['class' => 'text-nowrap'],
            ],
            'owner' => [
                'header'            => gT('Owner'),
                'name'              => 'owner',
                'value'             => '$data->ownerUserName',
                'headerHtmlOptions' => ['class' => 'd-md-none d-xl-table-cell text-nowrap'],
                'htmlOptions'       => ['class' => 'd-md-none d-xl-table-cell has-link'],
            ],
            'anonymized_responses' => [
                'header'            => gT('Anonymized responses'),
                'name'              => 'anonymized_responses',
                'value'             => '$data->anonymizedResponses',
                'headerHtmlOptions' => ['class' => 'd-md-none d-lg-table-cell'],
                'htmlOptions'       => ['class' => 'd-md-none d-lg-table-cell has-link'],
            ],
            'partial' => [
                'header'      => gT('Partial'),
                'value'       => '$data->countPartialAnswers',
                'name'        => 'partial',
                'htmlOptions' => ['class' => 'has-link'],
            ],
            'full' => [
                'header'      => gT('Full'),
                'name'        => 'full',
                'value'       => '$data->countFullAnswers',
                'htmlOptions' => ['class' => 'has-link'],
            ],
            'total' => [
                'header'      => gT('Total'),
                'name'        => 'total',
                'value'       => '$data->countTotalAnswers',
                'htmlOptions' => ['class' => 'has-link'],
            ],
            'uses_tokens' => [
                'header'      => gT('Closed group'),
                'name'        => 'uses_tokens',
                'type'        => 'raw',
                'value'       => '$data->hasTokensTable ? gT("Yes"):gT("No")',
                'htmlOptions' => ['class' => 'has-link'],
            ]
        ];

        return $additionalColumns;
    }

    /**
     * Search
     *
     * $options = [
     *  'pageSize' => 10,
     *  'currentPage' => 1
     * ];
     *
     * @param array $options
     * @return CActiveDataProvider
     */
    public function search($options = [])
    {
        $options = $options ?? [];
        // Flush cache to get proper counts for partial/complete/total responses
        if (method_exists(Yii::app()->cache, 'flush')) {
            Yii::app()->cache->flush();
        }
        $pagination = [
            'pageSize' => Yii::app()->user->getState(
                'pageSize',
                Yii::app()->params['defaultPageSize']
            )
        ];
        if (isset($options['pageSize'])) {
            $pagination['pageSize'] = $options['pageSize'];
        }
        if (isset($options['currentPage'])) {
            $pagination['currentPage'] = $options['currentPage'];
        }

        $sort = new CSort();
        $sort->attributes = array(
            'survey_id' => array(
                'asc' => 't.sid asc',
                'desc' => 't.sid desc',
            ),
            'title' => array(
                'asc' => 'correct_relation_defaultlanguage.surveyls_title asc',
                'desc' => 'correct_relation_defaultlanguage.surveyls_title desc',
            ),

            'creation_date' => array(
                'asc' => 't.datecreated asc',
                'desc' => 't.datecreated desc',
            ),

            'owner' => array(
                'asc' => 'owner.users_name asc',
                'desc' => 'owner.users_name desc',
            ),

            'anonymized_responses' => array(
                'asc' => 't.anonymized asc',
                'desc' => 't.anonymized desc',
            ),

            'running' => array(
                'asc' => 't.active asc, t.expires asc',
                'desc' => 't.active desc, t.expires desc',
            ),

            'group' => array(
                'asc'  => 'surveygroup.title asc',
                'desc' => 'surveygroup.title desc',
            ),

        );
        $sort->defaultOrder = array('creation_date' => CSort::SORT_DESC);

        $criteria = new LSDbCriteria();
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
            // The survey group filter (from the dropdown, not by title search) is applied to five levels of survey groups.
            // That is, it matches the group the survey is in, the parent group of that group, and the "grandparent" group, etc.
            $groupJoins = 'LEFT JOIN {{surveys_groups}} parentGroup1 ON t.gsid = parentGroup1.gsid ';
            $groupJoins .= 'LEFT JOIN {{surveys_groups}} parentGroup2 ON parentGroup1.parent_id = parentGroup2.gsid ';
            $groupJoins .= 'LEFT JOIN {{surveys_groups}} parentGroup3 ON parentGroup2.parent_id = parentGroup3.gsid ';
            $groupJoins .= 'LEFT JOIN {{surveys_groups}} parentGroup4 ON parentGroup3.parent_id = parentGroup4.gsid ';
            $groupJoins .= 'LEFT JOIN {{surveys_groups}} parentGroup5 ON parentGroup4.parent_id = parentGroup5.gsid ';
            $criteria->mergeWith([
                'join' => $groupJoins,
            ]);
            $groupCondition = "t.gsid=:gsid";
            $groupCondition .= " OR parentGroup2.gsid=:gsid2"; // MSSQL issue with single param for multiple value, issue #19072
            $groupCondition .= " OR parentGroup3.gsid=:gsid3";
            $groupCondition .= " OR parentGroup4.gsid=:gsid4";
            $groupCondition .= " OR parentGroup5.gsid=:gsid5";
            $criteria->addCondition($groupCondition, 'AND');
            $criteria->params = array_merge(
                $criteria->params,
                [
                    ':gsid' => $this->gsid,
                    ':gsid2' => $this->gsid,
                    ':gsid3' => $this->gsid,
                    ':gsid4' => $this->gsid,
                    ':gsid5' => $this->gsid
                ]
            );
        }

        // Active filter
        if (isset($this->active)) {
            if ($this->active == 'N' || $this->active == "Y") {
                $criteria->compare("t.active", $this->active, false);
            } else {
                // Time adjust
                $sNow = date("Y-m-d H:i:s", strtotime((string) Yii::app()->getConfig('timeadjust'), strtotime(date("Y-m-d H:i:s"))));

                if ($this->active == "E") {
                    $criteria->compare("t.active", 'Y');
                    $criteria->addCondition("t.expires <'$sNow'");
                } if ($this->active == "S") {
                    $criteria->compare("t.active", 'Y');
                    $criteria->addCondition("t.startdate >'$sNow'");
                }

                // Filter for surveys that are running now
                // Must be active, started and not expired
                if ($this->active == "R") {
                    $criteria->compare("t.active", 'Y');
                    $startedCriteria = new CDbCriteria();
                    $startedCriteria->addCondition("'{$sNow}' > t.startdate");
                    $startedCriteria->addCondition('t.startdate IS NULL', "OR");
                    $notExpiredCriteria = new CDbCriteria();
                    $notExpiredCriteria->addCondition("'{$sNow}' < t.expires");
                    $notExpiredCriteria->addCondition('t.expires IS NULL', "OR");
                    $criteria->mergeWith($startedCriteria);
                    $criteria->mergeWith($notExpiredCriteria);
                }
            }
        }


        $criteria->with = $aWithRelations;

        // Permission
        $criteriaPerm = self::getPermissionCriteria();
        $criteria->mergeWith($criteriaPerm, 'AND');
        // $criteria->addCondition("t.blabla == 'blub'");
        $dataProvider = new CActiveDataProvider('Survey', array(
            'sort' => $sort,
            'criteria' => $criteria,
            'pagination' => $pagination,
        ));

        $dataProvider->setTotalItemCount($this->count($criteria));

        return $dataProvider;
    }

    /**
     * Get criteria from Permission
     * @param $userid for thius user id , if not set : get current one
     * @todo : move to PermissionInterface
     * @todo : create an event
     * @return CDbCriteria
     */
    protected static function getPermissionCriteria($userid = null)
    {
        if (!$userid) {
            $userid = App()->getCurrentUserId();
        }
        // Note: reflect Permission::hasPermission
        $criteriaPerm = new CDbCriteria();
        $criteriaPerm->params = array();
        if (!Permission::model()->hasGlobalPermission("surveys", 'read', $userid)) {
            /* it's the owner of the survey */
            $criteriaPerm->compare('t.owner_id', $userid, false);

            /* Read is set on survey */
            $criteriaPerm->mergeWith(
                array(
                    'join' => "LEFT JOIN {{permissions}} AS surveypermissions{$userid} ON (surveypermissions{$userid}.entity_id = t.sid AND surveypermissions{$userid}.permission='survey' AND surveypermissions{$userid}.entity='survey' AND surveypermissions{$userid}.uid= :surveypermissionuserid{$userid}) ",
                )
            );
            $criteriaPerm->params[":surveypermissionuserid{$userid}"] = $userid;
            $criteriaPerm->compare("surveypermissions{$userid}.read_p", '1', false, 'OR');

            /* Read on Surveys in group */
            $criteriaPerm->mergeWith(
                array(
                    'join' => "LEFT JOIN {{permissions}} AS surveysingrouppermissions{$userid} ON (surveysingrouppermissions{$userid}.entity_id = t.gsid AND surveysingrouppermissions{$userid}.entity='surveysingroup' AND surveysingrouppermissions{$userid}.uid= :surveysingrouppermissionuserid{$userid}) ",
                )
            );
            $criteriaPerm->params[":surveysingrouppermissionuserid{$userid}"] = $userid;
            $criteriaPerm->compare("surveysingrouppermissions{$userid}.read_p", '1', false, 'OR'); // This mean : update, export … didn't allow see in list

            /* Under condition : owner of group */
            if (App()->getConfig('ownerManageAllSurveysInGroup')) {
                $criteriaPerm->mergeWith(
                    array(
                        'join' => "LEFT JOIN {{surveys_groups}} AS surveysgroupsowner{$userid} ON (surveysgroupsowner{$userid}.gsid = t.gsid) ",
                    )
                );
                $criteriaPerm->compare("surveysgroupsowner{$userid}.owner_id", $userid, false, 'OR');
            }
        }
        /* Place for a new event */
        return $criteriaPerm;
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
     *
     * 'E' = All inherited
     * 'F' = Inherited save and load + survey access + registration
     * 'G' = Inherited survey access + registration + save and load
     * 'H' = Inherited registration + save and load + survey access
     * 'I' = Inherited save and load + inherited survey access + registration
     * 'J' = Inherited survey access + inherited registration + save and load
     * 'K' = Inherited registration + inherited save and load + survey access
     *
     * 'L' = Inherited survey access + save and load
     * 'M' = Inherited survey access + registration
     * 'O' = Inherited registration + survey access
     * '1' = Inherited survey access + inherited registration
     * '2' = Inherited survey access + inherited save and load
     * '3' = Inherited registration + inherited save and load
     * '4' = Inherited survey access
     * '5' = Inherited save and load
     * '6' = Inherited registration
     *
     * 'N' = None
     *
     * @return string One character that corresponds to captcha usage
     * @todo Should really be saved as three fields in the database!
     */
    public static function saveTranscribeCaptchaOptions(Survey $oSurvey = null)
    {
        $surveyaccess = App()->request->getPost('usecaptcha_surveyaccess', null);
        $registration = App()->request->getPost('usecaptcha_registration', null);
        $saveandload = App()->request->getPost('usecaptcha_saveandload', null);

        if ($surveyaccess === null && $registration === null && $saveandload === null) {
            if ($oSurvey !== null) {
                return $oSurvey->usecaptcha;
            }
        }

        $surveyUseCaptcha = new \LimeSurvey\Models\Services\SurveyUseCaptcha(0, $oSurvey);
        return $surveyUseCaptcha->convertUseCaptchaForDB($surveyaccess, $registration, $saveandload);
    }


    /**
     * Method to make an approximation on how long a survey will last
     * Approx is 3 questions each minute.
     *
     * @deprecated Unused since 3.X
     * @return double
     */
    public function calculateEstimatedTime()
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
     * Get all surveys that has participant list
     * @return Survey[]
     */
    public static function getSurveysWithTokenTable()
    {
        $surveys = self::model()->with(array('languagesettings' => array('condition' => 'surveyls_language=language'), 'owner'))->findAll();
        $surveys = array_filter($surveys, function ($s) {
            return $s->hasTokensTable;
        });
        return $surveys;
    }

    /**
     * Fix invalid question in this survey
     * Delete question that don't exist in primary language
     */
    public function fixInvalidQuestions()
    {
        /* Delete invalid questions (don't exist in primary language) using qid like column name*/
        $validQuestion = Question::model()->findAll(array(
            'select' => 'qid',
            'condition' => 'sid=:sid AND parent_qid = 0',
            'params' => array('sid' => $this->sid)
        ));
        $criteria = new CDbCriteria();
        $criteria->compare('sid', $this->sid);
        $criteria->addCondition('parent_qid = 0');
        $criteria->addNotInCondition('qid', CHtml::listData($validQuestion, 'qid', 'qid'));
        Question::model()->deleteAll($criteria); // Must log count of deleted ?

        /* Delete invalid Sub questions (don't exist in primary language) using title like column name*/
        $validSubQuestion = Question::model()->findAll(array(
            'select' => 'title',
            'condition' => 'sid=:sid AND parent_qid != 0',
            'params' => array('sid' => $this->sid)
        ));
        $criteria = new CDbCriteria();
        $criteria->compare('sid', $this->sid);
        $criteria->addCondition('parent_qid != 0');
        $criteria->addNotInCondition('title', CHtml::listData($validSubQuestion, 'title', 'title'));
        Question::model()->deleteAll($criteria); // Must log count of deleted ?
    }

    /**
     * TODO: Not used anywhere. Deprecate it?
     */
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
            'parent_qid' => 0,
        ));
        $criteria->addInCondition('t.type', Question::getQuotableTypes());
        /** @var Question[] $questions */
        $questions = Question::model()->with('questionl10ns')->findAll($criteria);
        return $questions;
    }

    /**
     * @return Question[]
     */
    public function getAllQuestions()
    {
        $criteria = $this->getSurveyQuestionsCriteria();
        /** @var Question[] $questions */
        $questions = Question::model()->findAll($criteria);
        return $questions;
    }

    /**
     * @return Question[]
     */
    public function getBaseQuestions()
    {
        $criteria = $this->getSurveyQuestionsCriteria();
        $criteria->addColumnCondition(array(
            'parent_qid' => 0,
        ));

        /** @var Question[] $questions */
        $questions = Question::model()->findAll($criteria);
        return $questions;
    }

    private function getSurveyQuestionsCriteria()
    {
        $criteria = $this->getQuestionOrderCriteria();
        $criteria->addColumnCondition(array(
            't.sid' => $this->sid,
        ));
        return $criteria;
    }

    /**
     * Get the DB criteria to get questions as ordered in survey
     * @return CDbCriteria
     */
    private function getQuestionOrderCriteria()
    {
        $criteria = new CDbCriteria();
        $criteria->select = Yii::app()->db->quoteColumnName('t.*');
        $criteria->with = array(
            'survey.groups',
        );
        if (Yii::app()->db->driverName == 'sqlsrv' || Yii::app()->db->driverName == 'dblib') {
            $criteria->order = Yii::app()->db->quoteColumnName('t.question_order');
        } else {
            $criteria->order = Yii::app()->db->quoteColumnName('groups.group_order') . ',' . Yii::app()->db->quoteColumnName('t.question_order');
        }
        $criteria->addCondition('groups.gid=t.gid', 'AND');
        return $criteria;
    }

    /**
     * Gets number of groups inside a particular survey
     */
    public function getGroupsCount()
    {
        return QuestionGroup::model()->countByAttributes(['sid' => $this->sid]);
    }

    /**
     * Gets number of Questions inside a particular survey
     */
    public function getQuestionsCount()
    {
        return Question::model()->countByAttributes(['sid' => $this->sid]);
    }

    /**
     * @param boolean $countHidden determines whether to count hidden questions or not.
     * @return int
     */
    public function getCountTotalQuestions($countHidden = true)
    {
        $sumresult = null;

        if ($countHidden) {
            $condn = array('sid' => $this->sid, 'parent_qid' => 0);
            $sumresult = Question::model()->countByAttributes($condn);
        } else {
            $query = Yii::app()->db->createCommand()
                ->select('COUNT(DISTINCT t.qid) as count')
                ->from('{{questions}} t')
                ->leftJoin('{{question_attributes}} qa', 'qa.qid = t.qid AND qa.attribute = :hidden', [':hidden' => 'hidden'])
                ->where('t.sid = :sid AND t.parent_qid = 0', [':sid' => $this->sid])
                ->andWhere('qa.value IS NULL OR qa.value != :hidden_value', [':hidden_value' => '1']);
            $result = $query->queryScalar();
            return (int) $result;
        }

        return (int) $sumresult;
    }

    /**
     * Get the coutn of questions that do not need input (skipping text-display etc.)
     * @return int
     */
    public function getCountNoInputQuestions()
    {
        $condn = array(
            'sid' => $this->sid,
            'parent_qid' => 0,
            'type' => ['X', '*'],
        );
        $sumresult = Question::model()->countByAttributes($condn);
        return (int) $sumresult;
    }

    /**
     * Get the coutn of questions that need input (skipping text-display etc.)
     * @return int
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

    /**
     * Get the final label for survey ID
     * @param string $dataSecurityNoticeLabel current label
     * @param integer $surveyId unused
     * @return string
     */
    public static function replacePolicyLink($dataSecurityNoticeLabel, $surveyId)
    {
        /* @var string[] to go to automatic translation */
        $translation = [
            gT("Show policy")
        ];
        return App()->twigRenderer->renderPartial(
            '/subviews/privacy/privacy_datasecurity_notice_label.twig',
            [
                'dataSecurityNoticeLabel' => $dataSecurityNoticeLabel,
                'sid' => $surveyId,
            ]
        );
    }

    /**
     * @param string $type Question->type
     * @param bool $includeSubquestions
     * @return Question
     */
    public function findQuestionByType($type, $includeSubquestions = false)
    {
        $criteria = $this->getSurveyQuestionsCriteria();
        if ($includeSubquestions) {
            $criteria->addColumnCondition(['parent_qid' => 0]);
        }
        $criteria->addColumnCondition(['type' => $type]);
        return Question::model()->find($criteria);
    }

    /**
     * decodes the tokenencryptionoptions to be used anywhere necessary
     * @return Array
     */
    public function getTokenEncryptionOptions()
    {
        $aOptions = json_decode_ls($this->tokenencryptionoptions);
        if (empty($aOptions)) {
            $aOptions = Token::getDefaultEncryptionOptions();
        }
        return $aOptions;
    }

    /**
     * @param array $tmp
     */
    public function setTokenEncryptionOptions($options)
    {
        $this->tokenencryptionoptions = $options;
    }

    public function setOptions($gsid = 1)
    {
        $instance = SurveysGroupsettings::getInstance($gsid, $this, null, 1, $this->bShowRealOptionValues);
        if ($instance) {
            $this->oOptions = $instance->oOptions;
            $this->oOptionLabels = $instance->oOptionLabels;
            $this->aOptions = (array) $instance->oOptions;
            $this->showInherited = $instance->showInherited;
        }
    }

    public function setOptionsFromDatabase()
    {
        // set real survey options with inheritance
        $this->bShowRealOptionValues = false;
        $this->setOptions($this->gsid);
    }

    public function setToInherit()
    {
        $settings = new SurveysGroupsettings();
        $settings->setToInherit();
        // set Survey attributes to 'inherit' values
        foreach ($settings as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @return string
     */
    public function getOwnerUserName()
    {
        return $this->owner["users_name"] ?? "";
    }

    /**
     * Get the owner id of this Survey
     * Used for Permission
     * @return integer
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * @inheritdoc
     * @todo use it in surveyspermission
     */
    public static function getMinimalPermissionRead()
    {
        return 'survey';
    }

    /**
     * Get Permission data for Survey
     * @return array
     */
    public static function getPermissionData()
    {
        $aPermission = array(
            'assessments' => array(
                'import' => false,
                'export' => false,
                'title' => gT("Assessments"),
                'description' => gT("Permission to create, view, update, delete assessments rules for a survey"),
                'img' => ' ri-chat-3-fill',
            ),
            'quotas' => array(
                'import' => false,
                'export' => false,
                'title' => gT("Quotas"),
                'description' => gT("Permission to create, view, update, delete quota rules for a survey"),
                'img' => 'ri-bar-chart-horizontal-fill',
            ),
            'responses' => array(
                'title' => gT("Responses"),
                'description' => gT("Permission to create(data entry), view, update, delete, import, export responses"),
                'img' => ' ri-window-fill',
            ),
            'statistics' => array(
                'create' => false,
                'update' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Statistics"),
                'description' => gT("Permission to view statistics"),
                'img' => ' ri-bar-chart-fill',
            ),
            'survey' => array(
                'create' => false,
                'read' => true, /* Minimal : forced to true when edit */
                'update' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Survey"),
                'description' => gT("Permission for survey access. Read permission is a requirement to give any further permission to a survey."),
                'img' => ' ri-list-check',
            ),
            'surveyactivation' => array(
                'create' => false,
                'read' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Survey activation"),
                'description' => gT("Permission to activate, deactivate a survey"),
                'img' => ' ri-play-fill',
            ),
            'surveycontent' => array(
                'title' => gT("Survey content"),
                'description' => gT("Permission to create, view, update, delete, import, export the questions, groups, answers & conditions of a survey"),
                'img' => ' ri-file-text-line',
            ),
            'surveylocale' => array(
                'create' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Survey text elements"),
                'description' => gT("Permission to view, update the survey text elements, e.g. survey title, survey description, welcome and end message"),
                'img' => ' ri-file-edit-line',
            ),
            'surveysecurity' => array(
                'import' => false,
                'export' => false,
                'title' => gT("Survey security"),
                'description' => gT("Permission to modify survey security settings"),
                'img' => ' ri-shield-check-fill',
            ),
            'surveysettings' => array(
                'create' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Survey settings"),
                'description' => gT("Permission to view, update the survey settings including survey participant list creation"),
                'img' => ' ri-settings-5-fill',
            ),
            'tokens' => array(
                'title' => gT("Participants"), 'description' => gT("Permission to create, update, delete, import, export participants"),
                'img' => ' ri-group-fill',
            ),
            'translations' => array(
                'create' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Quick translation"),
                'description' => gT("Permission to view & update the translations using the quick-translation feature"),
                'img' => ' ri-global-line',
            ),
        );

        return $aPermission;
    }

    /**
     * @inheritdoc
     */
    public function hasPermission($sPermission, $sCRUD = 'read', $iUserID = null)
    {
        $sGlobalCRUD = $sCRUD;
        if (($sCRUD == 'create' || $sCRUD == 'import')) { // Create and import (token, response , question content …) need only allow update surveys
            $sGlobalCRUD = 'update';
        }
        if (($sCRUD == 'delete' && $sPermission != 'survey')) { // Delete (token, response , question content …) need only allow update surveys
            $sGlobalCRUD = 'update';
        }
        /* Global */
        if (Permission::model()->hasPermission(0, 'global', 'surveys', $sGlobalCRUD, $iUserID)) {
            return true;
        }
        /* Inherited by SurveysInGroup */
        $sig = SurveysInGroup::model()->findByPk($this->gsid);
        if ($sig && $sig->hasPermission('surveys', $sGlobalCRUD, $iUserID)) {
            return true;
        }
        return Permission::model()->hasPermission($this->getPrimaryKey(), 'survey', $sPermission, $sCRUD, $iUserID);
    }

    /*
     * Find all public surveys
     * @return Survey[]
     */
    public function findAllPublic()
    {
        $oCriteria = new CDbCriteria();
        $oCriteria->condition = "listpublic = 'Y' or listpublic = 'I'";
        $aSurveys = $this->findAll($oCriteria);
        $aSurveys = array_filter(
            $aSurveys,
            function ($s) {
                return $s->isListPublic;
            }
        );
        return $aSurveys;
    }

    /**
     * Returns the survey URL with the specified params.
     * If $preferShortUrl is true (default), and an alias is available, it returns the short
     * version of the URL.
     * @param string|null $language
     * @param array|string|mixed $params   Optional parameters to include in the URL.
     * @param bool $preferShortUrl  If true, tries to return the short URL instead of the traditional one.
     * @return string
     */
    public function getSurveyUrl($language = null, $params = [], $preferShortUrl = true)
    {
        if (empty($language)) {
            $language = $this->language;
        }
        if ($preferShortUrl) {
            $alias = $this->getAliasForLanguage($language);

            if (!empty($alias)) {
                // Check if there is other language with the same alias. If it does, we need to include the 'lang' parameter in the URL.
                foreach ($this->languagesettings as $otherLang => $settings) {
                    if ($otherLang == $language || empty($settings->surveyls_alias)) {
                        continue;
                    }
                    if ($settings->surveyls_alias == $alias) {
                        $params['lang'] = $language;
                        break;
                    }
                }

                // Create the URL according to the configured format
                $baseUrl = App()->getPublicBaseUrl(true);
                $urlManager = Yii::app()->getUrlManager();
                $urlFormat = $urlManager->getUrlFormat();
                if ($urlFormat == CUrlManager::GET_FORMAT) {
                    $url = $baseUrl;
                    $params = [$urlManager->routeVar => $alias] + $params;
                } else {
                    $url = $baseUrl . '/' . $alias;
                }
                $query = $urlManager->createPathInfo($params, '=', '&');
                if (!empty($query)) {
                    $url .= "?" . $query;
                }
                return $url;
            }
        }

        // If short url is not preferred or no alias is found, return a traditional URL
        $urlParams = array_merge($params, ['sid' => $this->sid, 'lang' => $language]);
        $url = App()->createPublicUrl('survey/index', $urlParams);

        return $url;
    }

    /**
     * Returns the survey alias for the specified language.
     * @param string|null $language
     * @return string|null
     */
    public function getAliasForLanguage($language = null)
    {
        if (!empty($language) && !empty($this->languagesettings[$language]->surveyls_alias)) {
            return $this->languagesettings[$language]->surveyls_alias;
        }
        if (!empty($this->languagesettings[$this->language]->surveyls_alias)) {
            return $this->languagesettings[$this->language]->surveyls_alias;
        }
        return null;
    }

    /**
     * Validates the Expiration Date is not lower than the Start Date
     */
    public function checkExpireAfterStart($attributes, $params)
    {
        if (empty($this->startdate) || empty($this->expires)) {
            return true;
        }
        if ($this->expires < $this->startdate) {
            $this->addError('expires', gT("Expiration date can't be lower than the start date", 'unescaped'));
        }
    }

    /**
     * Get a dateime DB and return DateTime or null adjusted
     * @var string|null $datetime in PHP datetime formats
     * @return \DateTime|null
     */
    private static function shiftedDateTime($datetime)
    {
        if (is_string($datetime) && strtotime($datetime)) {
            $datetime = dateShift($datetime, "Y-m-d H:i:s", strval(Yii::app()->getConfig('timeadjust')));
            return new DateTime($datetime);
        }
        return null;
    }
}
