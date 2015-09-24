<?php
namespace ls\models\questions;

/**
 * Class ChoiceQuestion
 * @package ls\models\questions
 */
class EquationQuestion extends \Question
{
    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'equation';
        return $result;
    }

    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param \ls\interfaces\Response $response
     * @param \ls\components\SurveySession $session
     * @return \ls\components\RenderedQuestion
     */
    public function render(\ls\interfaces\iResponse $response, \ls\components\SurveySession $session)
    {
        $result = parent::render($response, $session);
        $result->setHtml(" ");
        return $result;
    }


}