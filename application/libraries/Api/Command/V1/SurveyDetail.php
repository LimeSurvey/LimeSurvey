<?php

namespace LimeSurvey\Api\Command\V1;

use Permission;
use Survey;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurveyDetail;
use LimeSurvey\Api\Command\{
    CommandInterface,
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
    protected TransformerOutputSurveyDetail $transformerOutputSurveyDetail;
    protected ResponseFactory $responseFactory;
    protected Permission $permission;

    /**
     * Constructor
     *
     * @param Survey $survey
     * @param TransformerOutputSurveyDetail $transformerOutputSurveyDetail
     * @param ResponseFactory $responseFactory
     * @param Permission $permission
     */
    public function __construct(
        Survey $survey,
        TransformerOutputSurveyDetail $transformerOutputSurveyDetail,
        ResponseFactory $responseFactory,
        Permission $permission
    ) {
        $this->survey = $survey;
        $this->transformerOutputSurveyDetail = $transformerOutputSurveyDetail;
        $this->responseFactory = $responseFactory;
        $this->permission = $permission;
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
        $hasPermission = $this->permission->hasSurveyPermission(
            (int)$surveyId,
            'survey',
            'read'
        );
        if (!$hasPermission) {
            return $this->responseFactory
                ->makeErrorUnauthorised();
        }

        $surveyModel = $this->survey
            ->with(
                'languagesettings',
                'defaultlanguage',
                'owner',
                'surveygroup',
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
                'groups.questions.subquestions.answers',
                'groups.questions.conditions'
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
