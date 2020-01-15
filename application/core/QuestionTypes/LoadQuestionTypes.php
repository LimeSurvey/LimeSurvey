<?php

class LoadQuestionTypes
{
    public static function loadAll()
    {
        Yii::import('questiontypes.5PointChoice.*');
        Yii::import('questiontypes.Array10ChoiceQuestions.*');
        Yii::import('questiontypes.Array5ChoiceQuestions.*');
        Yii::import('questiontypes.ArrayFlexibleColumn.*');
        Yii::import('questiontypes.ArrayFlexibleRow.*');
        Yii::import('questiontypes.ArrayMultiFlexNumbers.*');
        Yii::import('questiontypes.ArrayMultiFlexText.*');
        Yii::import('questiontypes.ArrayMultiscale.*');
        Yii::import('questiontypes.ArrayOfIncSameDecQuestions.*');
        Yii::import('questiontypes.ArrayYesUncertainNo.*');
        Yii::import('questiontypes.BoilerplateQuestion.*');
        Yii::import('questiontypes.Date.*');
        Yii::import('questiontypes.DummyQuestion.*');
        Yii::import('questiontypes.Equation.*');
        Yii::import('questiontypes.FileUpload.*');
        Yii::import('questiontypes.GenderDropdown.*');
        Yii::import('questiontypes.HugeFreeText.*');
        Yii::import('questiontypes.Language.*');
        Yii::import('questiontypes.ListDropdown.*');
        Yii::import('questiontypes.ListRadio.*');
        Yii::import('questiontypes.ListRadioFlexible.*');
        Yii::import('questiontypes.ListWithComment.*');
        Yii::import('questiontypes.LongFreeText.*');
        Yii::import('questiontypes.MultipleChoice.*');
        Yii::import('questiontypes.MultipleChoiceWithComments.*');
        Yii::import('questiontypes.MultipleNumericalQuestion.*');
        Yii::import('questiontypes.MultipleShortText.*');
        Yii::import('questiontypes.Numerical.*');
        Yii::import('questiontypes.RankingStyle.*');
        Yii::import('questiontypes.ShortFreeText.*');
        Yii::import('questiontypes.YesNoRadio.*');
    }

    public static function load($type)
    {
        switch($type) {
            case Question::QT_X_BOILERPLATE_QUESTION:            Yii::import('questiontypes.BoilerplateQuestion.*'); break;
            case Question::QT_5_POINT_CHOICE:                    Yii::import('questiontypes.5PointChoice.*'); break;
            case Question::QT_ASTERISK_EQUATION:                 Yii::import('questiontypes.Equation.*'); break;
            case Question::QT_D_DATE:                            Yii::import('questiontypes.Date.*'); break;
            case Question::QT_1_ARRAY_MULTISCALE:                Yii::import('questiontypes.ArrayMultiscale.*'); break;
            case Question::QT_L_LIST_DROPDOWN:                   Yii::import('questiontypes.ListRadio.*'); break;
            case Question::QT_EXCLAMATION_LIST_DROPDOWN:         Yii::import('questiontypes.ListDropdown.*'); break;
            case Question::QT_O_LIST_WITH_COMMENT:               Yii::import('questiontypes.ListWithComment.*'); break;
            case Question::QT_R_RANKING_STYLE:                   Yii::import('questiontypes.RankingStyle.*'); break;
            case Question::QT_M_MULTIPLE_CHOICE:                 Yii::import('questiontypes.MultipleChoice.*'); break;
            case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:   Yii::import('questiontypes.MultipleChoiceWithComments.*'); break;
            case Question::QT_I_LANGUAGE:                        Yii::import('questiontypes.Language.*'); break;
            case Question::QT_Q_MULTIPLE_SHORT_TEXT:             Yii::import('questiontypes.MultipleShortText.*'); break;
            case Question::QT_T_LONG_FREE_TEXT:                  Yii::import('questiontypes.LongFreeText.*'); break;
            case Question::QT_U_HUGE_FREE_TEXT:                  Yii::import('questiontypes.HugeFreeText.*'); break;
            case Question::QT_K_MULTIPLE_NUMERICAL_QUESTION:     Yii::import('questiontypes.MultipleNumericalQuestion.*');break;
            case Question::QT_A_ARRAY_5_CHOICE_QUESTIONS:        Yii::import('questiontypes.Array5ChoiceQuestions.*'); break;
            case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS:       Yii::import('questiontypes.Array10ChoiceQuestions.*'); break;
            case Question::QT_C_ARRAY_YES_UNCERTAIN_NO:          Yii::import('questiontypes.ArrayYesUncertainNo.*'); break;
            case Question::QT_E_ARRAY_OF_INC_SAME_DEC_QUESTIONS: Yii::import('questiontypes.ArrayOfIncSameDecQuestions.*'); break;
            case Question::QT_F_ARRAY_FLEXIBLE_ROW:              Yii::import('questiontypes.ArrayFlexibleRow.*'); break;
            case Question::QT_G_GENDER_DROPDOWN:                 Yii::import('questiontypes.GenderDropdown.*'); break;
            case Question::QT_H_ARRAY_FLEXIBLE_COLUMN:           Yii::import('questiontypes.ArrayFlexibleColumn.*'); break;
            case Question::QT_N_NUMERICAL:                       Yii::import('questiontypes.Numerical.*'); break;
            case Question::QT_S_SHORT_FREE_TEXT:                 Yii::import('questiontypes.ShortFreeText.*'); break;
            case Question::QT_Y_YES_NO_RADIO:                    Yii::import('questiontypes.YesNoRadio.*'); break;
            case Question::QT_Z_LIST_RADIO_FLEXIBLE:             Yii::import('questiontypes.ListRadioFlexible.*'); break;
            case Question::QT_COLON_ARRAY_MULTI_FLEX_NUMBERS:    Yii::import('questiontypes.ArrayMultiFlexNumbers.*'); break;
            case Question::QT_SEMICOLON_ARRAY_MULTI_FLEX_TEXT:   Yii::import('questiontypes.ArrayMultiFlexText.*'); break;
            case Question::QT_VERTICAL_FILE_UPLOAD:              Yii::import('questiontypes.FileUpload.*'); break;
            default: Yii::import('questiontypes.DummyQuestion.*'); break;
        }
    }
}
