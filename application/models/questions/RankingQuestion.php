<?php
namespace ls\models\questions;


class RankingQuestion extends \Question
{
    public function getAnswerScales()
    {
        return 1;
    }


    public function getColumns()
    {
        $result = [];

        for ($i = 1; $i <= count($this->answers); $i++) {
            $result[$this->sgqa . $i] = "string(5)";
        }

        return $result;
    }
}


