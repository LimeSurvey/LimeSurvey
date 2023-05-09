<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use Survey;
use SurveyLanguageSetting;
use Answer;
use QuestionGroup;
use QuestionGroupL10n;
use Question;
use QuestionL10n;
use QuestionAttribute;

use LimeSurvey\Api\Command\V1\Transformer\Input\{
    TransformerInputSurvey,
    TransformerInputAnswer,
    TransformerInputQuestionGroup,
    TransformerInputQuestionGroupL10ns,
    TransformerInputQuestion,
    TransformerInputQuestionL10ns,
    TransformerInputQuestionAttribute,
    TransformerInputSurveyLanguageSettings
};

use LimeSurvey\ObjectPatch\{
    Patcher,
    OpHandler\OpHandlerActiveRecordUpdate
};

class PatcherSurvey extends Patcher
{
    public function __construct()
    {
        $this->addOpHandler(
            new OpHandlerActiveRecordUpdate(
                'survey',
                Survey::model(),
                new TransformerInputSurvey
            )
        );
        $this->addOpHandler(
            new OpHandlerActiveRecordUpdate(
                'languageSetting',
                SurveyLanguageSetting::model(),
                new TransformerInputSurveyLanguageSettings
            )
        );
        $this->addOpHandler(
            new OpHandlerActiveRecordUpdate(
                'questionGroup',
                QuestionGroup::model(),
                new TransformerInputQuestionGroup
            )
        );
        $this->addOpHandler(
            new OpHandlerActiveRecordUpdate(
                'questionGroupL10n',
                QuestionGroupL10n::model(),
                new TransformerInputQuestionGroupL10ns
            )
        );
        $this->addOpHandler(
            new OpHandlerActiveRecordUpdate(
                'question',
                Question::model(),
                new TransformerInputQuestion
            )
        );
        $this->addOpHandler(
            new OpHandlerActiveRecordUpdate(
                'questionL10n',
                QuestionL10n::model(),
                new TransformerInputQuestionL10ns
            )
        );
        $this->addOpHandler(
            new OpHandlerActiveRecordUpdate(
                'questionAttribute',
                QuestionAttribute::model(),
                new TransformerInputQuestionAttribute
            )
        );
        $this->addOpHandler(
            new OpHandlerActiveRecordUpdate(
                'questionAnswer',
                Answer::model(),
                new TransformerInputAnswer
            )
        );
    }
}
