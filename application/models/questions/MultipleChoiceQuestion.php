<?php
namespace ls\models\questions;

/**
 * Class MultipleChoiceQuestion
 * @package ls\models\questions
 */
class MultipleChoiceQuestion extends ChoiceQuestion
{

    public function getHasSubQuestions() {
        return true;
    }
//    public function relations()
//    {
//        return array_merge(parent::relations(), [
//            'answers' => [self::HAS_MANY, \Answer::class, 'question_id', 'order' => 'sortorder', 'index' => 'code']
//        ]);
//    }
}