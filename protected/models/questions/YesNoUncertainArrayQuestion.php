<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 7/23/15
 * Time: 11:46 AM
 */

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
            new \QuestionAnswer('Y', gT("Yes")),
            new \QuestionAnswer('N', gT("No")),
            new \QuestionAnswer('U', gT("Uncertain"))
        ];

        if (!$this->bool_mandatory) {
            $result[] = new \QuestionAnswer('', gT('No answer'));
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