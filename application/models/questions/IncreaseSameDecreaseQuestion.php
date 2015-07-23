<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 7/23/15
 * Time: 11:46 AM
 */

namespace ls\models\questions;


class IncreaseSameDecreaseQuestion extends FixedChoiceQuestion
{

    /**
     * Must return an array of answer options.
     * @return array
     */
    public function getAnswers()
    {
        $result = [
            'I' => gT("Increase"),
            'S' => gT("Same"),
            'D' => gT("Decrease"),
        ];

        if (!$this->bool_mandatory) {
            $result[""] = gT('No answer');
        }

        return $result;

    }
}