<?php
/**
 * This file is part of ExpressionAnswerOptions plugin
 * @version 0.1.0
 */

namespace ExpressionAnswerOptionsExt;

use LimeExpressionManager;
use Question;
use Answer;

class AnswerOptionsFunctions
{
    /**
     * Return the answer text related to a question
     * @param integer|string $qid : id or code of question, get parent question if needed 
     * @param string $code : code of the answer text to return
     * @return null|string
     */
    public static function getAnswerOptionText($qidortitle, $code, $scale = 0)
    {
        $surveyId = LimeExpressionManager::getLEMsurveyId();
        $oQuestion = null;
        if (is_int($qidortitle) || ctype_digit($qidortitle)) { // self.qid is not an int …
            $oQuestion = Question::model()->find("qid = :qid and sid = :sid", array(":qid"=>$qidortitle, ":sid" => $surveyId));
            if ($oQuestion && $oQuestion->parent_qid) {
                $oQuestion = Question::model()->find("qid = :qid and sid = :sid", array(":qid"=>$oQuestion->parent_qid, ":sid" => $surveyId));
            }
        }
        
        if(empty($oQuestion)) {
            $oQuestion = Question::model()->find("title = :title and sid = :sid", array(":title"=>$qidortitle, ":sid" => $surveyId));
        }
        if(empty($oQuestion)) {
            return null;
        }
        /* Don't check the question type : we know it's a question (not a subquetsion) */
        $language = LimeExpressionManager::getEMlanguage(); // Or by App()->getLanguage(), em for expression file view ?
        return Answer::model()->getAnswerFromCode($oQuestion->qid, $code, $language, $scale);
    }
}
