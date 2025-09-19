<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use LimeSurvey\Models\Services\SurveyStatistics\StatisticsService;
use Permission;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurvey;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use DI\FactoryInterface;

class Statistics implements CommandInterface
{
    use AuthPermissionTrait;

    protected Permission $permission;
    protected TransformerOutputSurvey $transformerOutputSurvey;
    protected ResponseFactory $responseFactory;
    protected StatisticsService $statisticsService;

    /**
     * Constructor
     *
     * @param TransformerOutputSurvey $transformerOutputSurvey
     * @param FactoryInterface $diFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        TransformerOutputSurvey $transformerOutputSurvey,
        Permission $permission,
        ResponseFactory $responseFactory,
        StatisticsService $statisticsService
    ) {
        $this->permission = $permission;
        $this->transformerOutputSurvey = $transformerOutputSurvey;
        $this->responseFactory = $responseFactory;
        $this->statisticsService = $statisticsService;
    }

    /**
     * Run survey list command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $surveyId = (int)$request->getData('_id');
        if (!$this->permission->hasSurveyPermission($surveyId, 'statistics')) {
            return $this->responseFactory
                ->makeErrorUnauthorised();
        }

        $this->statisticsService->setSurvey($surveyId, $request->getData('language') ?? 'en');

        $statistics = $this->statisticsService->run();

        return $this->responseFactory
            ->makeSuccess([
                'statistics' => $statistics,
            ]);
    }
}
