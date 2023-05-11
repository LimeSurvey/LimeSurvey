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

use CModel;
use LimeSurvey\Api\Transformer\TransformerInterface;

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
    OpHandler\OpHandlerActiveRecordUpdate,
    OpHandler\OpHandlerActiveRecordDelete,
    OpHandler\OpHandlerActiveRecordCreate
};

class PatcherSurvey extends Patcher
{
    public function __construct()
    {
        $this->addCrud(
            'survey',
            Survey::model(),
            new TransformerInputSurvey
        );

        $this->addCrud(
            'languageSetting',
            SurveyLanguageSetting::model(),
            new TransformerInputSurveyLanguageSettings
        );

        $this->addCrud(
            'questionGroup',
            QuestionGroup::model(),
            new TransformerInputQuestionGroup
        );

        $this->addCrud(
            'questionGroupL10n',
            QuestionGroupL10n::model(),
            new TransformerInputQuestionGroupL10ns
        );

        $this->addCrud(
            'question',
            Question::model(),
            new TransformerInputQuestion
        );

        $this->addCrud(
            'questionL10n',
            QuestionL10n::model(),
            new TransformerInputQuestionL10ns
        );

        $this->addCrud(
            'questionAttribute',
            QuestionAttribute::model(),
            new TransformerInputQuestionAttribute
        );

        $this->addCrud(
            'questionAnswer',
            Answer::model(),
            new TransformerInputAnswer
        );
    }

    private function addCrud(
        $entity,
        CModel $model,
        TransformerInterface $transformer = null
    )
    {
        $this->addOpHandler(
            new OpHandlerActiveRecordUpdate(
                $entity,
                $model,
                $transformer
            )
        );
        $this->addOpHandler(
            new OpHandlerActiveRecordDelete(
                $entity,
                $model,
                $transformer
            )
        );
        $this->addOpHandler(
            new OpHandlerActiveRecordCreate(
                $entity,
                $model,
                $transformer
            )
        );
    }
}
