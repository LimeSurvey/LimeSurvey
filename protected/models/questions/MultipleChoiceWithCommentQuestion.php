<?php
namespace ls\models\questions;


use ls\interfaces\iResponse;

class MultipleChoiceWithCommentQuestion extends MultipleChoiceQuestion
{
    /**
     * Returns an array of EM expression that validate this question.
     * @return string[]
     */
    public function getValidationExpressions()
    {
        $result = parent::getValidationExpressions();
//        switch ($this->commented_checkbox) {
//            case 'checked':
//                $sq_eqn_commented_checkbox = [];
//                foreach ($this->subQuestions as $subQuestion) {
//                    $sq_eqn_commented_checkbox[] = "(is_empty({$subQuestion->varName}.NAOK) and !is_empty({$subQuestion->varName}comment.NAOK))";
//                }
//                $result[] = [
//                    'type' => 'commented_checkbox',
//                    'class' => 'commented_checkbox',
//                    'eqn' => "sum(" . implode(",", $sq_eqn_commented_checkbox) . ")==0",
//                ];
//                break;
//            case 'unchecked':
//                $sq_eqn_commented_checkbox = array();
//                foreach ($this->subQuestions as $subQuestion) {
//                    $sq_eqn_commented_checkbox[] = "(!is_empty({$subQuestion->varName}.NAOK) and !is_empty({$subQuestion->varName}comment.NAOK))";
//                }
//                $result[] = [
//                    'type' => 'commented_checkbox',
//                    'class' => 'commented_checkbox',
//                    'eqn' => "sum(" . implode(",", $sq_eqn_commented_checkbox) . ")==0",
//                ];
//                break;
//            case 'allways':
//            default:
//                break;
//        }
//        if ($this->commented_checkbox != "always") {
//
//        }
        return $result;
    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        return ['multiple-opt-comments'];
    }


    public function renderSubQuestion(\Question $question, iResponse $response, \ls\components\SurveySession $session) {

        $result = parent::renderSubQuestion($question, $response, $session);
        // Render a line in the multiple choice question.
        $field = $this->sgqa . $question->title . 'comment';
        $result .= \CHtml::textField($field, $response->$field);
        $result .= \TbHtml::closeTag('div');
        return $result;

    }

    public function getColumns()
    {
        $result = [];
        foreach (parent::getColumns() as $name => $type) {
            $result[$name] = $type;
            $result[$name . 'comment'] = 'string';
        }
        return $result;

    }


}