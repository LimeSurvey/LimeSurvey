<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 7/23/15
 * Time: 11:46 AM
 */

namespace ls\models\questions;


class YesNoUncertainQuestion extends FixedChoiceQuestion
{
    /**
     * Must return an array of answer options.
     * @return array
     */
    public function getAnswers()
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
}