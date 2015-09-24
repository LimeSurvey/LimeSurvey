<?php
namespace ls\models\questions;

use ls\interfaces\iAnswer;

class YesNoUncertainArrayQuestion extends FixedArrayQuestion
{
    /**
     * Must return an array of answer options.
     * @return iAnswer
     */
    public function getAnswers($scale = null)
    {
        $result = [
            new \ls\components\QuestionAnswer('Y', gT("Yes")),
            new \ls\components\QuestionAnswer('N', gT("No")),
            new \ls\components\QuestionAnswer('U', gT("Uncertain"))
        ];

        if (!$this->bool_mandatory) {
            $result[] = new \ls\components\QuestionAnswer('', gT('No answer'));
        }

        return $result;

    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'array-yes-uncertain-no';
        return $result;
    }

    protected function getSummary()
    {
        return gT("An array with sub-question on each line. The answers are yes, no, uncertain and are in the table header. ");
    }

}