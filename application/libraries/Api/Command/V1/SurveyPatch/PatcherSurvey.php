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
        $this->addOpHandlerSurvey($diFactory, $diContainer);
        $this->addOpHandlerLanguageSetting($diFactory, $diContainer);
        $this->addOpHandlerQuestionGroup($diFactory, $diContainer);
        $this->addOpHandlerQuestionGroupL10n($diFactory, $diContainer);
        $this->addOpHandlerQuestion($diFactory, $diContainer);
        $this->addOpHandlerQuestionL10n($diFactory, $diContainer);
        $this->addOpHandlerQuestionAttribute($diFactory, $diContainer);
        $this->addOpHandlerQuestionAnswer($diFactory, $diContainer);
    }

    private function addOpHandlerSurvey(FactoryInterface $diFactory, ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'survey',
                'model' => Survey::model(),
                'transformer' => $diContainer->get(
                    TransformerInputSurvey::class
                )
            ]
        ));
    }

    private function addOpHandlerLanguageSetting(FactoryInterface $diFactory, ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'languageSetting',
                'model' => SurveyLanguageSetting::model(),
                'transformer' => $diContainer->get(
                    TransformerInputSurveyLanguageSettings::class
                )
            ]
        ));
    }

    private function addOpHandlerQuestionGroup(FactoryInterface $diFactory, ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'questionGroup',
                'model' => QuestionGroup::model(),
                'transformer' => $diContainer->get(
                    TransformerInputQuestionGroup::class
                )
            ]
        ));
    }


    private function addOpHandlerQuestionGroupL10n(FactoryInterface $diFactory, ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'questionGroupL10n',
                'model' => QuestionGroupL10n::model(),
                'transformer' => $diContainer->get(
                    TransformerInputQuestionGroupL10ns::class
                )
            ]
        ));
    }

    private function addOpHandlerQuestion(FactoryInterface $diFactory, ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'question',
                'model' => Question::model(),
                'transformer' => $diContainer->get(
                    TransformerInputQuestion::class
                )
            ]
        ));
    }

    private function addOpHandlerQuestionL10n(FactoryInterface $diFactory, ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'questionL10n',
                'model' => QuestionL10n::model(),
                'transformer' => $diContainer->get(
                    TransformerInputQuestionL10ns::class
                )
            ]
        ));
    }

    private function addOpHandlerQuestionAttribute(FactoryInterface $diFactory, ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'questionAttribute',
                'model' => QuestionAttribute::model(),
                'transformer' => $diContainer->get(
                    TransformerInputQuestionAttribute::class
                )
            ]
        ));
    }

    private function addOpHandlerQuestionAnswer(FactoryInterface $diFactory, ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'questionAnswer',
                'model' => Answer::model(),
                'transformer' => $diContainer->get(
                    TransformerInputAnswer::class
                )
            ]
        ));
    }
}
