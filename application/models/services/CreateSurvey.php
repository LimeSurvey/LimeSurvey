<?php


namespace LimeSurvey\Models\Services;

use Survey;

/**
 * This class is responsible for creating a new survey.
 *
 * Class CreateSurvey
 * @package LimeSurvey\Models\Services
 */
class CreateSurvey
{

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


        if($this->createExample){
            //... create exampls
        }

        return $this->survey;
    }

}