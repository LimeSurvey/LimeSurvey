<?php

class LoadQuestionTypes {
    public static function loadAll() {
        Yii::import('questiontypes.5pointchoice.*');
        Yii::import('questiontypes.array10choicequestions.*');
        Yii::import('questiontypes.array5choicequestions.*');
        Yii::import('questiontypes.arrayflexiblecolumn.*');
        Yii::import('questiontypes.arrayflexiblerow.*');
        Yii::import('questiontypes.arraymultiflexnumbers.*');
        Yii::import('questiontypes.arraymultiflextext.*');
        Yii::import('questiontypes.arraymultiscale.*');
        Yii::import('questiontypes.arrayofincsamedecquestions.*');
        Yii::import('questiontypes.arrayyesuncertainno.*');
        Yii::import('questiontypes.boilerplatequestion.*');
        Yii::import('questiontypes.date.*');
        Yii::import('questiontypes.dummyquestion.*');
        Yii::import('questiontypes.equation.*');
        Yii::import('questiontypes.fileupload.*');
        Yii::import('questiontypes.genderdropdown.*');
        Yii::import('questiontypes.hugefreetext.*');
        Yii::import('questiontypes.language.*');
        Yii::import('questiontypes.listdropdown.*');
        Yii::import('questiontypes.listradio.*');
        Yii::import('questiontypes.listradioflexible.*');
        Yii::import('questiontypes.listwithcomment.*');
        Yii::import('questiontypes.longfreetext.*');
        Yii::import('questiontypes.multiplechoice.*');
        Yii::import('questiontypes.multiplechoicewithcomments.*');
        Yii::import('questiontypes.multiplenumericalquestion.*');
        Yii::import('questiontypes.multipleshorttext.*');
        Yii::import('questiontypes.numerical.*');
        Yii::import('questiontypes.rankingstyle.*');
        Yii::import('questiontypes.shortfreetext.*');
        Yii::import('questiontypes.yesnoradio.*');                       
    }
}
