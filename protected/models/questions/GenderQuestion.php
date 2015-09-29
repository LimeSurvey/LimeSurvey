<?php
namespace ls\models\questions;


use ls\interfaces\iAnswer;

class GenderQuestion extends FixedChoiceQuestion
{

    /**
     * Must return an array of answer options.
     * @return iAnswer[]
     */
    public function getAnswers($scale = null)
    {
        $result = [
            new \ls\components\QuestionAnswer('M', gT("Male")),
            new \ls\components\QuestionAnswer('F', gT("Female")),
        ];

        if (!$this->bool_mandatory) {
            $result[] = new \ls\components\QuestionAnswer('', gT('No answer'));
        }

        return $result;

    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string ls\models\Question class to be added to the container
     */
    public function getClasses()
    {
        return ['gender'];
    }




}