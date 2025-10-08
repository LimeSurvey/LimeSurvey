<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use InvalidArgumentException;
use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\FilterPatcher;
use LimeSurvey\Libraries\Api\Command\V1\Transformer\Output\TransformerOutputSurveyResponses;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\DailyActivity\DailyActivityStatistics;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\SurveyOverview\SurveyOverviewStatistics;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsService;
use Permission;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use Survey;
use SurveyDynamic;

/**
 * API Command to retrieve statistical overview of a survey.
 */
class StatisticsOverview implements CommandInterface
{
    use AuthPermissionTrait;

    protected Permission $permission;
    protected Survey $survey;
    protected ResponseFactory $responseFactory;
    protected StatisticsService $statisticsService;
    protected FilterPatcher $responseFilterPatcher;
    protected TransformerOutputSurveyResponses $transformerOutputSurveyResponses;

    /** @var int Survey ID being processed */
    private int $surveyId = 0;
    /** @var string Survey language code */
    private $language = 'en';


    /**
     * @param ResponseFactory $responseFactory
     * @param Permission $permission
     * @param Survey $survey
     * @param StatisticsService $statisticsService
     * @param FilterPatcher $responseFilterPatcher
     * @param TransformerOutputSurveyResponses $transformerOutputSurveyResponses
     */
    public function __construct(
        Permission $permission,
        Survey $survey,
        ResponseFactory $responseFactory,
        StatisticsService $statisticsService,
        FilterPatcher $responseFilterPatcher,
        TransformerOutputSurveyResponses $transformerOutputSurveyResponses
    ) {
        $this->permission = $permission;
        $this->survey = $survey;
        $this->responseFactory = $responseFactory;
        $this->statisticsService = $statisticsService;
        $this->responseFilterPatcher = $responseFilterPatcher;
        $this->transformerOutputSurveyResponses = $transformerOutputSurveyResponses;
    }

    /**
     * Execute the statistics overview command
     * Retrieves and combines:
     * - Survey statistics
     * - Daily activity data
     * - Latest responses
     *
     * @param Request $request API request containing survey ID and language
     * @return Response Success response with overview data or error response
     */
    public function run(Request $request)
    {
        $this->surveyId = (int)$request->getData('_id');
        $this->language = (string)$request->getData('language', 'en');


        $survey = $this->survey->findByPk($this->surveyId);
        if (empty($survey)) {
            return $this->responseFactory->makeErrorNotFound('Survey not found');
        }

        // Check if user has permission to view statistics
        if (!$this->permission->hasSurveyPermission($this->surveyId, 'statistics')) {
            return $this->responseFactory->makeErrorUnauthorised();
        }

        try {
            $latestResponses = $this->getLatestResponses();
            [$dailyActivity, $overview] = $this->getStatisticsOverviewData();

            $data = [
                // Survey statistics overview
                'statistics' => $overview['data'],
                // Daily activity (last 30 days)
                'dailyActivity' => $dailyActivity,
                // Latest 10 responses
                'responses' => $latestResponses,
            ];
        } catch (InvalidArgumentException $exception) {
            return $this->responseFactory->makeErrorBadRequest($exception->getMessage());
        } catch (TransformerException $exception) {
            return $this->responseFactory->makeErrorBadRequest($exception->getMessage());
        }

        return $this->responseFactory->makeSuccess(['overview' => $data]);
    }

    /**
     * Retrieve statistics overview and daily activity data
     *
     * @return array Array containing daily activity and overview data
     */
    private function getStatisticsOverviewData(): array
    {
        // Set up statistics overview chart because it's not part of the default set
        $this->statisticsService->setChart(SurveyOverviewStatistics::class);
        $this->statisticsService->setSurvey($this->surveyId, $this->language);

        return $this->statisticsService->run([
            DailyActivityStatistics::class,
            SurveyOverviewStatistics::class
        ]);
    }

    /**
     * Retrieve the latest survey responses
     *
     * Configures and executes a paginated query to get the most recent
     * survey responses, sorted by ID in descending order.
     *
     * @param int $size
     * @return array Transformed array of latest survey responses
     * @throws TransformerException
     */
    private function getLatestResponses($size = 10): array
    {
        $model = SurveyDynamic::model($this->surveyId);
        $criteria = new \LSDbCriteria();
        $sort = new \CSort();
        $this->responseFilterPatcher->apply(
            ['sort' => ['id' => 'DESC']],
            $criteria,
            $sort,
            $this->transformerOutputSurveyResponses->getDataMap()
        );

        $dataProvider = new \LSCActiveDataProvider(
            $model,
            array(
                'sort' => $sort,
                'criteria' => $criteria,
                'pagination' => [
                    'pageSize' => $size,
                    'currentPage' => 0,
                ],
            )
        );

        $result = $dataProvider->getData();
        if ($survey = $this->survey->findByPk($this->surveyId)) {
            $this->transformerOutputSurveyResponses->fieldMap = createFieldMap($survey);
        }

        return $this->transformerOutputSurveyResponses->transform($result);
    }
}
