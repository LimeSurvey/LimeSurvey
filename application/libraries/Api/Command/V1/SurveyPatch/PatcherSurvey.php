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
use LimeSurvey\Models\Services\SurveyDetailService;

class PatcherSurvey extends Patcher
{
    protected SurveyResponse $surveyResponse;
    protected SurveyDetailService $surveyDetailService;

    /**
     * Constructor
     *
     * @param ContainerInterface $diContainer
     * @param SurveyResponse $surveyResponse
     * * @param SurveyDetailService $surveyDetailService
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct(
        ContainerInterface $diContainer,
        SurveyResponse $surveyResponse,
        SurveyDetailService $surveyDetailService
    ) {
        $this->surveyResponse = $surveyResponse;
        $this->surveyDetailService = $surveyDetailService;
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
            OpHandlerQuestionCondition::class,
            OpHandlerImport::class,
            OpHandlerThemeSettings::class,
            OpHandlerSurveyAccessMode::class,
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
            $entityMap = [];
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
                    $entityMap[$patchOpData['entity']] = $patchOpData['id'] ?? null;
                } catch (\Exception $e) {
                    $this->surveyResponse->handleException($e, $op);
                }
            }
            $survey = $this->surveyDetailService->getSurveyFromEntityMap($entityMap);
            if ($survey) {
                $survey->lastmodified = gmdate('Y-m-d H:i:s');
                $survey->save();
                $this->surveyDetailService->removeCache($survey->sid);
            }
        }
        return $this->surveyResponse->buildResponseObject();
    }
}
