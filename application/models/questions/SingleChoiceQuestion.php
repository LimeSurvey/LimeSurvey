<?php
namespace ls\models\questions;

/**
 * Class SingleChoiceQuestion
 * @package ls\models\questions
 */
class SingleChoiceQuestion extends ChoiceQuestion
{
    public function getAnswerScales()
    {
        return 1;
    }


    public function relations()
    {
        return array_merge(parent::relations(), [
            'answers' => [self::HAS_MANY, \Answer::class, 'question_id', 'order' => 'sortorder', 'index' => 'code']
        ]);
    }

    /**
     * @return array Column definitions for SingleChoiceQuestion type(s)
     */
    public function getColumns()
    {
        $result = [$this->sgqa => "string(5)"];

        if ($this->other == 'Y') {
            $result[$this->sgqa . 'other'] = 'text';
        }
        return $result;
    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        $result = [];
        switch ($this->type) {
            case self::TYPE_RADIO_LIST:
                $result[] = 'list-radio';
                break;
            case self::TYPE_DROPDOWN_LIST:
                $result[] = 'list-dropdown';
                break;
            default:
                throw new \Exception('no');
        }
        return $result;

    }


}