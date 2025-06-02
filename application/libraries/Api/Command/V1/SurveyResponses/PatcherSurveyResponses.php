<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

use LimeSurvey\Api\Command\V1\SurveyPatch\Response\{ExceptionErrorItem,
    ExceptionErrors,
    SurveyResponse,
    TempIdMapItem,
    TempIdMapping,
    ValidationErrors};
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerAnswer;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerAnswerDelete;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerLanguageSettingsUpdate;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionAttributeUpdate;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionCreate;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionDelete;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionGroup;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionGroupL10n;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionGroupReorder;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionL10nUpdate;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionUpdate;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSubQuestion;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSubquestionDelete;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSurveyStatus;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSurveyUpdate;
use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\patch\OpHandlerResponsesDelete;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\patch\OpHandlerResponsesUpdate;
use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\Exception\PersistErrorException;
use LimeSurvey\Models\Services\Exception\QuestionHasConditionsException;
use LimeSurvey\Models\Services\SurveyResponseService;
use LimeSurvey\ObjectPatch\{ObjectPatchException, Op\OpInterface, Op\OpStandard, OpHandler\OpHandlerException, Patcher};
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class PatcherSurveyResponses extends Patcher
{
    protected SurveyResponse $surveyResponse;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ContainerInterface $diContainer,
        SurveyResponse $surveyResponse
    ) {
        $this->surveyResponse = $surveyResponse;
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerResponsesDelete::class
            )
        );

        $this->addOpHandler(
            $diContainer->get(
                OpHandlerResponsesUpdate::class
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
