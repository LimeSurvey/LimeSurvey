<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 7/23/15
 * Time: 11:46 AM
 */

namespace ls\models\questions;


class YesNoUncertainArrayQuestion extends FixedArrayQuestion
{
    /**
     * Must return an array of answer options.
     * @return array
     */
    public function getAnswers($scale = null)
    {
        $result = [
            'Y' => gT("Yes"),
            'N' => gT("No"),
            'U' => gT("Uncertain"),
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
        return ['array-yes-uncertain-no'];
    }


}