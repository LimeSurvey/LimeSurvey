<?php
namespace ls\models\questions;


class ArrayByColumnQuestion extends ArrayQuestion
{
    /**
     * This function return the class by question type
     * @param string question type
     * @return string ls\models\Question class to be added to the container
     */
    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'array-flexible-column';
        return $result;
    }

}