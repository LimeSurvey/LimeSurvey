<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/20/15
 * Time: 11:56 AM
 */

namespace ls\models\questions;


class TenPointArrayQuestion extends FixedArrayQuestion
{

    public function getAnswers($scale = null)
    {
        $result = [];
        for ($i = 1; $i <= 10; $i++) {
            $result[$i] = $i;
        }

        if (!$this->bool_mandatory && $this->survey->bool_shownoanswer) {
            $result[""] = gT("No answer");
        }
        return $result;
    }

    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'array-10-pt';
        return $result;
    }

    protected function getSummary()
    {
        return gT("An array with sub-question on each line. The answers are value from 1 to 10 and are contained in the table header. ");
    }



}