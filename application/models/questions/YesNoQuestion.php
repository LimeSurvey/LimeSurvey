<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 7/23/15
 * Time: 9:48 AM
 */

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
            new \QuestionAnswer('N', gT('No')),
            new \QuestionAnswer('Y', gT('Yes')),
        ];

        if (!$this->bool_mandatory) {
            $result[] = new \QuestionAnswer("", gT('No answer'));
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
        return ['yes-no'];
    }


}