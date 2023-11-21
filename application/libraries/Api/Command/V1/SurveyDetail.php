<?php

namespace LimeSurvey\Api\Command\V1;

use Survey;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurveyDetail;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Auth\CommandAuthInterface,
    Request\Request,
    Response\Response,
    ResponseData\ResponseDataError,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

class SurveyDetail implements CommandInterface
{
    use AuthPermissionTrait;

    protected Survey $survey;
    protected CommandAuthInterface $commandAuth;
    protected TransformerOutputSurveyDetail $transformerOutputSurveyDetail;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param Survey $survey
     * @param CommandAuthInterface $commandAuth
     * @param TransformerOutputSurvey $transformerOutputSurvey
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        Survey $survey,
        CommandAuthInterface $commandAuth,
        TransformerOutputSurveyDetail $transformerOutputSurveyDetail,
        ResponseFactory $responseFactory
    ) {
        $this->survey = $survey;
        $this->commandAuth = $commandAuth;
        $this->transformerOutputSurveyDetail = $transformerOutputSurveyDetail;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run survey detail command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $surveyId = (string) $request->getData('_id');

        if (
            !$this->commandAuth
            || !$this->commandAuth
                ->isAuthenticated($request)
        ) {
            return $this->responseFactory
                ->makeErrorUnauthorised();
        }

        $surveyModel = $this->survey
            ->with(
                'languagesettings',
                'defaultlanguage',
                'groups',
                'groups.questiongroupl10ns',
                'groups.questions',
                'groups.questions.questionl10ns',
                'groups.questions.questionattributes',
                'groups.questions.answers',
                'groups.questions.answers.answerl10ns',
                'groups.questions.subquestions',
                'groups.questions.subquestions.questionl10ns',
                'groups.questions.subquestions.questionattributes',
                'groups.questions.subquestions.answers'
            )->findByPk($surveyId);

        if (!$surveyModel) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(
                    'SURVEY_NOT_FOUND',
                    'Survey not found'
                )
                )->toArray()
            );
        }

        $survey = $this->transformerOutputSurveyDetail
            ->transform($surveyModel);

        return $this->responseFactory
            ->makeSuccess(['survey' => $survey]);
    }
}
