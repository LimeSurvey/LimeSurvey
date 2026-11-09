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
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSetService;
use LimeSurvey\Models\Services\SurveyStatistics\DateTimeValues;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
use Permission;
use Survey;
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
    protected ResponseFilterSetService $filterSetService;

    /**
     * @param Permission $permission
     * @param ResponseFactory $responseFactory
     * @param StatisticsResponseFilters $filters
     * @param DateTimeValues $dateTimeValues
     * @param ResponseFilterSetService $filterSetService
     */
    public function __construct(
        Permission $permission,
        ResponseFactory $responseFactory,
        StatisticsResponseFilters $filters,
        DateTimeValues $dateTimeValues,
        ResponseFilterSetService $filterSetService
    ) {
        $this->permission = $permission;
        $this->responseFactory = $responseFactory;
        $this->filters = $filters;
        $this->dateTimeValues = $dateTimeValues;
        $this->filterSetService = $filterSetService;
    }

    /**
     * Return the answers of the `qid` question per day and time, for all days
     * or only those from `from` to `to`.
     *
     * @param Request $request
     * @return Response The values, or an error when not permitted, the survey
     *     doesn't exist or the request is invalid
     */
    public function run(Request $request)
    {
        $surveyId = (int)$request->getData('_id');
        if (!$this->permission->hasSurveyPermission($surveyId, 'statistics')) {
            return $this->responseFactory->makeErrorUnauthorised();
        }

        $params = Yii::app()->getRequest()->getQueryParams();
        $qid = (int)($params['qid'] ?? 0);
        if ($qid <= 0) {
            return $this->responseFactory->makeErrorBadRequest('Missing question ID (qid).');
        }

        $decrypt = in_array($params['decrypt'] ?? null, ['1', 'true'], true);
        if ($decrypt && !$this->permission->hasSurveyPermission($surveyId, 'responses')) {
            return $this->responseFactory->makeErrorUnauthorised();
        }

        $survey = $this->findSurvey($surveyId);
        if ($survey === null) {
            return $this->responseFactory->makeErrorNotFound("Survey {$surveyId} not found.");
        }

        $fromDay = $params['from'] ?? null;
        $toDay = $params['to'] ?? null;
        foreach ([$fromDay, $toDay] as $day) {
            if ($day !== null && !self::isDay($day)) {
                return $this->responseFactory->makeErrorBadRequest('Days have to be given as YYYY-MM-DD.');
            }
        }
        if ($fromDay !== null && $toDay !== null && $fromDay > $toDay) {
            return $this->responseFactory->makeErrorBadRequest('The range ends before it starts.');
        }

        $language = $request->getData('language') ?: $survey->language;
        if (!in_array($language, $survey->getAllLanguages(), true)) {
            return $this->responseFactory->makeErrorBadRequest('Invalid language code');
        }

        try {
            $filters = $this->getFilters();
            $values = $this->dateTimeValues->get(
                $surveyId,
                $qid,
                $filters->count() > 0 ? $filters : null,
                $this->resolveFilterSet($request, $survey, $language),
                $decrypt,
                $fromDay,
                $toDay
            );
        } catch (InvalidArgumentException $exception) {
            return $this->responseFactory->makeErrorBadRequest($exception->getMessage());
        } catch (PermissionDeniedException $exception) {
            return $this->responseFactory->makeErrorUnauthorised();
        } catch (CDbException $exception) {
            if ($exception->getCode() === 42) {
                return $this->responseFactory->makeErrorBadRequest("Survey table with ID {$surveyId} does not exist.");
            }
            throw $exception;
        }

        if (isset($values['days']) && $values['days'] === []) {
            $values['days'] = new \stdClass();
        }

        return $this->responseFactory->makeSuccess($values);
    }

    /**
     * Whether a request value is a real day, 'Y-m-d'.
     *
     * @param mixed $value
     * @return bool
     */
    private static function isDay($value): bool
    {
        return is_string($value)
            && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $parts) === 1
            && checkdate((int)$parts[2], (int)$parts[3], (int)$parts[1]);
    }

    /**
     * Load the survey.
     *
     * @param int $surveyId
     * @return Survey|null Null when the survey doesn't exist
     */
    protected function findSurvey(int $surveyId): ?Survey
    {
        return Survey::model()->findByPk($surveyId);
    }
}
