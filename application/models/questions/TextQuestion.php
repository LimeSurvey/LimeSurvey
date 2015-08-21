<?php
namespace ls\models\questions;

/**
 * Class TextQuestion
 * "Long free text"
 * @package ls\models\questions
 */
class TextQuestion extends \Question{
    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        $result = [];
        switch($this->type) {
            case self::TYPE_SHORT_TEXT:
                $result[] = 'text-short';
                break;
            case self::TYPE_LONG_TEXT:
                $result[] = 'text-long';
                break;
            case self::TYPE_HUGE_TEXT:
                $result[] = 'text-huge';
                break;
            default:
                throw new \Exception('no');
        }
        return $result;
    }

}