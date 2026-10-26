<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use CDbException;
use InvalidArgumentException;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use LimeSurvey\Libraries\Api\Command\V1\Statistics\StatisticsFilterParamsTrait;
use LimeSurvey\Models\Services\SurveyStatistics\DateTimeValues;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
use Permission;
use Yii;

/**
 * The entered values of one Date/time question, grouped by day and time,
 * for the day breakdown and date range views of its statistics card.
 */
class StatisticsDateValues implements CommandInterface
{
    use AuthPermissionTrait;
    use StatisticsFilterParamsTrait;

    protected Permission $permission;
    protected ResponseFactory $responseFactory;
    protected StatisticsResponseFilters $filters;
    protected DateTimeValues $dateTimeValues;

    public function __construct(
        Permission $permission,
        ResponseFactory $responseFactory,
        StatisticsResponseFilters $filters,
        DateTimeValues $dateTimeValues
    ) {
        $this->permission = $permission;
        $this->responseFactory = $responseFactory;
        $this->filters = $filters;
        $this->dateTimeValues = $dateTimeValues;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $surveyId = (int)$request->getData('_id');
        if (!$this->permission->hasSurveyPermission($surveyId, 'statistics')) {
            return $this->responseFactory->makeErrorUnauthorised();
        }

        $qid = (int)(Yii::app()->getRequest()->getQueryParams()['qid'] ?? 0);
        if ($qid <= 0) {
            return $this->responseFactory->makeErrorBadRequest('Missing question ID (qid).');
        }

        try {
            $filters = $this->getFilters();
            $values = $this->dateTimeValues->get(
                $surveyId,
                $qid,
                $filters->count() > 0 ? $filters : null
            );
        } catch (InvalidArgumentException $exception) {
            return $this->responseFactory->makeErrorBadRequest($exception->getMessage());
        } catch (CDbException $exception) {
            if ($exception->getCode() === 42) {
                return $this->responseFactory->makeErrorBadRequest("Survey table with ID {$surveyId} does not exist.");
            }
            throw $exception;
        }

        return $this->responseFactory->makeSuccess($values);
    }
}
