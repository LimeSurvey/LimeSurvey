<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/11/15
 * Time: 1:39 PM
 */

namespace ls\models\questions;


class DateTimeQuestion extends TextQuestion
{
    /**
     * Returns an array of EM expression that validate this question.
     * @return string[]
     */
    public function getValidationExpressions()
    {
        $result = parent::getValidationExpressions();

        // Minimum date allowed in date question
        if (isset($this->date_min) && trim($this->date_min) != '') {
            // date_min: Determine whether we have an expression, a full date (YYYY-MM-DD) or only a year(YYYY)
            if (trim($this->date_min) != '') {
                if ((strlen($this->date_min) == 4) && ($this->date_min >= 1900) && ($this->date_min <= 2099)) {
                    // backward compatibility: if only a year is given, add month and day
                    $date_min = '\'' . $this->date_min . '-01-01' . ' 00:00\'';
                } elseif (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/", $this->date_min)) {
                    $date_min = '\'' . $this->date_min . ' 00:00\'';
                } elseif (array_key_exists($this->date_min, $this->qcode2sgqa))  // refers to another question
                {
                    $date_min = $this->date_min . '.NAOK';
                }
            }
            $result[] = [
                'type' => 'date_min',
                'class' => 'value_range',
                'eqn' => '(is_empty(' . $this->varName . '.NAOK) || (' . $this->varName . '.NAOK >= date("Y-m-d H:i", strtotime(' . $date_min . ')) ))'
            ];




        }
        return $result;
    }

}