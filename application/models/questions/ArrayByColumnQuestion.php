<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/20/15
 * Time: 3:54 PM
 */

namespace ls\models\questions;


class ArrayByColumnQuestion extends ArrayQuestion
{
    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        return ['array-flexible-column'];
    }

}