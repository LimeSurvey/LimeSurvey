<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/20/15
 * Time: 12:02 PM
 */

namespace ls\models\questions;


class NumericalArrayQuestion extends OpenArrayQuestion
{
    /**
     * Returns the fields for this question.
     * @return QuestionResponseField[]
     */
    public function getFields()
    {
        $result = parent::getFields();
        foreach($result as $field) {
            $field->setIsNumerical(true);
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
        return ['array-multi-flexi'];
    }


}