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

    /**
     * Returns an array of EM expression that validate this question.
     * @return string[]
     */
    public function getValidationExpressions()
    {
        $result = parent::getValidationExpressions();
        foreach($this->subQuestions as $subQuestion) {
            $sq_names[] = $subq['varName'].".NAOK";
            $sq_eqPart[] = "intval(!is_empty({$subq['varName']}.NAOK))*{$subq['csuffix']}";
        }
        $result[] = [
            'type' => 'default',
            'class' => 'default',
            'eqn' =>  'unique(' . implode(',',$sq_names) . ') and count(' . implode(',',$sq_names) . ')==max('. implode(',',$sq_eqPart) .')',
        ];

    }


}


