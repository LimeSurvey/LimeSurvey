<?php

/**
 * Class SurveyAnonymizer will overwrite the possibly personal data with random un-identifiable data
 */
class SurveyAnonymizer
{
    /** @var Survey */
    protected $survey;

    /** @var string */
    public $error;

    public function __construct($survey)
    {
        if (!($survey instanceof Survey)){
            throw new \Exception("Survey must be an instance of Survey");
        }

        $this->survey = $survey;
    }

    /**
     * @return bool
     */
    public function anonymize(){
        return false;
    }


}