<?php
namespace ls\models\questions;

/**
 * Class SingleChoiceQuestion
 * @package ls\models\questions
 */
class SingleChoiceQuestion extends ChoiceQuestion
{

    public function getHasAnswers() {
        return true;
    }
    public function relations()
    {
        return array_merge(parent::relations(), [
            'answers' => [self::HAS_MANY, \Answer::class, 'question_id', 'order' => 'sortorder', 'index' => 'code']
        ]);
    }
}