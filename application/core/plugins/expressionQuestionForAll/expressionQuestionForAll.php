<?php

/**
 * expressionQuestionForAll : Add QCODE.question for question with subquestion for expression Manager.
 * This don't manage subquestion Scale Y or Scale X
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2019 LimeSurvey - Denis Chenu
 * @license GPL version 3
 * @version 1.0.1
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
class expressionQuestionForAll extends PluginBase
{
    protected static $description = 'Add QCODE.question for question with subquestion for expression Manager.';
    protected static $name = 'expressionQuestionForAll';

    /** @inheritdoc this plugin didn't have any public method */
    public $allowedPublicMethods = array();

    public function init()
    {
        $this->subscribe('setVariableExpressionEnd', 'addQuestionAll');
    }

    /**
     * Add the question.question for question with sub question
     * @link https://manual.limesurvey.org/ExpressionManagerStart
     */
    public function addQuestionAll()
    {
        $knownVars = $this->event->get('knownVars');
        $language = $this->event->get('language');
        $surveyId =  $this->event->get('surveyId');

        $aQuestionManaged = array(
            \QuestionType::QT_1_ARRAY_DUAL,
            \QuestionType::QT_A_ARRAY_5_POINT,
            \QuestionType::QT_B_ARRAY_10_POINT,
            \QuestionType::QT_C_ARRAY_YES_UNCERTAIN_NO,
            \QuestionType::QT_E_ARRAY_INC_SAME_DEC,
            \QuestionType::QT_F_ARRAY,
            \QuestionType::QT_H_ARRAY_COLUMN,
            \QuestionType::QT_K_MULTIPLE_NUMERICAL,
            \QuestionType::QT_M_MULTIPLE_CHOICE,
            \QuestionType::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS,
            \QuestionType::QT_Q_MULTIPLE_SHORT_TEXT,
        );
        $criteria = new CDbCriteria();
        $criteria->select = array('qid','gid','title');
        $criteria->compare('sid', $surveyId);
        $criteria->compare('parent_qid', 0);
        $criteria->addInCondition('type', $aQuestionManaged);
        $aoQuestions = \Question::model()->findAll($criteria);
        
        $newKnownVars = array();
        foreach ($aoQuestions as $oQuestion) {
            $oQuestionL10n = \QuestionL10n::model()->find("qid = :qid and language = :language", array(":qid" => $oQuestion->qid,":language" => $language));
            $newKnownVars[$oQuestion->title] = array(
                'code' => '',
                'jsName_on' => '',
                'jsName' => '',
                'readWrite' => 'N',
                'qid' => $oQuestion->qid,
                'question' => $oQuestionL10n->question,
            );
        }
        $this->event->append('knownVars', $newKnownVars);
    }
}
