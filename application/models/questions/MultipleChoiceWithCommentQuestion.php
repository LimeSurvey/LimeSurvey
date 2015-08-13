<?php
namespace ls\models\questions;


class MultipleChoiceWithCommentQuestion extends MultipleChoiceQuestion
{
    /**
     * Returns an array of EM expression that validate this question.
     * @return string[]
     */
    public function getValidationExpressions()
    {
        $result = parent::getValidationExpressions();
        switch ($this->commented_checkbox) {
            case 'checked':
                $sq_eqn_commented_checkbox = [];
                foreach ($this->subQuestions as $subQuestion) {
                    $sq_eqn_commented_checkbox[] = "(is_empty({$subQuestion->varName}.NAOK) and !is_empty({$subQuestion->varName}comment.NAOK))";
                }
                $result[] = [
                    'type' => 'commented_checkbox',
                    'class' => 'commented_checkbox',
                    'eqn' => "sum(" . implode(",", $sq_eqn_commented_checkbox) . ")==0",
                ];
                break;
            case 'unchecked':
                $sq_eqn_commented_checkbox = array();
                foreach ($this->subQuestions as $subQuestion) {
                    $sq_eqn_commented_checkbox[] = "(!is_empty({$subQuestion->varName}.NAOK) and !is_empty({$subQuestion->varName}comment.NAOK))";
                }
                $result[] = [
                    'type' => 'commented_checkbox',
                    'class' => 'commented_checkbox',
                    'eqn' => "sum(" . implode(",", $sq_eqn_commented_checkbox) . ")==0",
                ];
                break;
            case 'allways':
            default:
                break;
        }
        if ($this->commented_checkbox != "allways") {

        }
        return $result;
    }

}