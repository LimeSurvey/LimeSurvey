<?php

class QuestionCreate extends Question
{
    public static function getInstance($iSurveyId, $type)
    {
        $oSurvey = Survey::model()->findByPk($iSurveyId);
        $gid = Yii::app()->request->getParam('gid', 0);
        if($gid == 0) {
            $gid = array_values($oSurvey->groups)[0]->gid;
        }
        if (isset($type) && !empty($type)){
            $questionType = $type;
        } else {
            $questionType = SettingsUser::getUserSettingValue('preselectquestiontype', null, null, null, Yii::app()->getConfig('preselectquestiontype'));
        }
        $oCurrentGroup = QuestionGroup::model()->findByPk($gid);
        $temporaryTitle = 'G'.str_pad($oCurrentGroup->group_order, 2, '0', STR_PAD_LEFT).'Q'.str_pad((safecount($oSurvey->baseQuestions)+1), 2, '0', STR_PAD_LEFT);
        $aQuestionData = [
                'sid' => $iSurveyId,
                'gid' => $gid,
                'type' => $questionType,
                'other' => 'N',
                'mandatory' => 'N',
                'same_default' => 1,
                'relevance' => 1,
                'group_name' => '',
                'modulename' => '',
                'title' => $temporaryTitle,
                'question_order' => 9999,
        ];

        $oQuestion = new QuestionCreate();
        $oQuestion->setAttributes($aQuestionData, false);
        if ($oQuestion == null) {
            throw new CException("Object creation failed, input array malformed or invalid");
        }
        
        $i10N = [];
        foreach ($oSurvey->allLanguages as $sLanguage) {
            $i10N[$sLanguage] = new QuestionL10n();
            $i10N[$sLanguage]->setAttributes([
                'qid' => $oQuestion->qid,
                'language' => $sLanguage,
                'question' => '',
                'help' => '',
            ], false);
        }
        $oQuestion->questionl10ns = $i10N;

        return $oQuestion;
    }

    public function getOrderedAnswers($scale_id=null) {
        if($scale_id == null) {
            return [];
        }
        if($this->questionType->subquestions >= 1) {
            return  $this->questionType->subquestions == 1 ? [[]] : [[],[]];
        }
        return null;
    }
    public function getOrderedSubQuestions($scale_id=null) {
        if($scale_id == null) {
            return [];
        }
        if($this->questionType->answerscales >= 1) {
            return $this->questionType->answerscales == 1 ? [[]] : [[],[]];
        }
        return null;
    }
}
