<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use CDbException;
use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\FilterPatcher;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use Permission;
use Survey;
use Answer;
use LimeSurvey\Api\Command\{CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use LimeSurvey\Libraries\Api\Command\V1\Transformer\Output\TransformerOutputSurveyResponses;

class SurveyResponses implements CommandInterface
{
    use AuthPermissionTrait;

    protected Survey $survey;
    protected Answer $answerModel;
    protected Permission $permission;
    protected ResponseFactory $responseFactory;
    protected FilterPatcher $responseFilterPatcher;
    protected TransformerOutputSurveyResponses $transformerOutputSurveyResponses;

    /**
     * Constructor
     *
     * @param Survey $survey
     * @param Answer $answerModel
     * @param Permission $permission
     * @param FilterPatcher $responseFilterPatcher
     * @param ResponseFactory $responseFactory
     * @param TransformerOutputSurveyResponses $transformerOutputSurveyResponses
     */
    public function __construct(
        Survey $survey,
        Answer $answerModel,
        Permission $permission,
        FilterPatcher $responseFilterPatcher,
        ResponseFactory $responseFactory,
        TransformerOutputSurveyResponses $transformerOutputSurveyResponses
    ) {
        $this->survey = $survey;
        $this->answerModel = $answerModel;
        $this->permission = $permission;
        $this->responseFactory = $responseFactory;
        $this->responseFilterPatcher = $responseFilterPatcher;
        $this->transformerOutputSurveyResponses = $transformerOutputSurveyResponses;
    }

    /**
     * Run survey detail command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        try {
            $data = $this->process($request);

            return $this->responseFactory->makeSuccess(['responses' => $data]);
        } catch (TransformerException $e) {
            return $this->responseFactory->makeError('Invalid key sent');
        } catch (PermissionDeniedException $e) {
            return $this->responseFactory->makeErrorUnauthorised();
        }
    }

    /**
     * @param Request $request
     * @return array
     * @throws TransformerException
     */
    public function process(Request $request): array
    {
        $surveyId = $this->getSurveyId($request);
        if (!$this->permission->hasSurveyPermission($surveyId, 'responses')) {
            throw new PermissionDeniedException();
        }

        $this->getSurvey($request);
        $model = $this->getSurveyDynamicModel($request);
        [$criteria, $sort] = $this->buildCriteria($request);
        $pagination = $this->buildPagination($request);
        $dataProvider = new \LSCActiveDataProvider(
            $model,
            array(
                'sort' => $sort,
                'criteria' => $criteria,
                'pagination' => $pagination,
            )
        );

        try {
            $surveyResponses = $dataProvider->getData();
        } catch (CDbException $e) {
            // Since questions keys are column, if there's an invalid key sent,
            // an exception will be thrown which will result in an error 500.
            throw new TransformerException();
        }

        $this->transformerOutputSurveyResponses->fieldMap = createFieldMap($this->survey);

        $data = [];
        $data['responses'] = $this->transformerOutputSurveyResponses->transform(
            $surveyResponses,
            ['survey' => $this->survey]
        );
        $data['surveyQuestions'] = $this->getQuestionFieldMap();
        $data['_meta'] = [
            'pagination' => [
                'pageSize' => $pagination['pageSize'],
                'currentPage' => $pagination['currentPage'],
                'totalItems' => $dataProvider->getTotalItemCount(),
                'totalPages' => ceil(
                    $dataProvider->getTotalItemCount()
                    / ($pagination['pageSize'] ?? 1)
                )
            ],
            'filters' => $request->getData('filters', []),
            'sort' => $request->getData('sort', []),
        ];

        return $this->mapResponsesToQuestions($data);
    }

    /**
     * Maps survey responses to survey questions.
     *
     * @param array $data
     * @return array
     */
    protected function mapResponsesToQuestions(array $data): array
    {
        foreach ($data['responses'] as &$response) {
            foreach ($response['answers'] as &$answer) {
                $qid = $answer['key'];
                if (isset($data["surveyQuestions"][$qid])) {
                    $answer = array_merge(
                        $answer,
                        $data["surveyQuestions"][$qid]
                    );
                    $answer['actual_aid'] = $this->getActualAid(
                        $answer['qid'],
                        $answer['scale_id'] ?? $answer['scaleid'] ?? 0,
                        $answer['value'],
                    );
                }
            }
        }
        return $data;
    }

    /**
     * @return array
     */
    protected function getQuestionFieldMap(): array
    {
        //This function generates an array containing the fieldcode, and matching data in the same order as the responses table
        $fieldMap = $this->transformerOutputSurveyResponses->fieldMap;

        return array_filter(
            array_map(
                function ($item) {
                    if (!empty($item['qid'])) {
                        return [
                            'gid' => $item['gid'],
                            'qid' => $item['qid'],
                            'aid' => $item['aid'] ?? null,
                            'sqid' => $item['sqid'] ?? null,
                            'scaleid' => $item['scale_id'] ?? null,
                        ];
                    }
                    return null; // Explicit return for when condition is false
                },
                $fieldMap
            )
        );
    }

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
        $surveyId = (string)$request->getData('_id');
        if (!is_numeric($surveyId)) {
            throw new \InvalidArgumentException("Invalid survey ID");
        }

        return $surveyId;
    }

    protected function getSurveyDynamicModel(Request $request): \SurveyDynamic
    {
        return \SurveyDynamic::model($this->getSurveyId($request));
    }

    protected function buildCriteria(Request $request): array
    {
        $searchParams = [];
        $searchParams['filters'] = $request->getData('filters', null);
        $searchParams['sort'] = $request->getData('sort', null);
        $dataMap = $this->transformerOutputSurveyResponses->getDataMap();
        $sort = new \CSort();
        $criteria = new \LSDbCriteria();
        $this->responseFilterPatcher->apply(
            $searchParams,
            $criteria,
            $sort,
            $dataMap
        );

        return [$criteria, $sort];
    }

    protected function buildPagination(Request $request): array
    {
        $pagination = $request->getData('page');
        $paginationDefault = [
            'pageSize' => 15,
            'currentPage' => 0,
        ];

        if ($pagination) {
            $paginationRequiredKeys = ['currentPage', 'pageSize'];

            if (
                isset($pagination['pageSize'])
                && (int)$pagination['pageSize'] == 0
            ) {
                $pagination['pageSize'] = $paginationDefault['pageSize'];
            }

            if (
                !empty(
                    array_diff_key(
                        array_flip($paginationRequiredKeys),
                        $pagination
                    )
                )
            ) {
                return array_merge($paginationDefault, $pagination);
            }

            return $pagination;
        }

        return $paginationDefault;
    }

    /**
     * Gets all answers for the survey questions and caches them for later use
     *
     * @return array Answers indexed by qid, scale_id, and code
     */
    protected function getAllSurveyAnswers()
    {
        static $answersCache = [];
        $surveyId = $this->survey->sid;
        if (!isset($answersCache[$surveyId])) {
            // Get all questions for this survey
            /** @var \Question[] $questions */
            /** @psalm-suppress UndefinedMagicPropertyFetch */
            $questions = $this->survey->questions;
            $questionIds = array_map(function (\Question $q): int {
                return $q->qid;
            }, $questions);

            // Fetch all answers for these questions in a single query
            $answers = $this->answerModel->findAll(
                'qid IN (' . implode(',', $questionIds) . ')'
            );

            // Index answers by qid, scale_id, and code for fast lookup
            $answersCache[$surveyId] = [];
            foreach ($answers as $answer) {
                $answersCache[$surveyId][$answer->qid][$answer->scale_id][$answer->code] = $answer->aid;
            }
        }

        return $answersCache[$surveyId];
    }

    /**
     * Gets the actual answer ID efficiently using the cached answers
     *
     * @param int $questionID The question ID
     * @param int $scaleId The scale ID
     * @param string $value The answer code
     * @return int|null The answer ID or null if not found
     */
    protected function getActualAid($questionID, $scaleId, $value)
    {
        $allAnswers = $this->getAllSurveyAnswers();
        return $allAnswers[$questionID][$scaleId][$value] ?? null;
    }
}
