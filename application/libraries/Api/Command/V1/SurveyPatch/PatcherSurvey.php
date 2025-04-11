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
        $classes = [
            OpHandlerSurveyUpdate::class,
            OpHandlerLanguageSettingsUpdate::class,
            OpHandlerQuestionGroup::class,
            OpHandlerQuestionGroupL10n::class,
            OpHandlerQuestionDelete::class,
            OpHandlerQuestionCreate::class,
            OpHandlerQuestionUpdate::class,
            OpHandlerQuestionL10nUpdate::class,
            OpHandlerAnswer::class,
            OpHandlerQuestionAttributeUpdate::class,
            OpHandlerQuestionGroupReorder::class,
            OpHandlerSubquestionDelete::class,
            OpHandlerAnswerDelete::class,
            OpHandlerSubQuestion::class,
            OpHandlerSurveyStatus::class,
            OpHandlerThemeSettings::class,
        ];

        foreach ($classes as $class) {
            $this->addOpHandler(
                $diContainer->get(
                    $class
                )
            );
        }
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
