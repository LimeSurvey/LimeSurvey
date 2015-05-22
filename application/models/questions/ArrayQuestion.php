<?php
namespace ls\models\questions;

/**
 * Class ArrayQuestion
 * @package ls\models\questions
 */
class ArrayQuestion extends \Question
{
    public function getHasAnswers()
    {
        return true;
    }

    public function getHasSubQuestions()
    {
        return true;
    }


    public function relations()
    {
        return array_merge(parent::relations(), [
            'answers' => [self::HAS_MANY, \Answer::class, 'question_id', 'order' => 'sortorder', 'index' => 'code']
        ]);
    }



}