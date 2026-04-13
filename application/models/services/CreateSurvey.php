<?php

namespace LimeSurvey\Models\Services;

use Survey;
use SurveyLanguageSetting;
use Permission;
use LimeSurvey\Datavalueobjects\SimpleSurveyValues;

/**
 * This class is responsible for creating a new survey.
 *
 * Class CreateSurvey
 * @package LimeSurvey\Models\Services
 */
class CreateSurvey
{
    /** @var int number of attempts to find a valid survey ID */
    const ATTEMPTS_CREATE_SURVEY_ID = 50;

    /** @var string all attributes that have the value "NO" */
    const STRING_VALUE_FOR_NO_FALSE = 'N';

    /** @var string all attributes that have the value "YES" */
    const STRING_VALUE_FOR_YES_TRUE = 'Y';

    /** @var string value to set attribute to inherit */
    const STRING_SHORT_VALUE_INHERIT = 'I';

    /** @var int */
    const INTEGER_VALUE_FOR_INHERIT = -1;

    /** @var int this is the default value for DB table (it corresponds to ) */
    const DEFAULT_DATE_FORMAT = 1;

    /** @var Survey the survey */
    private $survey;

    /** @var SurveyLanguageSetting the new language settings model for the survey*/
    private $newLanguageSettings;

    /** @var SimpleSurveyValues has the simple values for creating a survey */
    private $simpleSurveyValues;

    /**
     * CreateSurvey constructor.
     *
     * @param Survey $survey the survey object
     * @param SurveyLanguageSetting $newLanguageSettings new created SurveyLanguageSettings model
     *
     */
    public function __construct($survey, $newLanguageSettings)
    {
        $this->survey = $survey;
        $this->newLanguageSettings = $newLanguageSettings;
    }

    /**
     * This creates a simple survey with the basic attributes set in param simpleSurveyValues
     *
     * @param SimpleSurveyValues $simpleSurveyValues
     * @param int $userID the id of user who is creating the survey
     * @param Permission $permissionModel
     *
     * @return Survey|bool returns the survey or false if survey could not be created for any reason
     */
    public function createSimple($simpleSurveyValues, $userID, $permissionModel, $overrideAdministrator = true)
    {

        $this->simpleSurveyValues = $simpleSurveyValues;
        $this->survey->gsid = $simpleSurveyValues->surveyGroupId;
        try {
            $this->createSurveyId();
            $this->setBaseLanguage();
            $this->initialiseSurveyAttributes($overrideAdministrator);

            if (!$this->survey->save()) {
                // TODO: Localization?
                throw new \Exception("Survey value/values are not valid. Not possible to save survey");
            }

            //check realtional tables to be initialised like survey_languagesettings
            $this->createRelationSurveyLanguageSettings($this->newLanguageSettings);

            // Update survey permissions
            $permissionModel->giveAllSurveyPermissions($userID, $this->survey->sid);
        } catch (\Exception $e) {
            return false;
        }

        return $this->survey;
    }

    /**
     * Insert new entry in surveys_languagesettings (sets surveyid, title, language). All other values
     * are set to default values (user can change them later in survey administration).
     *
     * @param SurveyLanguageSetting $langsettings
     *
     * @return void
     * @throws \Exception if not possible to save in DB
     */
    private function createRelationSurveyLanguageSettings($langsettings)
    {
        $sTitle = html_entity_decode($this->simpleSurveyValues->title, ENT_QUOTES, "UTF-8");

        // Fix bug with FCKEditor saving strange BR types
        $sTitle = fixCKeditorText($sTitle);

        // select dateformat/numberformat(radixpoint) in dependency
        // of chosen language (see surveytranslator_helper getLanguageData()) as default value...
        $languageSettings = getLanguageData();
        if (isset($languageSettings[$this->survey->language]['dateformat']) && isset($languageSettings[$this->survey->language]['radixpoint'])) {
            $dateFormat = $languageSettings[$this->survey->language]['dateformat'];
            $numberFormat = $languageSettings[$this->survey->language]['radixpoint'];
        } else {
            $dateFormat = 1; //default value
            $numberFormat = 0; // set 0 as default ... means '.' see getRadixPointData() in surveytranslator_helper ...
        }

        // Insert base language into surveys_language_settings table
        $aInsertData = array(
            'surveyls_survey_id' => $this->survey->sid,
            'surveyls_title' => $sTitle,
            'surveyls_description' => '',
            'surveyls_welcometext' => '',
            'surveyls_language' => $this->survey->language,
            'surveyls_urldescription' => '',
            'surveyls_endtext' => '',
            'surveyls_url' => '',
            'surveyls_dateformat' => $dateFormat,
            'surveyls_numberformat' => $numberFormat,
            'surveyls_policy_notice' => '',
            'surveyls_policy_notice_label' => ''
        );

        if (!$langsettings->insertNewSurvey($aInsertData)) {
            throw new \Exception('SurveyLanguageSettings could not be created');
        }
    }

    /**
     * Sets the baselanguage. If baselanguag is null or empty string Exception is thrown.
     *
     * @throws \Exception  if $this->baseLanguage is null or empty string
     */
    private function setBaseLanguage()
    {
        $baseLang = $this->simpleSurveyValues->baseLanguage;

        //check if language exists in our language array...
        $languageShortNames = getLanguageDataRestricted(true, 'short');

        if (array_key_exists($baseLang, $languageShortNames)) {
            $this->survey->language = $baseLang;
        } else {
            throw new \Exception("Invalid language");
        }
    }

    /**
     * Creates a unique survey ID. A survey ID always consists of 6 numbers [123456789].
     *
     * If not possible within ATTEMPTS_CREATE_SURVEY_ID an Exception is thrown
     *
     * @throws \Exception
     */
    private function createSurveyId()
    {
        $attempts = 0;
        /* Validate sid : > 1 and unique */
        $this->survey->sid = intval(randomChars(6, '123456789'));
        while (!$this->survey->validate(array('sid'))) {
            $attempts++;
            $this->survey->sid = intval(randomChars(6, '123456789'));
            /* If it's happen : there are an issue in server … (or in randomChars function …) */
            if ($attempts > self::ATTEMPTS_CREATE_SURVEY_ID) {
                throw new \Exception("Unable to get a valid survey ID after " . self::ATTEMPTS_CREATE_SURVEY_ID . " attempts");
            }
        }
    }

    /**
     * @return void
     */
    private function initialiseSurveyAttributes($overrideAdministrator = true)
    {
        $this->survey->expires = null;
        $this->survey->startdate = null;
        $this->survey->template = 'inherit'; //default template from default group is set to 'fruity'
        $this->survey->active = self::STRING_VALUE_FOR_NO_FALSE;
        $this->survey->anonymized = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->format = self::STRING_SHORT_VALUE_INHERIT; //inherits value from survey group
        $this->survey->savetimings = self::STRING_SHORT_VALUE_INHERIT; //could also be 'I' for inherit from survey group ...
        $this->survey->language = $this->simpleSurveyValues->baseLanguage;
        $this->survey->datestamp = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->ipaddr = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->ipanonymize = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->refurl = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->usecookie = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->emailnotificationto = 'inherit';
        $this->survey->allowregister = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->allowsave = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->navigationdelay = self::INTEGER_VALUE_FOR_INHERIT;
        $this->survey->autoredirect = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->showxquestions = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->showgroupinfo = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->showqnumcode = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->shownoanswer = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->showwelcome = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->allowprev =  self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->questionindex = self::INTEGER_VALUE_FOR_INHERIT;
        $this->survey->nokeyboard = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->showprogress = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->printanswers = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->listpublic = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->htmlemail = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->sendconfirmation = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->tokenanswerspersistence = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->alloweditaftercompletion = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->usecaptcha = 'E'; // see Survey::saveTranscribeCaptchaOptions() special inherit char ...
        $this->survey->publicstatistics = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->publicgraphs = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->assessments = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->emailresponseto = 'inherit';
        $this->survey->tokenlength = self::INTEGER_VALUE_FOR_INHERIT;
        $this->survey->bounce_email = 'inherit';
        if ($overrideAdministrator) {
            $this->survey->admin = $this->simpleSurveyValues->admin; //admin name ...
            $this->survey->adminemail = $this->simpleSurveyValues->adminEmail;
        }
    }
}
