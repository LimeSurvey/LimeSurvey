<?php
namespace ls\models\questions;

class YesNoQuestion extends FixedChoiceQuestion
{

    /**
     * Must return an array of answer options.
     * @return iAnswer
     */
    public function getAnswers($scale = null)
    {
        $result = [
            new \ls\components\QuestionAnswer('N', gT('No')),
            new \ls\components\QuestionAnswer('Y', gT('Yes')),
        ];

        if (!$this->bool_mandatory) {
            $result[] = new \ls\components\QuestionAnswer("", gT('No answer'));
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
        return ['yes-no'];
    }


}