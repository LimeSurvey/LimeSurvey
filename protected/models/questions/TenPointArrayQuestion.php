<?php
namespace ls\models\questions;

class TenPointArrayQuestion extends FivePointArrayQuestion
{

    /**
     * @param int $scale
     * @return iAnswer
     */
    public function getAnswers($scale = null)
    {
        return $this->createAnswers(10);
    }

    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'array-10-pt';
        return $result;
    }

    protected function getSummary()
    {
        return gT("An array with sub-question on each line. The answers are value from 1 to 10 and are contained in the table header. ");
    }



}