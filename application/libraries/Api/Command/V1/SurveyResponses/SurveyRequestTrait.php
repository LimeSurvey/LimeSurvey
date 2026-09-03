<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

use LimeSurvey\Api\Command\Request\Request;
use SurveyDynamic;

/**
 * Shared request helpers for survey-response API commands: survey id
 * validation, survey + dynamic-model resolution, and pagination defaults.
 */
trait SurveyRequestTrait
{
    protected function getSurvey(Request $request): void
    {
        $survey = $this->survey->findByPk($this->getSurveyId($request));
        if ($survey === null) {
            throw new \RuntimeException('Survey not found');
        }
        $this->survey = $survey;
    }

    protected function getSurveyId(Request $request): string
    {
        $surveyId = (string) $request->getData('_id');
        if (!is_numeric($surveyId)) {
            throw new \InvalidArgumentException("Invalid survey ID");
        }

        return $surveyId;
    }

    protected function getSurveyDynamicModel(Request $request): SurveyDynamic
    {
        return SurveyDynamic::model($this->getSurveyId($request));
    }

    protected function buildPagination(Request $request): array
    {
        $pagination = $request->getData('page');
        $paginationDefault = [
            'pageSize' => 15,
            'currentPage' => 0,
        ];

        if ($pagination) {
            $pageSize = isset($pagination['pageSize'])
                ? (int)$pagination['pageSize']
                : 0;
            $currentPage = isset($pagination['currentPage'])
                ? (int)$pagination['currentPage']
                : null;

            if ($pageSize <= 0) {
                $pageSize = $paginationDefault['pageSize'];
            }
            if ($currentPage === null || $currentPage < 0) {
                $currentPage = $paginationDefault['currentPage'];
            }

            return [
                'pageSize' => $pageSize,
                'currentPage' => $currentPage,
            ];
        }

        return $paginationDefault;
    }
}
