<?php


namespace LimeSurvey\Models\Services;

use PHPMailer\PHPMailer\Exception;
use Survey;

/**
 * This class is responsible for creating a new survey.
 *
 * Class CreateSurvey
 * @package LimeSurvey\Models\Services
 */
class CreateSurvey
{
    /** @var int number of attempts tp find a valid survey id */
    const ATTEMPTS_CREATE_SURVEY_ID = 50;

    /** @var string all attributes that have the value "NO" */
    const STRING_VALUE_FOR_NO_FALSE = 'N';

    /** @var string all attributes that have the value "YES" */
    const STRING_VALUE_FOR_YES_TRUE = 'Y';

    /** @var string value to set attribute to inherit */
    const STRING_SHORT_VALUE_INHERIT = 'I';


    /** @var string language selected by user */
    private $baseLanguage;

    /** @var string title of the survey */
    private $title;

    /** @var boolean creates example questiongroup and questions */
    private $createExample;

    /** @var  int the surveygroup from which the new survey will inherit values*/
    private $surveyGroup;

    /** @var Survey the survey */
    private $survey;

    /**
     * CreateSurvey constructor.
     *
     * @param string $title  the title of the survey
     * @param boolean $createExample if true creates example questiongroup and questions
     * @param string $baseLanguage shortcut for the language like 'en' for english
     * @param int $surveyGroup the surveygroup from which the new survey will inherit values
     */
    public function __construct($title, $createExample, $baseLanguage, $surveyGroup)
    {
        $this->title = $title;
        $this->createExample = $createExample;
        $this->baseLanguage = $baseLanguage;
        $this->surveyGroup = $surveyGroup;
        $this->survey = new Survey();
    }

    /**
     * This creates a simple survey with the basic attributes set in constructor.
     *
     * @return Survey|bool returns the survey or false if survey could not be created for any reason
     */
    public function createSimple(){

        $this->survey->gsid = $this->surveyGroup;
        try {
            $this->createSurveyId();
            $this->setBaseLanguage();
            $this->initialiseSurveyAttributes();



            if($this->createExample){
                //... create exampls
            }

            //check realtional tables to be initialised like survey_languagesettings
            //e.g. the surveyTitle is set in table survey_languagesettings attribute ---> surveyls_title

            if(!$this->survey->save()){
                throw new \Exception("Survey value/values are not valid. Not possible to save survey");
            }

        }catch (Exception $e){
            return false;
        }

        return $this->survey;
    }

    /**
     * Sets the baselanguage. If baselanguag is null or empty string Exception is thrown.
     *
     * @throws \Exception  if $this->baseLanguage is null or empty string
     */
    private function setBaseLanguage(){
        if($this->baseLanguage !== null && $this->baseLanguage!==''){
            $this->survey->language = $this->baseLanguage;

            //todo: check the shortname of language (e.g. 'en')
        }else{
            throw new \Exception("Invalid language");
        }
    }

    /**
     * Creates a unique survey id. A survey id always consists of 6 numbers [123456789].
     *
     * If not possible within ATTEMPTS_CREATE_SURVEY_ID an Exception is thrown
     *
     * @throws Exception
     */
    private function createSurveyId(){
        $attempts = 0;
        /* Validate sid : > 1 and unique */
        while(!$this->survey->validate(array('sid'))) {
            $attempts++;
            $this->survey->sid = intval(randomChars(6, '123456789'));
            /* If it's happen : there are an issue in server … (or in randomChars function …) */
            if($attempts > self::ATTEMPTS_CREATE_SURVEY_ID) {
                throw new Exception("Unable to get a valid survey id after ". self::ATTEMPTS_CREATE_SURVEY_ID . " attempts");
            }
        }
    }

    /**
     *
     */
    private function initialiseSurveyAttributes(){

        $this->survey->expires = null;
        $this->survey->startdate = null;
        $this->survey->template = 'inherit'; //which template is this here???
        $this->survey->admin = ''; //what to set here ??
        $this->survey->active = self::STRING_VALUE_FOR_NO_FALSE;
        $this->survey->anonymized = self::STRING_VALUE_FOR_NO_FALSE;
        $this->survey->faxto = null;
        $this->survey->format = self::STRING_SHORT_VALUE_INHERIT;
        $this->survey->savetimings = 'N'; //could also be 'I' for inherit from survey group ...

       // 'expires' => $sExpiryDate,
       //         'startdate' => $sStartDate,
       //         'template' => App()->request->getPost('template'),
       //         'admin' => App()->request->getPost('admin'),
      //          'active' => 'N',
      //          'anonymized' => App()->request->getPost('anonymized'),
      //          'faxto' => App()->request->getPost('faxto'),
      //          'format' => App()->request->getPost('format'),
     //           'savetimings' => App()->request->getPost('savetimings'),
                'language' => App()->request->getPost('language', Yii::app()->session['adminlang']),
                'datestamp' => App()->request->getPost('datestamp'),
                'ipaddr' => App()->request->getPost('ipaddr'),
                'ipanonymize' => App()->request->getPost('ipanonymize'),
                'refurl' => App()->request->getPost('refurl'),
                'usecookie' => App()->request->getPost('usecookie'),
                'emailnotificationto' => App()->request->getPost('emailnotificationto'),
                'allowregister' => App()->request->getPost('allowregister'),
                'allowsave' => App()->request->getPost('allowsave'),
                'navigationdelay' => App()->request->getPost('navigationdelay'),
                'autoredirect' => App()->request->getPost('autoredirect'),
                'showxquestions' => App()->request->getPost('showxquestions'),
                'showgroupinfo' => App()->request->getPost('showgroupinfo'),
                'showqnumcode' => App()->request->getPost('showqnumcode'),
                'shownoanswer' => App()->request->getPost('shownoanswer'),
                'showwelcome' => App()->request->getPost('showwelcome'),
                'allowprev' => App()->request->getPost('allowprev'),
                'questionindex' => App()->request->getPost('questionindex'),
                'nokeyboard' => App()->request->getPost('nokeyboard'),
                'showprogress' => App()->request->getPost('showprogress'),
                'printanswers' => App()->request->getPost('printanswers'),
                'listpublic' => App()->request->getPost('listpublic'),
                'htmlemail' => App()->request->getPost('htmlemail'),
                'sendconfirmation' => App()->request->getPost('sendconfirmation'),
                'tokenanswerspersistence' => App()->request->getPost('tokenanswerspersistence'),
                'alloweditaftercompletion' => App()->request->getPost('alloweditaftercompletion'),
                'usecaptcha' => Survey::saveTranscribeCaptchaOptions(),
                'publicstatistics' => App()->request->getPost('publicstatistics'),
                'publicgraphs' => App()->request->getPost('publicgraphs'),
                'assessments' => App()->request->getPost('assessments'),
                'emailresponseto' => App()->request->getPost('emailresponseto'),
                'tokenlength' => $iTokenLength,
                'gsid' => App()->request->getPost('gsid', '1'),
                'adminemail' => Yii::app()->request->getPost('adminemail'),
                'bounce_email' => Yii::app()->request->getPost('bounce_email'),
    }

}