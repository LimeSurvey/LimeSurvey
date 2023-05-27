<?php

namespace LimeSurvey\Api\Command\V1;

use Survey;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurveyDetail;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    ResponseData\ResponseDataError,
    Response\ResponseFactory
};
use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\{
    CommandResponseTrait,
    Auth\AuthPermissionTrait
};

class SurveyDetail implements CommandInterface
{
    use AuthPermissionTrait;
    use CommandResponseTrait;

    protected ?AuthSession $authSession = null;
    protected ?TransformerOutputSurveyDetail $transformerOutputSurveyDetail = null;
    protected ?ResponseFactory $responseFactory = null;

    /**
     * Constructor
     *
     * @param TransformerOutputSurvey $transformerOutputSurvey
     */
    public function __construct(
        AuthSession $authSession,
        TransformerOutputSurveyDetail $transformerOutputSurveyDetail,
        ResponseFactory $responseFactory
    )
    {
        $this->authSession = $authSession;
        $this->transformerOutputSurveyDetail = $transformerOutputSurveyDetail;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run survey detail command
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sessionKey = (string) $request->getData('sessionKey');
        $surveyId = (string) $request->getData('_id');

        if (
            (
                $response = $this->authSession
                    ->checkKey($sessionKey)
            ) !== true
        ) {
            return $response;
        }

        $surveyModel = Survey::model()
            ->with(
                'languagesettings',
                'defaultlanguage',
                'groups',
                'groups.questiongroupl10ns',
                'groups.questions',
                'groups.questions.questionl10ns',
                'groups.questions.questionattributes',
                'groups.questions.answers',
                'groups.questions.subquestions',
                'groups.questions.subquestions.questionl10ns',
                'groups.questions.subquestions.questionattributes',
                'groups.questions.subquestions.questionattributes',
                'groups.questions.subquestions.answers'
            )->findByPk($surveyId);

        if (!$surveyModel) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(
                    'SURVEY_NOT_FOUND',
                    'Survey not found')
                )->toArray()
            );
        }

        $survey = $this->transformerOutputSurveyDetail
            ->transform($surveyModel);

        return $this->responseFactory
            ->makeSuccess(['survey' => $survey]);
    }
}
