<?php

/**
 * Class used when creating new question.
 */
class QuestionCreate extends Question
{
    /**
     * @todo This is a factory method, not a singleton. Rename to make() or create().
     */
    public static function getInstance($iSurveyId, $type = null, $themeName = null)
    {
        $oSurvey = Survey::model()->findByPk($iSurveyId);
        if (empty($oSurvey)) {
            throw new Exception('Found no survey with id ' . json_encode($iSurveyId));
        }
        $gid = Yii::app()->request->getParam('gid', 0);
        if ($gid == 0) {
            $gid = array_values($oSurvey->groups)[0]->gid;
        }
        if (isset($type) && !empty($type)) {
            $questionType = $type;
        } else {
            $questionType = SettingsUser::getUserSettingValue('preselectquestiontype', null, null, null, Yii::app()->getConfig('preselectquestiontype'));
        }
        if (isset($themeName) && !empty($themeName)) {
            $questionThemeName = $themeName;
        } else {
            $questionThemeName = SettingsUser::getUserSettingValue('preselectquestiontheme', null, null, null, Yii::app()->getConfig('preselectquestiontheme'));
        }
        if (empty($questionThemeName)) {
            $questionThemeName = QuestionTheme::model()->getBaseThemeNameForQuestionType($questionType);
        }
        $oCurrentGroup = QuestionGroup::model()->findByPk($gid);
        $temporaryTitle =
            'G' . str_pad((string) $oCurrentGroup->group_order, 2, '0', STR_PAD_LEFT)
            . 'Q' . str_pad((safecount($oSurvey->baseQuestions) + 1), 2, '0', STR_PAD_LEFT);
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
                'question_theme_name' => $questionThemeName,
        ];

        $oQuestion = new QuestionCreate();
        $oQuestion->qid = 0;
        $oQuestion->setAttributes($aQuestionData, false);
        if ($oQuestion == null) {
            throw new CException("Object creation failed, input array malformed or invalid");
        }

        $l10n = [];
        foreach ($oSurvey->allLanguages as $sLanguage) {
            $l10n[$sLanguage] = new QuestionL10n();
            $l10n[$sLanguage]->setAttributes([
                'qid' => $oQuestion->qid,
                'language' => $sLanguage,
                'question' => '',
                'help' => '',
            ], false);
        }
        $oQuestion->questionl10ns = $l10n;

        return $oQuestion;
    }

    /**
     * @inheritdoc
     * @todo check why return both empty array and null?
     */
    public function getOrderedAnswers($scale_id = null, $language = null)
    {
        if ($scale_id == null) {
            return [];
        }
        if ($this->questionType->subquestions >= 1) {
            return  $this->questionType->subquestions == 1 ? [[]] : [[],[]];
        }
        return null;
    }

    /**
     * @param int|null $scale_id
     * @return array|null
     */
    public function getOrderedSubQuestions($scale_id = null)
    {
        if ($scale_id == null) {
            return [];
        }
        if ($this->questionType->answerscales >= 1) {
            return $this->questionType->answerscales == 1 ? [[]] : [[],[]];
        }
        return null;
    }
}
