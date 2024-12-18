<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\Response\{ExceptionErrorItem,
    ExceptionErrors,
    SurveyResponse,
    TempIdMapItem,
    TempIdMapping,
    ValidationErrors};
use LimeSurvey\ObjectPatch\{
    ObjectPatchException,
    Op\OpStandard,
    OpHandler\OpHandlerException,
    Patcher
};
use Psr\Container\ContainerInterface;

class PatcherSurvey extends Patcher
{
    protected SurveyResponse $surveyResponse;

    /**
     * Constructor
     *
     * @param ContainerInterface $diContainer
     * @param SurveyResponse $surveyResponse
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct(
        ContainerInterface $diContainer,
        SurveyResponse $surveyResponse
    ) {
        $this->surveyResponse = $surveyResponse;
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerSurveyUpdate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerLanguageSettingsUpdate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionGroup::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionGroupL10n::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionDelete::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionCreate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionUpdate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionL10nUpdate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerAnswer::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionAttributeUpdate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionGroupReorder::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerSubquestionDelete::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerAnswerDelete::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerSubQuestion::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerSurveyStatus::class
            )
        );

        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionCondition::class
            )
        );
    }

    /**
     * Apply patch
     *
     * @param ?mixed $patch
     * @param ?array $context
     * @return array
     * @throws ObjectPatchException
     * @throws OpHandlerException
     */
    public function applyPatch($patch, $context = []): array
    {
        if (is_array($patch) && !empty($patch)) {
            foreach ($patch as $patchOpData) {
                $op = OpStandard::factory(
                    $patchOpData['entity'] ?? '',
                    $patchOpData['op'] ?? '',
                    $patchOpData['id'] ?? null,
                    $patchOpData['props'] ?? [],
                    $context ?? []
                );
                try {
                    $response = $this->handleOp($op);
                    $this->surveyResponse->handleResponse($response);
                } catch (\Exception $e) {
                    $this->surveyResponse->handleException($e, $op);
                }
            }
        }
        return $this->surveyResponse->buildResponseObject();
    }
}
