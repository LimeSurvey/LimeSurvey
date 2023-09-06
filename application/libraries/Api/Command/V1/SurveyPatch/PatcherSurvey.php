<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\ObjectPatch\OpHandler\OpHandlerActiveRecordUpdate;
use LimeSurvey\ObjectPatch\Patcher;
use Survey;
use SurveyLanguageSetting;
use Answer;
use QuestionGroup;
use QuestionGroupL10n;
use Question;
use QuestionL10n;
use QuestionAttribute;
use LimeSurvey\Api\Command\V1\Transformer\Input\{
    TransformerInputAnswer,
    TransformerInputQuestionGroup,
    TransformerInputQuestionGroupL10ns,
    TransformerInputQuestion,
    TransformerInputQuestionL10ns,
    TransformerInputQuestionAttribute
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
        $this->addOpHandlerSurvey($diContainer);
        $this->addOpHandlerLanguageSetting($diContainer);
        $this->addOpHandlerQuestionGroup($diFactory, $diContainer);
        $this->addOpHandlerQuestionGroupL10n($diFactory, $diContainer);
        $this->addOpHandlerQuestionCreate($diFactory, $diContainer);
        $this->addOpHandlerQuestion($diFactory, $diContainer);
        $this->addOpHandlerQuestionL10n($diFactory, $diContainer);
        $this->addOpHandlerQuestionAttribute($diFactory, $diContainer);
        $this->addOpHandlerQuestionAnswer($diFactory, $diContainer);
        $this->addOpHandlerQuestionGroupReorder($diContainer);
    }

    private function addOpHandlerSurvey(ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diContainer->get(
            OpHandlerSurveyUpdate::class
        ));
    }

    private function addOpHandlerLanguageSetting(ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diContainer->get(
            OpHandlerLanguageSettingsUpdate::class
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

    private function addOpHandlerQuestionCreate(FactoryInterface $diFactory, ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diFactory->make(
            OpHandlerQuestionCreate::class,
            [
                'entity' => 'question',
                'model' => Question::model(),
                'transformer' => $diContainer->get(
                    TransformerInputQuestion::class
                ),
                'transformerL10n' => $diContainer->get(
                    TransformerInputQuestionL10ns::class
                ),
                'transformerAttribute' => $diContainer->get(
                    TransformerInputQuestionAttribute::class
                ),
                'transformerAnswer' => $diContainer->get(
                    TransformerInputAnswer::class
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

    private function addOpHandlerQuestionGroupReorder(
        ContainerInterface $diContainer
    ): void {
        $this->addOpHandler($diContainer->get(
            OpHandlerQuestionGroupReorder::class
        ));
    }
}
