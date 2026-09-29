<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

use LimeSurvey\Api\Command\V1\SurveyPatch\Response\SurveyResponse;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\patch\OpHandlerResponsesDelete;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\patch\OpHandlerResponsesFileDelete;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\patch\OpHandlerResponsesUpdate;
use LimeSurvey\ObjectPatch\{ObjectPatchException, Op\OpStandard, OpHandler\OpHandlerException, Patcher};
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class PatcherSurveyResponses extends Patcher
{
    protected SurveyResponse $surveyResponse;

    private array $handlers = [
        OpHandlerResponsesDelete::class,
        OpHandlerResponsesUpdate::class,
        OpHandlerResponsesFileDelete::class,
    ];

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ContainerInterface $diContainer,
        SurveyResponse $surveyResponse
    ) {
        $this->surveyResponse = $surveyResponse;

        foreach ($this->handlers as $handler) {
            $this->addOpHandler(
                $diContainer->get($handler)
            );
        }
    }

    /**
     * Apply patch
     *
     * @param  ?mixed $patch
     * @param  ?array $context
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
