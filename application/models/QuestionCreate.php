<?php

class QuestionCreate extends Question
{
    public static function getInstance($iSurveyId, $type)
    {
        $temporaryTitle = SettingsUser::getUserSettingValue('temporaryTitle', SettingsUser::ENTITY_SURVEY, $iSurveyId);
        if ($temporaryTitle == null) {
            $temporaryTitle = 'tempX'.$iSurveyId.substr(md5(time()), 4, 9);
            SettingsUser::setUserSetting('temporaryTitle', $temporaryTitle, SettingsUser::ENTITY_SURVEY, $iSurveyId);
        }

        $oSurvey = Survey::model()->findByPk($iSurveyId);
        $aQuestionData = [
                'sid' => $iSurveyId,
                'gid' => Yii::app()->request->getParam('gid'),
                'type' => SettingsUser::getUserSettingValue('preselectquestiontype', null, null, null, Yii::app()->getConfig('preselectquestiontype')),
                'other' => 'N',
                'mandatory' => 'N',
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
        $oQuestion->questionL10ns = $i10N;

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
