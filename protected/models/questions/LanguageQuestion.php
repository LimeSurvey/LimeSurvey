<?php
namespace ls\models\questions;

use Response;

class LanguageQuestion extends FixedChoiceQuestion {

    /**
     * Must return an array of answer options.
     * @return array
     */
    public function getAnswers($scale = null)
    {
        return array_map(function($language) {
            return new \ls\components\QuestionAnswer($language, $language);
        }, $this->survey->getAllLanguages());
    }

    /**
     * Checks if the question is relevant for the current response.
     * @param Response $response
     * @return boolean
     */
    public function isRelevant(\ls\interfaces\iResponse $response)
    {

        $result = (count($this->survey->allLanguages) > 1) ? parent::isRelevant($response) : false;
        vdd($result);
    }

    public function getRelevanceScript()
    {
        return (count($this->survey->allLanguages) > 1) ? parent::getRelevanceScript() : false;
    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        return ['language'];
    }


}