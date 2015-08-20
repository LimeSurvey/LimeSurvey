<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 5/11/15
 * Time: 5:12 PM
 */

namespace ls\models\questions;


class MultipleTextQuestion extends TextQuestion
{
    public function getSubQuestionScales()
    {
        return 1;
    }

    /**
     * Returns the fields for this question.
     * @return \QuestionResponseField[]
     */
    public function getFields() {
        foreach($this->subQuestions as $subQuestion) {
            $fields[] = $field = new \QuestionResponseField($this->sgqa . $subQuestion->title, "{$this->title}_{$subQuestion->title}", $this);
        }

        return $fields;


    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        return ['multiple-short-txt'];
    }


}