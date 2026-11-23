<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use CDbException;
use InvalidArgumentException;
use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSetService;
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
use LimeSurvey\Libraries\Api\Command\V1\Statistics\StatisticsFilterParamsTrait;
use DI\FactoryInterface;

class Statistics implements CommandInterface
{
    use AuthPermissionTrait;
    use StatisticsFilterParamsTrait;

    /** Default number of charts returned per page */
    private const DEFAULT_PAGE_SIZE = 15;

    private const MAX_PAGE_SIZE = 100;

    protected Permission $permission;
    protected TransformerOutputSurvey $transformerOutputSurvey;
    protected ResponseFactory $responseFactory;
    protected StatisticsService $statisticsService;
    protected StatisticsResponseFilters $filters;
    protected ResponseFilterSetService $filterSetService;

    /**
     * Constructor
     *
     * @param TransformerOutputSurvey $transformerOutputSurvey
     * @param Permission $permission
     * @param ResponseFactory $responseFactory
     * @param StatisticsResponseFilters $filters
     * @param StatisticsService $statisticsService
     * @param ResponseFilterSetService $filterSetService
     */
    public function __construct(
        TransformerOutputSurvey $transformerOutputSurvey,
        Permission $permission,
        ResponseFactory $responseFactory,
        StatisticsResponseFilters $filters,
        StatisticsService $statisticsService,
        ResponseFilterSetService $filterSetService
    ) {
        $this->permission = $permission;
        $this->transformerOutputSurvey = $transformerOutputSurvey;
        $this->responseFactory = $responseFactory;
        $this->statisticsService = $statisticsService;
        $this->filters = $filters;
        $this->filterSetService = $filterSetService;
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

            if ($this->getFilters($request)->count() > 0) {
                $this->statisticsService->setFilters($this->filters);
            }

            $this->statisticsService->setResolvedFilters(
                $this->resolveFilterSet(
                    $request,
                    $this->statisticsService->getSurvey(),
                    $this->statisticsService->getLanguage()
                )
            );

            $this->statisticsService->setPagination(
                $this->getPage($request),
                $this->getPageSize($request)
            );

            $statistics = $this->statisticsService->run();
        } catch (InvalidArgumentException $exception) {
            return $this->responseFactory->makeErrorBadRequest($exception->getMessage());
        } catch (PermissionDeniedException $exception) {
            return $this->responseFactory->makeErrorUnauthorised();
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
                'pagination' => $this->statisticsService->getPaginationMeta(),
            ]);
    }

    /**
     * The page of charts to return, from the `page` param.
     *
     * @param Request $request
     * @return int Zero-based page number, never negative
     */
    private function getPage(Request $request): int
    {
        $page = $request->getData('page', 0);
        return max(0, (int)$page);
    }

    /**
     * The number of charts per page, from the `pageSize` param.
     *
     * @param Request $request
     * @return int Between 1 and MAX_PAGE_SIZE
     */
    private function getPageSize(Request $request): int
    {
        $pageSize = $request->getData('pageSize', self::DEFAULT_PAGE_SIZE);
        return min(self::MAX_PAGE_SIZE, max(1, (int)$pageSize));
    }
}
