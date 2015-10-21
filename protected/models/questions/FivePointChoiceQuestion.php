<?php
namespace ls\models\questions;
use ls\interfaces\iAnswer;
use ls\interfaces\iResponse;

class FivePointChoiceQuestion extends FixedChoiceQuestion
{

    /**
     * @param null $scale
     * @return iAnswer[];
     */
    public function getAnswers($scale = null)
    {

        $answers = [];
        for ($i = 1; $i <= 5; $i++) {
            $answers[] = $answer = new \ls\components\QuestionAnswer($i, $i);
        }
        if (!$this->bool_mandatory && $this->survey->bool_shownoanswer) {
            $result[] = new \ls\components\QuestionAnswer("", gT("No answer"));
        }
        return $answers;
    }
    /**
     * This function return the class by question type
     * @param string question type
     * @return string ls\models\Question class to be added to the container
     *
     */
    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'choice-5-pt-radio';
        return $result;
    }

    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param \ls\interfaces\Response $response
     * @param \ls\components\SurveySession $session
     * @return string
     */
    public function render(iResponse$response, \ls\components\SurveySession $session)
    {
        $result = parent::render($response, $session);
        return $result;
    }



}