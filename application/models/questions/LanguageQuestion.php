<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/20/15
 * Time: 11:17 AM
 */

namespace ls\models\questions;


use Response;

class LanguageQuestion extends FixedChoiceQuestion {

    /**
     * Must return an array of answer options.
     * @return array
     */
    public function getAnswers($scale = null)
    {
        return $this->survey->getAllLanguages();
    }

    /**
     * Checks if the question is relevant for the current response.
     * @param Response $response
     * @return boolean
     */
    public function isRelevant(Response $response)
    {
        return (count($this->survey->allLanguages) > 1) ? parent::isRelevant($response) : false;
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