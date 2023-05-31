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

use DI\FactoryInterface;
use Psr\Container\ContainerInterface;

class PatcherSurvey extends Patcher
{
    /**
     * Constructor
     *
     * @param FactoryInterface $diFactory
     * @param ContainerInterface $diContainer
     */
    public function __construct(FactoryInterface $diFactory, ContainerInterface $diContainer)
    {
        $opHandlerSurvey = $diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'survey',
                'model' => Survey::model(),
                'transformer' => $diContainer->get(
                    TransformerInputSurvey::class
                )
            ]
        );
        $this->addOpHandler($opHandlerSurvey);

        $opHandlerLanguageSetting = $diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'languageSetting',
                'model' => SurveyLanguageSetting::model(),
                'transformer' => $diContainer->get(
                    TransformerInputSurveyLanguageSettings::class
                )
            ]
        );
        $this->addOpHandler($opHandlerLanguageSetting);

        $opHandlerQuestionGroup = $diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'questionGroup',
                'model' => QuestionGroup::model(),
                'transformer' => $diContainer->get(
                    TransformerInputQuestionGroup::class
                )
            ]
        );
        $this->addOpHandler($opHandlerQuestionGroup);

        $opHandlerQuestionGroupL10n = $diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'questionGroupL10n',
                'model' => QuestionGroupL10n::model(),
                'transformer' => $diContainer->get(
                    TransformerInputQuestionGroupL10ns::class
                )
            ]
        );
        $this->addOpHandler($opHandlerQuestionGroupL10n);

        $opHandlerQuestion = $diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'question',
                'model' => Question::model(),
                'transformer' => $diContainer->get(
                    TransformerInputQuestion::class
                )
            ]
        );
        $this->addOpHandler($opHandlerQuestion);

        $opHandlerQuestionL10n = $diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'questionL10n',
                'model' => QuestionL10n::model(),
                'transformer' => $diContainer->get(
                    TransformerInputQuestionL10ns::class
                )
            ]
        );
        $this->addOpHandler($opHandlerQuestionL10n);

        $opHandlerQuestionAttribute = $diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'questionAttribute',
                'model' => QuestionAttribute::model(),
                'transformer' => $diContainer->get(
                    TransformerInputQuestionAttribute::class
                )
            ]
        );
        $this->addOpHandler($opHandlerQuestionAttribute);

        $opHandlerQuestionAnswer = $diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'questionAnswer',
                'model' => Answer::model(),
                'transformer' => $diContainer->get(
                    TransformerInputAnswer::class
                )
            ]
        );
        $this->addOpHandler($opHandlerQuestionAnswer);
    }
}
