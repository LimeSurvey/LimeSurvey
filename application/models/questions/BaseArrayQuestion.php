<?php
namespace ls\models\questions;

/**
 * Base class for array questions
 * Class ArrayQuestion
 * @package ls\models\questions
 */
abstract class BaseArrayQuestion extends \Question
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

    public function getColumns()
    {
        $result = call_user_func_array('array_merge', array_map(function (\Question $subQuestion) {
            $subResult = [];
            foreach ($subQuestion->columns as $name => $type) {
                $subResult[$this->sgqa . $name] = $type;
            }
            return $subResult;
        }, $this->subQuestions));
        return $result;
    }





}