<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/20/15
 * Time: 11:19 AM
 */

namespace ls\models\questions;


class MultipleNumberQuestion extends MultipleTextQuestion
{
    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        return ['numeric-multi'];
    }


}