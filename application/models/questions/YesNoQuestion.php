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
     * @return array
     */
    public function getAnswers($scale = null)
    {
        $result = [
            'N' => gT('No'),
            'Y' => gT('Yes'),
        ];

        if (!$this->bool_mandatory) {
            $result[""] = gT('No answer');
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