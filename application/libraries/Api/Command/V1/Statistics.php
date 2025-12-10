<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use CDbException;
use InvalidArgumentException;
use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
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
use Yii;

class Statistics implements CommandInterface
{
    use AuthPermissionTrait;

    protected Permission $permission;
    protected TransformerOutputSurvey $transformerOutputSurvey;
    protected ResponseFactory $responseFactory;
    protected StatisticsService $statisticsService;
    protected StatisticsResponseFilters $filters;

    /**
     * Constructor
     *
     * @param TransformerOutputSurvey $transformerOutputSurvey
     * @param FactoryInterface $diFactory
     * @param ResponseFactory $responseFactory
     * @param StatisticsResponseFilters $filters
     * @param StatisticsService $statisticsService
     */
    public function __construct(
        TransformerOutputSurvey $transformerOutputSurvey,
        Permission $permission,
        ResponseFactory $responseFactory,
        StatisticsResponseFilters $filters,
        StatisticsService $statisticsService
    ) {
        $this->permission = $permission;
        $this->transformerOutputSurvey = $transformerOutputSurvey;
        $this->responseFactory = $responseFactory;
        $this->statisticsService = $statisticsService;
        $this->filters = $filters;
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

        try {
            $this->statisticsService->setSurvey($surveyId, $request->getData('language'));

            if ($this->getFilters()->count() > 0) {
                $this->statisticsService->setFilters($this->filters);
            }

            $statistics = $this->statisticsService->run();
        } catch (InvalidArgumentException $exception) {
            return $this->responseFactory->makeErrorBadRequest($exception->getMessage());
        } catch (NotFoundException $exception) {
            return $this->responseFactory->makeErrorNotFound($exception->getMessage());
        } catch (CDbException $exception) {
            if ($exception->getCode() === 42) {
                return $this->responseFactory->makeErrorBadRequest("Survey table with ID {$surveyId} does not exist.");
            }
            throw $exception;
        }

        return $this->responseFactory
            ->makeSuccess([
                'statistics' => $statistics,
            ]);
    }

    public function getFilters(): StatisticsResponseFilters
    {
        $params = Yii::app()->getRequest()->getQueryParams();
        $filterMap = [
            'minId' => 'setMinId',
            'maxId' => 'setMaxId',
            'completed' => 'setCompleted',
        ];

        foreach ($filterMap as $key => $method) {
            $value = $params[$key] ?? null;
            if ($value !== null) {
                $this->filters->$method(
                    $key === 'completed' ? $value === 'true' : (int)$value
                );
            }
        }

        return $this->filters;
    }
}
