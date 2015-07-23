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
    public function getAnswers()
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




}