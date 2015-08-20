<?php
namespace ls\models\questions;

class NumericalQuestion extends \Question {
    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        return ['numeric'];
    }

}