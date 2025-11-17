<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\FilterPatcher;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\SurveyOverview\SurveyOverviewStatistics;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsService;
use Permission;
use Survey;
use LimeSurvey\Api\Command\{CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

class SurveyStatisticsGlance implements CommandInterface
{
    use AuthPermissionTrait;

    protected Survey $survey;
    protected Permission $permission;
    protected ResponseFactory $responseFactory;
    protected FilterPatcher $responseFilterPatcher;
    protected StatisticsService $statisticsService;

    /** @var int Survey ID being processed */
    private int $surveyId = 0;

    /** @var string Survey language code */
    private $language = '';

    /**
     * Constructor
     *
     * @param Permission $permission
     * @param FilterPatcher $responseFilterPatcher
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        Survey $survey,
        Permission $permission,
        FilterPatcher $responseFilterPatcher,
        ResponseFactory $responseFactory,
        StatisticsService $statisticsService,
    ) {
        $this->permission = $permission;
        $this->responseFactory = $responseFactory;
        $this->responseFilterPatcher = $responseFilterPatcher;
        $this->statisticsService = $statisticsService;
        $this->survey = $survey;
    }

    /**
     * Run survey detail command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $this->surveyId = (int)$request->getData('_id');

        if (!$this->permission->hasSurveyPermission($this->surveyId, 'statistics')) {
            return $this->responseFactory->makeErrorUnauthorised();
        }

        $survey = $this->survey->findByPk($this->surveyId);

        if (empty($survey)) {
            return $this->responseFactory->makeErrorNotFound('Survey not found');
        }

        $this->language = $request->getData('language', $survey->language);

        $data = $this->process();

        return $this->responseFactory->makeSuccess(['statistics' => $data]);
    }

    private function process(): array
    {
        $this->statisticsService->setChart(SurveyOverviewStatistics::class);
        $this->statisticsService->setSurvey($this->surveyId, $this->language);

        [$data] = $this->statisticsService->run([
            SurveyOverviewStatistics::class,
        ]);

        return $data['data'] ?? [];
    }
}
