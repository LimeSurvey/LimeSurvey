<?php
namespace ls\models\questions;

/**
 * Class ArrayQuestion
 * @package ls\models\questions
 */
abstract class ArrayQuestion extends \Question
{


    public function getSubQuestionScales()
    {
        return 1;
    }


    public function relations()
    {
        return array_merge(parent::relations(), [
            'answers' => [self::HAS_MANY, \Answer::class, 'question_id', 'order' => 'sortorder', 'index' => 'code']
        ]);
    }



}