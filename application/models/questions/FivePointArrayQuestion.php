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
        // TODO: Implement getAnswers() method.
    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        return ['array-5-pt'];
    }


}