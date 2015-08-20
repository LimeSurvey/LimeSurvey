<?php
namespace ls\models\questions;

/**
 * Class MultipleChoiceQuestion
 * @package ls\models\questions
 */
class MultipleChoiceQuestion extends ChoiceQuestion
{
    public function getSubQuestionScales()
    {
        return 1;
    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        return ['multiple-opt'];
    }


}