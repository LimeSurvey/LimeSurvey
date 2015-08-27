<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/20/15
 * Time: 11:56 AM
 */

namespace ls\models\questions;


class FivePointArrayQuestion extends FixedArrayQuestion
{

    public function getAnswers($scale = null)
    {
        $result = [
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            "{TEXTRIGHT}" => true, // right text is placed before no answer column.
        ];

        if (!$this->bool_mandatory && $this->survey->bool_shownoanswer) {
            $result[""] = gT("No answer");
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
        $result[] = 'array-5-pt';
        return $result;
    }


    protected function getSummary()
    {
        return gT("An array with sub-question on each line. The answers are value from 1 to 5 and are contained in the table header. ");
    }
}