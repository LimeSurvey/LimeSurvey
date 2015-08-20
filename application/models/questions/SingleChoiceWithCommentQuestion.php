<?php
namespace ls\models\questions;

/**
 * @package ls\models\questions
 */
class SingleChoiceWithCommentQuestion extends SingleChoiceQuestion
{
    /**
     * @return array Column definitions for SingleChoiceQuestion type(s)
     */
    public function getColumns()
    {
        $result = parent::getColumns();
        $result[$this->sgqa . 'comment'] = 'text';
        return $result;
    }

    public function getFields() {
        bP();
        $result = parent::getFields();

        $result[$this->sgqa . 'other'] = new \QuestionResponseField($this->sgqa . 'comment', $this->title . 'comment', $this);

        eP();
        return $result;
    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        return ['list-with-comment'];
    }


}