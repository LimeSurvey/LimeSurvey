<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 7/23/15
 * Time: 9:48 AM
 */

namespace ls\models\questions;


class GenderQuestion extends FixedChoiceQuestion
{

    /**
     * Must return an array of answer options.
     * @return array
     */
    public function getAnswers()
    {
        $result = [
            'M' => gT("Male"),
            'F' => gT("Female"),
        ];

        if (!$this->bool_mandatory) {
            $result[""] = gT('No answer');
        }

        return $result;

    }




}