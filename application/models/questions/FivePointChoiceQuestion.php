<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/20/15
 * Time: 11:22 AM
 */

namespace ls\models\questions;


class FivePointChoiceQuestion extends FixedChoiceQuestion
{

    /**
     * Must return an array of answer options.
     * @return array
     */
    public function getAnswers($scale = null)
    {
        // TODO: Implement getAnswers() method.
    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     *
     * @todo Move this to individual classes
     */
    public function getClasses()
    {
        return ['choice-5-pt-radio'];
    }


}