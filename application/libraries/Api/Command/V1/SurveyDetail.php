<?php

namespace LimeSurvey\Api\Command\V1;

use Survey;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurveyDetail;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    ResponseData\ResponseDataError,
    Response\ResponseFactory
};
use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

class SurveyDetail implements CommandInterface
{
    use AuthPermissionTrait;

    protected Survey $survey;
    protected AuthSession $authSession;
    protected TransformerOutputSurveyDetail $transformerOutputSurveyDetail;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param Survey $survey
     * @param AuthSession $authSession
     * @param TransformerOutputSurvey $transformerOutputSurvey
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        Survey $survey,
        AuthSession $authSession,
        TransformerOutputSurveyDetail $transformerOutputSurveyDetail,
        ResponseFactory $responseFactory
    ) {
        $this->survey = $survey;
        $this->authSession = $authSession;
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
        $sessionKey = (string) $request->getData('sessionKey');
        $surveyId = (string) $request->getData('_id');

        if (
            !$this->authSession
                ->checkKey($sessionKey)
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
        //set real survey options with inheritance to get value of "inherit" attribute from db
        // for example get inherit template value  $surveyModel->options->template
        $surveyModel->setOptionsFromDatabase();

        $survey = $this->transformerOutputSurveyDetail
            ->transform($surveyModel);

        return $this->responseFactory
            ->makeSuccess(['survey' => $survey]);
    }
}
